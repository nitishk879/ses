<?php

namespace Tests\Unit;

use App\Models\AiMatch;
use PHPUnit\Framework\TestCase;

/**
 * Model logic that needs no database.
 */
class AiMatchTest extends TestCase
{
    private function match(array $attributes = []): AiMatch
    {
        $match = new AiMatch();
        $match->forceFill(array_merge([
            'score' => 80,
            'jd_source_hash' => 'jd-hash',
            'resume_source_hash' => 'resume-hash',
            'payload' => [
                'reasons' => ['A strength', 'Another strength'],
                'blockers' => ['Japanese is below the required N2.'],
                'unverified' => ['Resume does not state total experience.'],
            ],
        ], $attributes));

        return $match;
    }

    public function test_a_score_from_the_current_parses_is_not_stale(): void
    {
        $this->assertFalse($this->match()->isStale('jd-hash', 'resume-hash'));
    }

    public function test_a_reparsed_jd_makes_the_score_stale(): void
    {
        $this->assertTrue($this->match()->isStale('new-jd-hash', 'resume-hash'));
    }

    public function test_a_reparsed_resume_makes_the_score_stale(): void
    {
        $this->assertTrue($this->match()->isStale('jd-hash', 'new-resume-hash'));
    }

    public function test_missing_hashes_count_as_stale(): void
    {
        // A parse that has gone away must never leave a score looking current.
        $this->assertTrue($this->match()->isStale(null, null));
    }

    public function test_reasons_blockers_and_unverified_are_read_from_the_payload(): void
    {
        $match = $this->match();

        $this->assertCount(2, $match->reasons());
        $this->assertSame(['Japanese is below the required N2.'], $match->blockers());
        $this->assertCount(1, $match->unverified());
    }

    public function test_a_payload_without_those_keys_returns_empty_arrays(): void
    {
        // Older rows, or a scorer that stopped emitting a field, must not
        // fatal the candidate list.
        $match = $this->match(['payload' => ['score' => 10]]);

        $this->assertSame([], $match->reasons());
        $this->assertSame([], $match->blockers());
        $this->assertSame([], $match->unverified());
    }
}
