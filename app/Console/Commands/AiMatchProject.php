<?php

namespace App\Console\Commands;

use App\Jobs\ParseProjectJd;
use App\Jobs\ParseTalentResume;
use App\Jobs\ScoreProjectMatches;
use App\Models\AiMatch;
use App\Models\Project;
use App\Models\Talent;
use Illuminate\Console\Command;

/**
 * Drive the JD parsing and candidate scoring pipeline for one project.
 *
 * Exists so the pipeline can be run and inspected without a queue worker, a
 * browser or a recruiter — which is how you find out whether it actually works
 * on real SES data rather than on the fixtures it was built against.
 */
class AiMatchProject extends Command
{
    protected $signature = 'ses:ai-match
                            {project : Project id or slug}
                            {--resumes : Parse candidate resumes first (slow: one LLM call per CV)}
                            {--limit=0 : With --resumes, cap how many candidates are parsed}
                            {--force : Re-parse and re-score even when nothing changed}
                            {--queue : Dispatch to the queue instead of running inline}
                            {--top=5 : How many ranked candidates to print}';

    protected $description = 'Parse a project JD, score candidates against it, and show the shortlist';

    public function handle(): int
    {
        $project = is_numeric($this->argument('project'))
            ? Project::find($this->argument('project'))
            : Project::where('slug', $this->argument('project'))->first();

        if (! $project) {
            $this->error("Project not found: {$this->argument('project')}");

            return self::FAILURE;
        }

        $this->info("Project #{$project->id} — {$project->title}");
        $force = (bool) $this->option('force');

        if ($this->option('queue')) {
            ParseProjectJd::dispatch($project->id, $force);
            $this->line('  JD parse dispatched; scoring follows on completion.');

            return self::SUCCESS;
        }

        // ── 1. JD ─────────────────────────────────────────────────────────
        $this->line('  Parsing JD...');
        ParseProjectJd::dispatchSync($project->id, $force);

        $project->refresh()->load('aiJdParse');
        if (! $project->aiJdParse) {
            $this->error('  JD parse produced nothing — is the AI service reachable?');

            return self::FAILURE;
        }

        $jd = $project->aiJdParse->payload;
        $this->line(sprintf(
            '  required=%d preferred=%d min_exp=%s languages=%s',
            count($jd['required_skills'] ?? []),
            count($jd['preferred_skills'] ?? []),
            $jd['min_experience_months'] ?? '-',
            collect($jd['languages'] ?? [])->pluck('level')->implode(',') ?: '-'
        ));

        if ($unmapped = $project->aiJdParse->unmappedSkills()) {
            // Expected rather than alarming: the SES taxonomy holds job
            // domains, not technologies. Repeat offenders belong in the alias
            // table.
            $this->comment('  unmapped skills: '.implode(', ', $unmapped));
        }

        // ── 2. Resumes ────────────────────────────────────────────────────
        if ($this->option('resumes')) {
            $query = Talent::query()->whereNotNull('resume')->orderBy('id');
            if ($limit = (int) $this->option('limit')) {
                $query->limit($limit);
            }
            $talents = $query->get();

            $this->line("  Parsing {$talents->count()} resume(s)...");
            $bar = $this->output->createProgressBar($talents->count());
            foreach ($talents as $talent) {
                try {
                    ParseTalentResume::dispatchSync($talent->id, $force);
                } catch (\Throwable $e) {
                    // A scanned or missing CV must not stop the run.
                    $this->newLine();
                    $this->warn("  talent #{$talent->id}: {$e->getMessage()}");
                }
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();
        }

        // ── 3. Score ──────────────────────────────────────────────────────
        $this->line('  Scoring candidates...');
        ScoreProjectMatches::dispatchSync($project->id, $force);

        // ── 4. Shortlist ──────────────────────────────────────────────────
        $top = AiMatch::with('talent.user')
            ->where('project_id', $project->id)
            ->ranked()
            ->limit((int) $this->option('top'))
            ->get();

        if ($top->isEmpty()) {
            $this->warn('  No scored candidates. Run again with --resumes.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('Shortlist');
        foreach ($top as $match) {
            $name = $match->talent?->user?->name ?? "talent #{$match->talent_id}";
            $this->line("  [{$match->score}/100] {$name}");
            foreach ($match->reasons() as $reason) {
                $this->line("      {$reason}");
            }
            foreach ($match->blockers() as $blocker) {
                $this->line("      ! {$blocker}");
            }
        }

        return self::SUCCESS;
    }
}
