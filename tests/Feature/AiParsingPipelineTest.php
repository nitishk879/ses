<?php

namespace Tests\Feature;

use App\Jobs\ParseProjectJd;
use App\Jobs\ScoreProjectMatches;
use App\Models\AiJdParse;
use App\Models\AiMatch;
use App\Models\AiResumeParse;
use App\Models\Company;
use App\Models\Project;
use App\Models\Talent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The parse-and-score pipeline.
 *
 * The behaviour that matters most here is what does NOT happen: an unchanged
 * job description must not reach the language model at all. That is the
 * difference between re-scoring a candidate pool for free and paying five
 * seconds of GPU per project every time someone opens a page.
 */
class AiParsingPipelineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.ai_parser.url', 'http://ai-parser.test');
        config()->set('services.ai_parser.secret', str_repeat('s', 48));
    }

    private function project(): Project
    {
        return Project::factory()->create([
            'company_id' => Company::factory()->create()->id,
            'title' => 'Backend engineer',
            'project_description' => 'Java and AWS required.',
            'personnel_requirement' => 'Five years.',
        ]);
    }

    private function fakeJdParse(): void
    {
        Http::fake([
            'ai-parser.test/v1/parse/jd' => Http::response([
                'project_id' => 1,
                'title' => 'Backend engineer',
                'required_skills' => [[
                    'raw' => 'Java', 'canonical' => null, 'sub_category_id' => null,
                    'evidence' => 'Java and AWS required.', 'kind' => 'required',
                    'source' => 'text',
                ]],
                'preferred_skills' => [],
                'unmapped_skills' => ['Java'],
                'meta' => [
                    'parser_version' => '1.0.0',
                    'source_hash' => str_repeat('b', 64),
                    'model' => 'llama-3.3-70b',
                    'warnings' => [],
                ],
            ], 200),
        ]);
    }

    public function test_jd_parse_stores_the_structured_result(): void
    {
        $this->fakeJdParse();
        $project = $this->project();

        ParseProjectJd::dispatchSync($project->id);

        $parse = AiJdParse::firstWhere('project_id', $project->id);
        $this->assertNotNull($parse);
        $this->assertSame('1.0.0', $parse->parser_version);
        $this->assertSame(['Java'], $parse->unmappedSkills());
        // The stored hash is OUR fingerprint of the request, not the one the
        // service echoed back — that is what the skip check compares against.
        $this->assertSame(64, strlen($parse->source_hash));
    }

    public function test_unchanged_project_makes_no_call_to_the_language_model(): void
    {
        $this->fakeJdParse();
        $project = $this->project();

        ParseProjectJd::dispatchSync($project->id);
        Http::assertSentCount(1);

        // Second run over identical content: the fingerprint matches, so the
        // job must return before it ever reaches the service.
        ParseProjectJd::dispatchSync($project->id);
        Http::assertSentCount(1);
    }

    public function test_force_reparses_even_when_nothing_changed(): void
    {
        $this->fakeJdParse();
        $project = $this->project();

        ParseProjectJd::dispatchSync($project->id);
        ParseProjectJd::dispatchSync($project->id, force: true);

        Http::assertSentCount(2);
    }

    public function test_edited_description_triggers_a_reparse(): void
    {
        $this->fakeJdParse();
        $project = $this->project();

        ParseProjectJd::dispatchSync($project->id);

        $project->project_description = 'Now we need Python instead.';
        $project->save();

        ParseProjectJd::dispatchSync($project->id);

        Http::assertSentCount(2);
    }

    public function test_parsing_a_jd_queues_a_rescore(): void
    {
        // Every stored score quotes evidence from the JD, so a new JD makes
        // all of them explanations of a document that changed.
        Bus::fake([ScoreProjectMatches::class]);
        $this->fakeJdParse();
        $project = $this->project();

        ParseProjectJd::dispatchSync($project->id);

        Bus::assertDispatched(
            ScoreProjectMatches::class,
            fn ($job) => $job->projectId === $project->id
        );
    }

    public function test_a_deleted_project_is_a_no_op_rather_than_a_failure(): void
    {
        $this->fakeJdParse();

        ParseProjectJd::dispatchSync(999999);

        Http::assertNothingSent();
        $this->assertSame(0, AiJdParse::count());
    }

    public function test_scoring_skips_candidates_whose_score_is_still_fresh(): void
    {
        $project = $this->project();
        $talent = Talent::factory()->create([
            'company_id' => Company::factory()->create()->id,
        ]);

        $jdHash = str_repeat('c', 64);
        $resumeHash = str_repeat('d', 64);

        AiJdParse::create([
            'project_id' => $project->id,
            'parser_version' => '1.0.0',
            'source_hash' => $jdHash,
            'payload' => ['project_id' => $project->id, 'required_skills' => []],
            'parsed_at' => now(),
        ]);
        AiResumeParse::create([
            'talent_id' => $talent->id,
            'parser_version' => '1.0.0',
            'source_hash' => $resumeHash,
            'payload' => ['talent_id' => $talent->id, 'skills' => []],
            'parsed_at' => now(),
        ]);
        AiMatch::create([
            'project_id' => $project->id,
            'talent_id' => $talent->id,
            'score' => 50,
            'payload' => ['score' => 50],
            'scorer_version' => '1.0.0',
            'jd_source_hash' => $jdHash,
            'resume_source_hash' => $resumeHash,
            'scored_at' => now(),
        ]);

        Http::fake();

        ScoreProjectMatches::dispatchSync($project->id);

        // Both hashes still match, so there is nothing to recompute.
        Http::assertNothingSent();
    }

    public function test_scoring_does_nothing_when_the_jd_has_not_been_parsed(): void
    {
        Http::fake();

        ScoreProjectMatches::dispatchSync($this->project()->id);

        Http::assertNothingSent();
        $this->assertSame(0, AiMatch::count());
    }
}
