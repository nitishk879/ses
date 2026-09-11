<?php

namespace App\Exceptions\Interview;

use RuntimeException;

/**
 * The time the candidate chose is no longer available.
 *
 * An expected branch, not a fault. Offering does not reserve, so several
 * candidates are legitimately holding an email that proposes the same 10:00
 * window — only one of them can have it, and the rest need to be told plainly
 * and shown what is left.
 *
 * Callers should render this, never report it: a 500 here would tell a
 * candidate the system is broken when it is working exactly as designed.
 */
class SlotUnavailable extends RuntimeException
{
}
