<?php

namespace App\Support;

use Stringable;

/**
 * A string that has already been HTML-escaped.
 *
 * Exists so {@see ResumeFormSuggestions} can hand a partly-marked-up line to
 * its own list builder without that builder having to guess whether to escape
 * it. Guessing is how content gets double-escaped (a candidate named
 * "O'Brien" arriving as "O&amp;#039;Brien") or, far worse, escaped zero times.
 *
 * Deliberately not a general-purpose "raw HTML" wrapper: the only way to make
 * one is to pass a string you have just escaped yourself, a line or two above.
 */
final class PreEscaped implements Stringable
{
    public function __construct(private readonly string $html)
    {
    }

    public function __toString(): string
    {
        return $this->html;
    }
}
