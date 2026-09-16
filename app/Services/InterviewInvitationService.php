<?php

namespace App\Services;

use App\Enums\InterviewSlotStatus;
use App\Enums\InterviewStatus;
use App\Models\AiMatch;
use App\Models\Interview;
use App\Models\Project;
use App\Models\Talent;
use App\Notifications\InterviewInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Tasks 5 and 6: shortlist a candidate, then offer them times.
 *
 * The two are one operation on purpose. An invitation with no times in it asks
 * the candidate to reply and wait, which is the manual back-and-forth this
 * whole feature exists to remove — so the email that says "we would like to
 * screen you" is the same email that says "here are three slots".
 *
 * Nothing here dials. Selection sets `interviews.scheduled_at`, and the
 * scheduler that already runs every minute takes it from there.
 */
class InterviewInvitationService
{
    /**
     * States from which a fresh invitation may legitimately be issued.
     *
     * Everything absent from this list is either live or finished, and
     * re-inviting it would be destructive: the write below resets the status,
     * mints a new token and deletes the slot rows, so running it against a
     * COMPLETED interview would erase the record of how that interview was
     * booked and put a candidate who has already been screened back at the
     * start.
     *
     * The command path never reaches that — `shortlistFor()` excludes anyone
     * who already has an interview — but the service is public, and a future
     * "Re-invite" button calling it directly must not be able to do this.
     *
     * @var array<int, InterviewStatus>
     */
    private const REINVITABLE = [
        InterviewStatus::PENDING,
        InterviewStatus::INVITED,
        InterviewStatus::SLOT_SELECTION,
        InterviewStatus::RESCHEDULE_REQUIRED,
        InterviewStatus::NO_ANSWER,
        InterviewStatus::MISSED,
        InterviewStatus::FAILED,
        InterviewStatus::CANCELLED,
    ];

    public function __construct(
        private readonly InterviewSlotGenerator $slots,
    ) {
    }

    /**
     * Everyone scoring at or above the threshold who has not been invited yet.
     *
     * Reads `ai_matches`, which is the output of the scoring step — so the
     * shortlist is the match result, not a separate judgement that could
     * disagree with it.
     *
     * @return \Illuminate\Support\Collection<int, AiMatch>
     */
    public function shortlistFor(Project $project, ?int $threshold = null): \Illuminate\Support\Collection
    {
        $threshold ??= (int) config('services.interview.invitation.min_match_score', 70);

        $alreadyInvited = Interview::query()
            ->where('project_id', $project->id)
            ->pluck('talent_id');

        return AiMatch::query()
            ->where('project_id', $project->id)
            ->where('score', '>=', $threshold)
            ->whereNotIn('talent_id', $alreadyInvited)
            ->orderByDesc('score')
            ->get();
    }

    /**
     * Why a shortlist came back empty — there are three different reasons.
     *
     * An empty shortlist used to be reported one way: "nobody is at or above
     * N". That is true in only one of the three cases, and it is actively
     * misleading in the other two. A recruiter whose candidates had all been
     * invited last week, or who had never pressed Run matching, was told their
     * threshold was too high and lowered it repeatedly, which changed nothing.
     *
     * @return array{scored: int, qualifying: int, available: int}
     */
    public function shortlistBreakdown(Project $project, ?int $threshold = null): array
    {
        $threshold ??= (int) config('services.interview.invitation.min_match_score', 70);

        $scored = AiMatch::where('project_id', $project->id);

        return [
            // Has matching ever run for this project at all?
            'scored' => (clone $scored)->count(),
            // Of those, how many clear the threshold, invited or not?
            'qualifying' => (clone $scored)->where('score', '>=', $threshold)->count(),
            // And how many of those have not already been invited?
            'available' => $this->shortlistFor($project, $threshold)->count(),
        ];
    }

    /**
     * States a reschedule must refuse.
     *
     * The interview is happening, or has happened. Rescheduling one of these
     * would mint a fresh token, wipe the slots and put the status back to
     * "choose a time" — erasing the record of a call that actually took place,
     * and in the two live states, cutting off a candidate mid-sentence.
     *
     * @var array<int, InterviewStatus>
     */
    private const NOT_RESCHEDULABLE = [
        InterviewStatus::STARTING,
        InterviewStatus::IN_PROGRESS,
        InterviewStatus::COMPLETED,
        InterviewStatus::EVALUATING,
        InterviewStatus::EVALUATED,
    ];

    /**
     * Withdraw the times already offered and put new ones in front of the
     * candidate.
     *
     * Separate from {@see invite()} rather than a flag on it, because the two
     * have opposite safety rules. `invite()` is idempotent and deliberately
     * does nothing when a live invitation exists — that is what stops a queue
     * retry from emailing somebody twice. Rescheduling is the case where the
     * recruiter *means* to replace a live invitation, so it has to do exactly
     * what `invite()` refuses to.
     *
     * The old slots are deleted, including one the candidate had already
     * chosen. That is the point: their booking is being withdrawn. The time
     * they had picked goes into the log line, because deleting the row removes
     * the only other record of it, and `(interview_id, position)` is unique —
     * so keeping the old rows around would collide with the new ones rather
     * than preserving anything.
     *
     * @param  array<int, string>  $slotTimes  naive local times; empty means generate
     *
     * @throws RuntimeException when the interview has already happened
     */
    public function reschedule(Interview $interview, array $slotTimes = []): Interview
    {
        $interview->loadMissing(['talent.user', 'project']);

        if (in_array($interview->status, self::NOT_RESCHEDULABLE, true)) {
            throw new RuntimeException(__('interview.not_reschedulable', [
                'status' => InterviewStatus::toName($interview->status),
            ]));
        }

        $talent = $interview->talent;

        if (blank($talent?->user?->email)) {
            throw new RuntimeException(__('interview.no_email_to_reschedule'));
        }

        // Same gate as a first invitation: the email promises a phone call.
        if ($talent->interviewPhone() === null) {
            throw new RuntimeException(__('interview.phone_not_dialable', [
                'talent' => $talent->user?->name ?: "#{$talent->id}",
            ]));
        }

        $timezone = $this->timezoneFor($interview);
        $count = (int) config('services.interview.invitation.slots_offered', 3);

        $windows = filled($slotTimes)
            ? $this->slots->fromExplicit($slotTimes, $timezone)
            : $this->slots->generate($timezone, $count);

        if ($windows->isEmpty()) {
            throw new RuntimeException(filled($slotTimes)
                ? __('interview.no_slots_chosen')
                : __('interview.no_slots_available'));
        }

        $previous = $interview->scheduled_at;
        $validHours = (int) config('services.interview.invitation.offer_valid_hours', 72);

        DB::transaction(function () use ($interview, $timezone, $windows, $validHours) {
            $interview->fill([
                'status' => InterviewStatus::SLOT_SELECTION,
                'timezone' => $timezone,
                'invitation_token' => bin2hex(random_bytes(32)),
                'invitation_sent_at' => now(),
                'invitation_expires_at' => now()->addHours($validHours),
                // The old booking is gone. Left set, the scheduler that runs
                // every minute would dial the withdrawn time.
                'scheduled_at' => null,
                'slot_selected_at' => null,
                'failure_reason' => null,
            ])->save();

            $interview->slots()->delete();

            foreach ($windows as $index => $window) {
                $interview->slots()->create([
                    'starts_at' => $window['starts_at']->utc(),
                    'ends_at' => $window['ends_at']->utc(),
                    'status' => InterviewSlotStatus::OFFERED,
                    'position' => $index + 1,
                ]);
            }
        });

        $interview->load('slots', 'project');

        // Outside the transaction, for the same reason as the first invitation:
        // a mail failure must not roll back a withdrawal the candidate may
        // already have been told about.
        $talent->user->notify(new InterviewInvitation($interview, rescheduled: true));

        Log::info('interview.rescheduled', [
            'interview_id' => $interview->id,
            // The withdrawn booking, recorded here because deleting the slot
            // row removes the only other trace of it.
            'previous_scheduled_at' => $previous?->toIso8601String(),
            'slots_offered' => $windows->count(),
            'chosen_by_recruiter' => filled($slotTimes),
            'by' => auth()->id(),
        ]);

        return $interview;
    }

    /**
     * Invite one candidate: create the interview, offer slots, send the email.
     *
     * Idempotent by construction — an interview that already carries an
     * invitation token is returned untouched. A queue retry after a successful
     * send must not put a second email in a candidate's inbox.
     *
     * @param  array<int, string>|null  $slotTimes  naive local times the recruiter
     *         chose, or null to fall back to generated windows
     */
    public function invite(
        Project $project,
        Talent $talent,
        ?int $matchScore = null,
        ?array $slotTimes = null,
    ): Interview {
        $talent->loadMissing('user');

        if (blank($talent->user?->email)) {
            throw new RuntimeException(
                "Talent {$talent->id} has no email address; cannot be invited."
            );
        }

        // Checked here, before anything is written or sent.
        //
        // The invitation is not an email, it is a promise: it offers three
        // times and says the candidate will receive a phone call at the one
        // they choose. Sending it to somebody with no dialable number means
        // they read it, pick a slot, and then sit waiting for a call that was
        // never possible — and the recruiter finds out at dial time, after the
        // window has passed.
        //
        // The same parse the orchestrator runs before dialling, so what passes
        // here is exactly what will dial later. Failing at the invitation is a
        // recruiter fixing one field; failing at dial time is a candidate
        // stood up.
        if ($talent->interviewPhone() === null) {
            throw new RuntimeException(__('interview.phone_not_dialable', [
                'talent' => $talent->user?->name ?: "#{$talent->id}",
            ]));
        }

        $interview = Interview::firstOrNew([
            'project_id' => $project->id,
            'talent_id' => $talent->id,
        ]);

        if ($interview->exists && filled($interview->invitation_token)) {
            // A live invitation is already out there. A queue retry after a
            // successful send must not put a second email in an inbox.
            Log::info('interview.invite_skipped_already_sent', [
                'interview_id' => $interview->id,
            ]);

            return $interview;
        }

        if ($interview->exists && ! in_array($interview->status, self::REINVITABLE, true)) {
            // Live or finished. Returned untouched rather than thrown on: the
            // caller asked for this candidate to have an interview, and they
            // do — it is simply further along than the caller assumed.
            Log::info('interview.invite_skipped_not_reinvitable', [
                'interview_id' => $interview->id,
                'status' => $interview->status->value,
            ]);

            return $interview;
        }

        $timezone = $this->timezoneFor($interview);
        $count = (int) config('services.interview.invitation.slots_offered', 3);

        // Times the recruiter chose win over generated ones. They know
        // things the scheduler cannot: that this candidate asked for an
        // evening, that the client wants them seen today, that Monday is
        // a holiday. The generator remains the default because most
        // invitations do not need that knowledge.
        $windows = filled($slotTimes)
            ? $this->slots->fromExplicit($slotTimes, $timezone)
            : $this->slots->generate($timezone, $count);

        if ($windows->isEmpty()) {
            // Loud, not silent. An email listing no times is worse than no
            // email at all, and the two ways of getting here need different
            // fixes — one is a recruiter who left every box blank, the
            // other a calendar that is full or a misconfigured horizon.
            throw new RuntimeException(filled($slotTimes)
                ? __('interview.no_slots_chosen')
                : 'No interview slots are available within the configured horizon; '
                  .'widen INTERVIEW_HORIZON_DAYS or check for a booked-out calendar.'
            );
        }

        $validHours = (int) config('services.interview.invitation.offer_valid_hours', 72);

        $interview = DB::transaction(function () use (
            $interview, $project, $talent, $timezone, $windows, $validHours, $matchScore
        ) {
            $interview->fill([
                'project_id' => $project->id,
                'talent_id' => $talent->id,
                'status' => InterviewStatus::SLOT_SELECTION,
                'channel' => 'phone',
                'timezone' => $timezone,
                'invitation_token' => bin2hex(random_bytes(32)),
                'invitation_sent_at' => now(),
                'invitation_expires_at' => now()->addHours($validHours),
                'match_score' => $matchScore,
                'failure_reason' => null,
            ])->save();

            /*
             * A re-invite after expiry starts from a clean set — but never
             * touches a slot that was actually taken. Belt and braces next to
             * the REINVITABLE guard above: a confirmed booking is the record
             * of an appointment that was made, and nothing in an invitation
             * flow has any business deleting one.
             */
            $interview->slots()
                ->where('status', '!=', InterviewSlotStatus::SELECTED)
                ->delete();

            foreach ($windows as $index => $window) {
                $interview->slots()->create([
                    'starts_at' => $window['starts_at']->utc(),
                    'ends_at' => $window['ends_at']->utc(),
                    'status' => InterviewSlotStatus::OFFERED,
                    'position' => $index + 1,
                ]);
            }

            return $interview;
        });

        $interview->load('slots', 'project');

        // Sent outside the transaction: a mail failure must not roll back an
        // invitation that the candidate may already have received, and a
        // committed row with no mail can be re-sent safely.
        $talent->user->notify(new InterviewInvitation($interview));

        Log::info('interview.invited', [
            'interview_id' => $interview->id,
            'project_id' => $project->id,
            'talent_id' => $talent->id,
            'slots' => $interview->slots->count(),
            'score' => $matchScore,
        ]);

        return $interview;
    }

    /**
     * The zone the candidate should read times in.
     *
     * SES stores no timezone against a user, so a configured default stands in.
     * Named here rather than inlined because the day that column exists, this
     * is the only place that has to change.
     */
    private function timezoneFor(Interview $interview): string
    {
        return $interview->timezone
            ?: (string) config('services.interview.invitation.timezone', 'Asia/Tokyo');
    }
}
