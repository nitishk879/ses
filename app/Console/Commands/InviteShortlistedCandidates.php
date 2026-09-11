<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Talent;
use App\Services\InterviewInvitationService;
use Illuminate\Console\Command;
use RuntimeException;
use Throwable;

/**
 * Task 5: invite everyone who cleared the match threshold.
 *
 * Deliberately a command a person runs, not something on the every-minute
 * schedule. Inviting a candidate sends real email to a real person and starts
 * a clock; that should be a decision somebody takes, not a side effect of a
 * scoring job finishing at 3am. The service underneath is callable from a
 * controller the day a "Invite shortlist" button exists.
 *
 * Safe to re-run: candidates who already have an interview are skipped by the
 * shortlist query, and an interview that already carries a token is skipped
 * again inside the service.
 */
class InviteShortlistedCandidates extends Command
{
    protected $signature = 'interviews:invite
                            {project : Project id to shortlist for}
                            {--threshold= : Match score to invite at (default from config)}
                            {--limit=25 : Most candidates to invite in one run}
                            {--dry-run : Show who would be invited, send nothing}';

    protected $description = 'Invite shortlisted candidates and offer them interview slots';

    public function handle(InterviewInvitationService $invitations): int
    {
        $project = Project::find($this->argument('project'));

        if (! $project) {
            $this->components->error("Project {$this->argument('project')} not found.");

            return self::FAILURE;
        }

        $threshold = $this->option('threshold') !== null
            ? (int) $this->option('threshold')
            : (int) config('services.interview.invitation.min_match_score', 70);

        $dryRun = (bool) $this->option('dry-run');
        $limit = max(1, (int) $this->option('limit'));

        $shortlist = $invitations->shortlistFor($project, $threshold)->take($limit);

        if ($shortlist->isEmpty()) {
            $this->components->info(
                "No uninvited candidates at or above {$threshold} for project {$project->id}."
            );

            return self::SUCCESS;
        }

        $this->components->info(
            sprintf('%s %d candidate(s) for "%s" at threshold %d',
                $dryRun ? 'Would invite' : 'Inviting',
                $shortlist->count(), $project->title, $threshold)
        );

        $sent = $failed = 0;

        foreach ($shortlist as $match) {
            $talent = Talent::with('user')->find($match->talent_id);

            if (! $talent) {
                continue;
            }

            $label = "talent #{$match->talent_id} (score {$match->score})";

            if ($dryRun) {
                $reachable = filled($talent->user?->email) ? 'email ok' : 'NO EMAIL';
                $this->components->twoColumnDetail($label, "<fg=yellow>would invite — {$reachable}</>");
                $sent++;

                continue;
            }

            try {
                $interview = $invitations->invite($project, $talent, (int) $match->score);
                $sent++;
                $this->components->twoColumnDetail(
                    $label,
                    "<fg=green>invited — interview #{$interview->id}, {$interview->slots()->count()} slots</>"
                );
            } catch (RuntimeException $e) {
                // One unreachable candidate must not abandon the shortlist.
                $failed++;
                $this->components->twoColumnDetail($label, "<fg=red>{$e->getMessage()}</>");
            } catch (Throwable $e) {
                $failed++;
                $this->components->twoColumnDetail($label, "<fg=red>unexpected: {$e->getMessage()}</>");
            }
        }

        $this->newLine();
        $this->components->info(sprintf(
            '%s %d; failed %d.', $dryRun ? 'Would send' : 'Sent', $sent, $failed
        ));

        return $failed > 0 && $sent === 0 ? self::FAILURE : self::SUCCESS;
    }
}
