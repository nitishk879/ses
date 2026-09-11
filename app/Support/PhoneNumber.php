<?php

namespace App\Support;

use Stringable;

/**
 * A phone number in the only form a carrier will accept.
 *
 * Every talent number in the database today is a bare local string — no
 * country code, no plus. Twilio rejects those outright (error 21211), so
 * without this the interview feature cannot dial a single existing candidate.
 * Storing the raw value and normalising at the point of use is deliberate: the
 * registration form is not ours to change here, and a migration that rewrote
 * everybody's number in place would be a destructive guess about numbers we
 * have not verified.
 *
 * The rules are narrow on purpose. This is not a libphonenumber replacement —
 * it does not validate that a number is assignable, only that it is in a shape
 * a carrier can route. Where the input is ambiguous it refuses rather than
 * guessing, because the cost of guessing wrong is phoning a stranger.
 */
final class PhoneNumber implements Stringable
{
    /**
     * National number lengths we will accept per region.
     *
     * Japan: 10 digits for landlines (03-xxxx-xxxx), 11 for mobile
     * (090/080/070-xxxx-xxxx). India: 10. These are the two regions the SES
     * data actually contains.
     *
     * @var array<string, array{code: string, lengths: array<int>}>
     */
    private const REGIONS = [
        'JP' => ['code' => '81', 'lengths' => [9, 10, 11]],
        'IN' => ['code' => '91', 'lengths' => [10]],
        'US' => ['code' => '1', 'lengths' => [10]],
    ];

    private function __construct(
        public readonly string $e164,
    ) {
    }

    public function __toString(): string
    {
        return $this->e164;
    }

    /**
     * Parse a stored number into E.164, or null when it cannot be trusted.
     *
     * Returning null rather than throwing is the right shape here: an
     * un-dialable number is an ordinary data condition for a candidate pool
     * entered by hand over two years, not an exceptional one.
     *
     * @param  string|null  $raw            whatever is in the column
     * @param  string       $defaultRegion  assumed only when the number carries no country code
     */
    public static function parse(?string $raw, string $defaultRegion = 'JP'): ?self
    {
        $raw = trim((string) $raw);

        if ($raw === '') {
            return null;
        }

        // Already international: keep the country code the data gave us and
        // never second-guess it with the default region.
        if (str_starts_with($raw, '+')) {
            $digits = preg_replace('/\D/', '', $raw) ?? '';

            return self::isPlausibleInternational($digits)
                ? new self('+'.$digits)
                : null;
        }

        $digits = preg_replace('/\D/', '', $raw) ?? '';

        if ($digits === '') {
            return null;
        }

        $region = self::REGIONS[strtoupper($defaultRegion)] ?? null;

        if ($region === null) {
            return null;
        }

        // "00" is the international access prefix in both JP and IN. A number
        // starting with it is already international, just written the way you
        // would dial it from inside the country.
        if (str_starts_with($digits, '00')) {
            $international = substr($digits, 2);

            return self::isPlausibleInternational($international)
                ? new self('+'.$international)
                : null;
        }

        // A national number written with its trunk prefix: 090-1234-5678 in
        // Japan is +81 90 1234 5678 — the leading zero is dropped, not kept.
        $national = ltrim($digits, '0');

        // Someone may already have typed the country code without the plus.
        // Only treat it as such when what follows is a valid national length,
        // otherwise "8012345678" (a real Japanese mobile) would be mangled
        // into "+80 12345678".
        if (str_starts_with($national, $region['code'])) {
            $withoutCode = substr($national, strlen($region['code']));

            if (in_array(strlen($withoutCode), $region['lengths'], true)
                || in_array(strlen(ltrim($withoutCode, '0')), $region['lengths'], true)) {
                $national = ltrim($withoutCode, '0');
            }
        }

        if (! in_array(strlen($national), $region['lengths'], true)) {
            // Wrong length for this region. Refuse rather than dial something
            // that is probably a typo or an extension.
            return null;
        }

        return new self('+'.$region['code'].$national);
    }

    /**
     * Whether a value is already a usable E.164 string.
     */
    public static function isE164(?string $raw): bool
    {
        return (bool) preg_match('/^\+[1-9]\d{7,14}$/', (string) $raw);
    }

    /**
     * E.164 allows 15 digits total and every real country code starts 1-9.
     * Eight is the shortest national number in use anywhere.
     */
    private static function isPlausibleInternational(string $digits): bool
    {
        return (bool) preg_match('/^[1-9]\d{7,14}$/', $digits);
    }

    /**
     * The number with everything but the last two digits hidden.
     *
     * Used in logs and API responses. A screening call is placed to a private
     * individual, and the identifier for that call must not be the thing that
     * puts their number into a log aggregator.
     */
    public function masked(): string
    {
        $tail = substr($this->e164, -2);

        return str_repeat('*', max(0, strlen($this->e164) - 2)).$tail;
    }
}
