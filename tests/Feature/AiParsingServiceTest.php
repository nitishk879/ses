<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Project;
use App\Models\Talent;
use App\Services\AiParsingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

/**
 * The contract between SES and the parsing service.
 *
 * All HTTP is faked: these assert what SES *sends* and how it behaves when
 * the service answers badly. Nothing here needs the Python service running.
 */
class AiParsingServiceTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'test-secret-that-is-long-enough-for-the-service-x';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.ai_parser.url', 'http://ai-parser.test');
        config()->set('services.ai_parser.secret', self::SECRET);
    }

    private function project(array $attributes = []): Project
    {
        $company = Company::factory()->create();

        return Project::factory()->create(array_merge([
            'company_id' => $company->id,
            'title' => 'Backend engineer',
            'project_description' => 'Java and AWS required.',
            'personnel_requirement' => 'Five years of experience.',
            'minimum_price' => 600000,
            'maximum_price' => 900000,
        ], $attributes));
    }

    private function fakeParseResponse(array $overrides = []): void
    {
        Http::fake([
            'ai-parser.test/*' => Http::response(array_merge([
                'project_id' => 1,
                'title' => 'Backend engineer',
                'required_skills' => [],
                'preferred_skills' => [],
                'unmapped_skills' => [],
                'meta' => [
                    'parser_version' => '1.0.0',
                    'source_hash' => str_repeat('a', 64),
                    'model' => 'llama-3.3-70b',
                    'warnings' => [],
                ],
            ], $overrides), 200),
        ]);
    }

    public function test_parse_project_posts_to_the_jd_endpoint_with_the_shared_secret(): void
    {
        $this->fakeParseResponse();
        $project = $this->project();

        app(AiParsingService::class)->parseProject($project);

        Http::assertSent(function ($request) use ($project) {
            $body = $request->data();

            return $request->url() === 'http://ai-parser.test/v1/parse/jd'
                && $request->hasHeader('X-Internal-Secret', self::SECRET)
                && $body['project_id'] === $project->id
                && $body['project_description'] === 'Java and AWS required.'
                && $body['personnel_requirement'] === 'Five years of experience.'
                && $body['minimum_price'] === 600000;
        });
    }

    public function test_every_request_carries_a_correlation_id(): void
    {
        // One parse has to be traceable from the SES log line into the
        // service's own logs; without this they cannot be lined up.
        $this->fakeParseResponse();

        app(AiParsingService::class)->parseProject($this->project());

        Http::assertSent(fn ($request) => $request->hasHeader('X-Request-Id'));
    }

    public function test_input_hash_is_content_based_and_ignores_the_project_id(): void
    {
        $service = app(AiParsingService::class);

        $a = $this->project(['title' => 'Same', 'project_description' => 'Same text.']);
        $b = $this->project(['title' => 'Same', 'project_description' => 'Same text.']);

        // Two different rows with identical content must fingerprint the same,
        // otherwise the id alone would force a needless re-parse.
        $this->assertSame(
            $service->projectInputHash($a),
            $service->projectInputHash($b)
        );
    }

    public function test_input_hash_changes_when_the_description_changes(): void
    {
        $service = app(AiParsingService::class);
        $project = $this->project(['project_description' => 'Original.']);

        $before = $service->projectInputHash($project);

        $project->project_description = 'Edited.';
        $project->save();

        $this->assertNotSame($before, $service->projectInputHash($project->fresh()));
    }

    public function test_connection_failure_becomes_a_runtime_exception(): void
    {
        Http::fake(fn () => throw new ConnectionException('refused'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('unreachable');

        app(AiParsingService::class)->parseProject($this->project());
    }

    public function test_service_error_detail_is_surfaced_to_the_caller(): void
    {
        // A 422 means the document is unusable. The recruiter-facing message
        // has to say why, not just "failed".
        Http::fake([
            'ai-parser.test/*' => Http::response(
                ['detail' => 'PDF contains no extractable text (likely a scanned image)'],
                422
            ),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('scanned image');

        app(AiParsingService::class)->parseProject($this->project());
    }

    public function test_match_sends_the_jd_the_resume_and_the_talent_facts(): void
    {
        Http::fake([
            'ai-parser.test/*' => Http::response([
                'project_id' => 1, 'talent_id' => 2, 'score' => 77,
                'dimensions' => [], 'reasons' => [], 'blockers' => [],
                'unverified' => [], 'scorer_version' => '1.0.0',
            ], 200),
        ]);

        $company = Company::factory()->create();
        $talent = Talent::factory()->create([
            'company_id' => $company->id,
            'min_monthly_price' => 700000,
            'max_monthly_price' => 850000,
        ]);

        app(AiParsingService::class)->match(
            ['project_id' => 1, 'title' => 'x'],
            ['talent_id' => 2, 'skills' => []],
            $talent
        );

        Http::assertSent(function ($request) use ($talent) {
            $body = $request->data();

            return $request->url() === 'http://ai-parser.test/v1/match'
                && $body['talent']['talent_id'] === $talent->id
                && $body['talent']['min_monthly_price'] === 700000
                && array_key_exists('location_ids', $body['talent'])
                && array_key_exists('sub_category_ids', $body['talent']);
        });
    }
}
