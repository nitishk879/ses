<?php

namespace App\Exceptions\Interview;

use RuntimeException;

/**
 * The AI interview service could not be reached, or failed transiently.
 *
 * Retryable. This is the one that means "wait and try again".
 *
 * Kept distinct from {@see InterviewCallingNotConfigured} deliberately.
 * Collapsing the two is what produced the situation this feature was built
 * out of: a deployment missing five environment variables looked, from the
 * Laravel side, exactly like the service being down. One is fixed by a deploy
 * and the other by waiting, and a retry loop that cannot tell them apart will
 * sit retrying a configuration gap forever.
 */
class InterviewAiUnavailable extends RuntimeException
{
}
