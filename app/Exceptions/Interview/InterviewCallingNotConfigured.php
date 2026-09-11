<?php

namespace App\Exceptions\Interview;

use RuntimeException;

/**
 * The AI service is running but cannot place calls.
 *
 * Its environment is missing the DENAI, TWILIO and DOCUMENTDB variables. The
 * service answers 501 and names them, which is why the message is passed
 * through verbatim — it is the actionable part.
 *
 * **Never retry this.** It resolves when somebody deploys, not when time
 * passes, so the job that catches it fails the attempt with a reason a human
 * can read rather than burning its retry budget.
 */
class InterviewCallingNotConfigured extends RuntimeException
{
}
