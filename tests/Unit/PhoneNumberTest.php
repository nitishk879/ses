<?php

namespace Tests\Unit;

use App\Support\PhoneNumber;
use PHPUnit\Framework\TestCase;

/**
 * The data-level blocker: not one talent phone in the live database is in a
 * form Twilio will dial. These tests pin the exact conversions, because the
 * cost of getting one wrong is calling a stranger.
 */
class PhoneNumberTest extends TestCase
{
    /** @dataProvider japaneseNumbers */
    public function test_japanese_numbers_normalise_to_e164(string $input, string $expected): void
    {
        $this->assertSame($expected, (string) PhoneNumber::parse($input, 'JP'));
    }

    public static function japaneseNumbers(): array
    {
        return [
            'mobile with hyphens' => ['090-1234-5678', '+819012345678'],
            'mobile bare' => ['09012345678', '+819012345678'],
            'landline with hyphens' => ['03-1234-5678', '+81312345678'],
            'with spaces' => ['090 1234 5678', '+819012345678'],
            'with parentheses' => ['(03) 1234-5678', '+81312345678'],
            'already international' => ['+81 90 1234 5678', '+819012345678'],
            'country code, no plus' => ['819012345678', '+819012345678'],
            'international access prefix' => ['00819012345678', '+819012345678'],
            'surrounding whitespace' => ['  09012345678  ', '+819012345678'],
        ];
    }

    /**
     * The exact shape of every number in the live `users` table.
     *
     * Ten bare digits and no country code. Twilio rejects these with error
     * 21211, which is why the feature could not dial a single existing
     * candidate before this class existed.
     */
    public function test_the_bare_local_numbers_actually_in_the_database(): void
    {
        foreach (['6485597620', '6006848118', '7198290462', '9227866503'] as $stored) {
            $parsed = PhoneNumber::parse($stored, 'IN');

            $this->assertNotNull($parsed, "[{$stored}] should normalise under IN");
            $this->assertTrue(PhoneNumber::isE164((string) $parsed));
        }
    }

    public function test_an_international_number_keeps_its_own_country_code(): void
    {
        // The default region must never override a code the data supplied.
        $this->assertSame('+14155552671', (string) PhoneNumber::parse('+1 415 555 2671', 'JP'));
    }

    /** @dataProvider unusableNumbers */
    public function test_unusable_input_is_refused_rather_than_guessed(?string $input): void
    {
        $this->assertNull(PhoneNumber::parse($input, 'JP'));
    }

    public static function unusableNumbers(): array
    {
        return [
            'null' => [null],
            'empty' => [''],
            'whitespace' => ['   '],
            'letters only' => ['not a phone'],
            'far too short' => ['1234'],
            'extension, not a number' => ['1234567890123456789'],
            'plus with nothing' => ['+'],
            'plus zero country code' => ['+0123456789'],
        ];
    }

    public function test_wrong_length_for_the_region_is_refused(): void
    {
        // Seven digits is not a Japanese national number; dialling it would be
        // dialling something else entirely.
        $this->assertNull(PhoneNumber::parse('1234567', 'JP'));
    }

    public function test_a_japanese_mobile_beginning_80_is_not_mistaken_for_a_country_code(): void
    {
        // 80 is also a country-code prefix shape. Treating "8012345678" as
        // "+80 12345678" would silently dial a different country.
        $this->assertSame('+818012345678', (string) PhoneNumber::parse('080-1234-5678', 'JP'));
    }

    public function test_an_unknown_region_is_refused(): void
    {
        $this->assertNull(PhoneNumber::parse('09012345678', 'ZZ'));
    }

    /** @dataProvider e164Checks */
    public function test_e164_detection(?string $input, bool $expected): void
    {
        $this->assertSame($expected, PhoneNumber::isE164($input));
    }

    public static function e164Checks(): array
    {
        return [
            ['+819012345678', true],
            ['+14155552671', true],
            ['09012345678', false],
            ['819012345678', false],
            ['+0123456789', false],
            ['', false],
            [null, false],
        ];
    }

    public function test_masking_leaves_only_the_last_two_digits(): void
    {
        $masked = PhoneNumber::parse('090-1234-5678', 'JP')->masked();

        $this->assertStringEndsWith('78', $masked);
        $this->assertStringNotContainsString('9012', $masked);
        $this->assertSame(strlen('+819012345678'), strlen($masked));
    }
}
