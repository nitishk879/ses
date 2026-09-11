<?php

namespace App\Exceptions\Interview;

use RuntimeException;
use Throwable;

/**
 * Every interview call slot is occupied.
 *
 * Retryable, but only after the delay the service asked for. Interview calls
 * are limited to one at a time because the shared TTS batches on a 30ms
 * window, and two concurrent calls have been measured producing 5-14 seconds
 * of dead air — which a candidate hears as a dropped line. Retrying sooner
 * does not get a slot, it just degrades the call already in progress.
 */
class InterviewAiBusy extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $retryAfterSeconds = 120,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
