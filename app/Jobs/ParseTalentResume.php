<?php

namespace App\Jobs;

use App\Models\AiResumeParse;
use App\Models\Talent;
use App\Services\AiParsingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Structure a candidate's resume into skills and experience.
 *
 * Slower than the JD parse — measured at 4.6-8.8s, and longer again for a CV
 * that has to be split across several model calls to fit the 8k context.
 */
class ParseTalentResume implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** Generous: a long skill sheet is several sequential model calls. */
    public int $timeout = 600;

    /** @return array<int, int> */
    public array $backoff = [30, 120];

    public function __construct(
        public int $talentId,
        public bool $force = false,
    ) {
    }

    public function uniqueId(): string
    {
        return (string) $this->talentId;
    }

    public function handle(AiParsingService $parser): void
    {
        $talent = Talent::find($this->talentId);
        if (! $talent) {
            return;
        }

        // Hash the file before sending it: an unchanged CV must not cost a
        // second parse, and a resume parse is the most expensive call in the
        // pipeline (several sequential model requests for a long skill sheet).
        $inputHash = $parser->resumeInputHash($talent);
        if ($inputHash === null) {
            Log::warning('ai.resume_parse.no_file', ['talent_id' => $this->talentId]);

            return;
        }

        $existing = AiResumeParse::firstWhere('talent_id', $this->talentId);
        if (! $this->force && $existing && $existing->source_hash === $inputHash) {
            Log::info('ai.resume_parse.unchanged', ['talent_id' => $this->talentId]);

            return;
        }

        try {
            $result = $parser->parseResume($talent);
        } catch (RuntimeException $e) {
            // A scanned or missing CV is a data problem, not a transient one.
            // Retrying burns model time and always lands in the same place, so
            // record it and stop.
            Log::warning('ai.resume_parse.unusable', [
                'talent_id' => $this->talentId,
                'reason' => $e->getMessage(),
            ]);
            $this->fail($e);

            return;
        }

        AiResumeParse::updateOrCreate(
            ['talent_id' => $this->talentId],
            [
                'parser_version' => $result['meta']['parser_version'] ?? 'unknown',
                'source_hash' => $inputHash,
                'payload' => $result,
                'parsed_at' => now(),
            ]
        );

        // Shapes only — never the extracted content, which is personal data.
        Log::info('ai.resume_parse.stored', [
            'talent_id' => $this->talentId,
            'skills' => count($result['skills'] ?? []),
            'truncated' => $result['meta']['truncated'] ?? false,
        ]);
    }

    public function failed(Throwable $e): void
    {
        Log::error('ai.resume_parse.failed', [
            'talent_id' => $this->talentId,
            'error' => $e->getMessage(),
        ]);
    }
}
