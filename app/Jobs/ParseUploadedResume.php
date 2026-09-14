<?php

namespace App\Jobs;

use App\Services\AiParsingService;
use App\Support\ResumeFormSuggestions;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Read a just-uploaded CV and cache form values for the page that is waiting.
 *
 * This runs on the queue rather than inside the upload request because the
 * work takes as long as it takes. Parsing a one-page CV was measured at 20
 * seconds against the live model, and a real 職務経歴書 runs to several
 * chunked model calls. The web tier will not wait that long: php-fpm's
 * `max_execution_time` on this box is 30 seconds and nginx's
 * `fastcgi_read_timeout` defaults to 60, so a synchronous version does not
 * fail gracefully — it dies mid-parse and hands the recruiter a 504 with no
 * explanation and no filled form.
 *
 * It is also the wrong thing to do to the server: a php-fpm worker blocked on
 * a GPU for a minute is a worker nobody else's page request can use.
 *
 * The result goes into the cache under a single-use token, which the browser
 * polls. Nothing is written to the database: the candidate does not exist yet
 * and may never be created, and a discarded upload should leave no trace.
 */
class ParseUploadedResume implements ShouldQueue
{
    use Queueable;

    /** One attempt. A failed parse is nearly always the document, not luck. */
    public int $tries = 1;

    public int $timeout = 300;

    /** How long a finished result waits to be collected. */
    public const TTL_SECONDS = 900;

    public function __construct(
        public string $token,
        public string $path,
        public int $userId,
        public string $originalName,
    ) {
    }

    public static function cacheKey(string $token): string
    {
        return 'resume-autofill:'.$token;
    }

    public function handle(AiParsingService $parser): void
    {
        try {
            if (! Storage::disk('local')->exists($this->path)) {
                $this->store(['status' => 'failed']);

                return;
            }

            $parsed = $parser->parseUploadedResume(new UploadedFile(
                Storage::disk('local')->path($this->path),
                $this->originalName,
                test: true,
            ));

            $suggestions = ResumeFormSuggestions::fromParsedResume($parsed);

            $this->store([
                'status' => 'done',
                'fields' => $suggestions['fields'],
                'unmapped_skills' => $suggestions['unmapped_skills'],
            ]);

            // Shapes only. The payload is somebody's CV.
            Log::info('talent.resume_autofill.parsed', [
                'fields' => count($suggestions['fields']),
                'by' => $this->userId,
            ]);
        } catch (Throwable $e) {
            Log::warning('talent.resume_autofill.unavailable', ['error' => $e->getMessage()]);
            $this->store(['status' => 'failed']);
        } finally {
            // The upload exists only to be read once. Leaving it behind would
            // accumulate strangers' CVs on disk for a form that was abandoned.
            Storage::disk('local')->delete($this->path);
        }
    }

    /**
     * Record a terminal state even when the job dies outright.
     *
     * Without this the browser polls a key that will never appear and has to
     * guess, from a timeout, whether the parse failed or is merely slow.
     */
    public function failed(?Throwable $e): void
    {
        Log::error('talent.resume_autofill.failed', ['error' => $e?->getMessage()]);
        $this->store(['status' => 'failed']);
        Storage::disk('local')->delete($this->path);
    }

    /** @param array<string, mixed> $payload */
    private function store(array $payload): void
    {
        // The owner travels with the result: whoever polls has to prove they
        // are the person who uploaded it, and a token alone does not.
        Cache::put(
            self::cacheKey($this->token),
            $payload + ['user_id' => $this->userId],
            self::TTL_SECONDS
        );
    }
}
