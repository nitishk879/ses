<?php

namespace App\Console\Commands;

use App\Enums\InterviewStatus;
use App\Models\Interview;
use App\Services\InterviewSchedulingService;
use Illuminate\Console\Command;

/**
 * Closes offers nobody answered.
 *
 * Without this an unanswered invitation sits as SLOT_SELECTION forever: its
 * token stays live long after the times in it have passed, the candidate
 * appears to be mid-process on every report, and a recruiter has no signal
 * that they should follow up by hand.
 *
 * Runs hourly rather than every minute — an offer measured in days does not
 * need minute-accurate expiry, and an hourly job is one that can be read in
 * the logs.
 */
class ExpireInterviewInvitations extends Command
{
    protected $signature = 'interviews:expire-invitations
                            {--limit=100 : Most invitations to close in one run}
                            {--dry-run : Report what would be closed, change nothing}';

    protected $description = 'Close interview invitations that were never answered';

    public function handle(InterviewSchedulingService $scheduling): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $stale = Interview::query()
            ->whereIn('status', [InterviewStatus::INVITED, InterviewStatus::SLOT_SELECTION])
            ->whereNotNull('invitation_expires_at')
            ->where('invitation_expires_at', '<', now())
            ->limit(max(1, (int) $this->option('limit')))
            ->get();

        if ($stale->isEmpty()) {
            $this->components->info('No expired invitations.');

            return self::SUCCESS;
        }

        foreach ($stale as $interview) {
            if ($dryRun) {
                $this->components->twoColumnDetail(
                    "Interview #{$interview->id}",
                    '<fg=yellow>would expire</>'
                );

                continue;
            }

            $scheduling->expire($interview);
            $this->components->twoColumnDetail(
                "Interview #{$interview->id}",
                '<fg=yellow>expired — needs rescheduling</>'
            );
        }

        $this->components->info(sprintf(
            '%s %d invitation(s).', $dryRun ? 'Would close' : 'Closed', $stale->count()
        ));

        return self::SUCCESS;
    }
}
