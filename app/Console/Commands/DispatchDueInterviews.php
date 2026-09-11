<?php

namespace App\Console\Commands;

use App\Enums\InterviewAttemptStatus;
use App\Enums\InterviewStatus;
use App\Jobs\StartInterviewAttemptJob;
use App\Models\Interview;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Task 9: at the selected time, start the interview automatically.
 *
 * This is the piece that was missing entirely — `InterviewStatus::SCHEDULED`
 * and `interviews.scheduled_at` both existed, and nothing ever read them, so
 * an interview could be scheduled and would simply sit there.
 *
 * Runs every minute. Everything about it is written to survive that:
 *
 * * **It claims before it queues.** The status moves to STARTING inside a
 *   transaction with a row lock, so two overlapping ticks cannot both dispatch
 *   the same interview. A duplicate here is not a wasted job, it is a
 *   candidate's phone ringing twice.
 * * **It has a grace window.** An interview whose slot passed while the queue
 *   was down is rescheduled rather than dialled — a candidate who agreed to
 *   10:00 will not welcome an unheralded call at 14:00.
 * * **It refuses to run half-configured.** With no outbound number or with
 *   calling disabled it reports and exits, rather than marking interviews
 *   started and failing each one individually.
 */
class DispatchDueInterviews extends Command
{
    protected $signature = 'interviews:dispatch-due
                            {--limit=25 : Most interviews to start in one tick}
                            {--dry-run : Report what would be started, change nothing}';

    protected $description = 'Start interviews whose scheduled time has arrived';

    public function handle(): int
    {
        if (! config('services.interview.enabled')) {
            $this->components->warn('Interview calling is disabled (INTERVIEW_ENABLED=false).');

            return self::SUCCESS;
        }

        if (blank(config('services.interview.from_number'))) {
            // An error, not a warning: the feature is switched on and cannot
            // work, which is a deployment mistake somebody needs to see.
            $this->components->error('INTERVIEW_FROM_NUMBER is not set; refusing to start interviews.');

            return self::FAILURE;
        }

        $grace = (int) config('services.interview.dispatch_grace_minutes', 15);
        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');

        $due = Interview::query()
            ->where('status', InterviewStatus::SCHEDULED)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->where('scheduled_at', '>=', now()->subMinutes($grace))
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->get();

        $stale = Interview::query()
            ->where('status', InterviewStatus::SCHEDULED)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<', now()->subMinutes($grace))
            ->limit($limit)
            ->get();

        foreach ($stale as $interview) {
            if ($dryRun) {
                $this->components->twoColumnDetail(
                    "Interview #{$interview->id}",
                    '<fg=yellow>would reschedule (missed its slot)</>'
                );

                continue;
            }

            $interview->update([
                'status' => InterviewStatus::RESCHEDULE_REQUIRED,
                'failure_reason' => __('interview.slot_missed'),
            ]);

            $this->components->warn("Interview #{$interview->id} missed its slot; marked for rescheduling.");
        }

        $started = 0;

        foreach ($due as $interview) {
            if ($dryRun) {
                $this->components->twoColumnDetail(
                    "Interview #{$interview->id}",
                    '<fg=green>would start</>'
                );
                $started++;

                continue;
            }

            $attempt = $this->claim($interview);

            if ($attempt === null) {
                // Another tick got there first. Not an error.
                continue;
            }

            StartInterviewAttemptJob::dispatch($attempt->id);
            $started++;

            $this->components->twoColumnDetail(
                "Interview #{$interview->id}",
                "<fg=green>queued attempt #{$attempt->attempt_number}</>"
            );
        }

        $this->components->info(sprintf(
            '%s %d interview(s); %d rescheduled.',
            $dryRun ? 'Would start' : 'Started',
            $started,
            $stale->count()
        ));

        return self::SUCCESS;
    }

    /**
     * Take ownership of an interview and give it an attempt to run.
     *
     * The row lock plus the status re-check inside the transaction is what
     * makes an overlapping tick safe: the second one reads STARTING and backs
     * out. Returns null when someone else claimed it.
     */
    private function claim(Interview $interview): ?\App\Models\InterviewAttempt
    {
        return DB::transaction(function () use ($interview) {
            $locked = Interview::query()
                ->whereKey($interview->id)
                ->lockForUpdate()
                ->first();

            if (! $locked || $locked->status !== InterviewStatus::SCHEDULED) {
                return null;
            }

            $locked->update(['status' => InterviewStatus::STARTING]);

            // Reuse an attempt that is still waiting rather than creating a
            // second one: `scheduleRetry()` creates the row when it schedules
            // the retry, so by the time the slot arrives one usually exists.
            $pending = $locked->attempts()
                ->where('status', InterviewAttemptStatus::PENDING)
                ->orderByDesc('attempt_number')
                ->first();

            if ($pending) {
                return $pending;
            }

            $next = ($locked->attempts()->max('attempt_number') ?? 0) + 1;

            return $locked->attempts()->create([
                'attempt_number' => $next,
                'status' => InterviewAttemptStatus::PENDING,
                'channel' => $locked->channel ?: 'phone',
            ]);
        });
    }
}
