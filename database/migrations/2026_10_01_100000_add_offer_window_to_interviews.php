<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The open booking window behind the candidate's calendar.
 *
 * Until now an invitation was a list: three rows in `interview_slots`, written
 * when the email went out, and the candidate picked one of them. The calendar
 * replaces that list with a range — "any half-hour between 8am and 8pm, for
 * the next fortnight" — which cannot be expressed as rows. Fourteen days of
 * half-hours is 336 windows per candidate; writing them as `offered` rows
 * would put six figures of records in the table to describe times nobody has
 * asked for, and every one of them would have to be swept when the invitation
 * expired.
 *
 * So the offer becomes two timestamps and the slot row is written at the
 * moment of booking instead. The columns are stored rather than derived from
 * config for the usual reason: an invitation issued when the window was a
 * fortnight must still mean a fortnight after somebody shortens the setting,
 * or a candidate holding a live email is told their link expired early.
 *
 * `offer_mode` defaults to `fixed`, so every invitation already in flight when
 * this ships keeps the behaviour it was sent under.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            // calendar | fixed — see App\Enums\InterviewOfferMode.
            $table->string('offer_mode', 16)
                ->default('fixed')
                ->after('match_score');

            /*
             * The bookable range, in UTC like every other instant here.
             *
             * Null in `fixed` mode: there is no range, there is a list. Both
             * are set together or neither is, and `offer_mode` is what says
             * which to read — a nullable pair with no discriminator is the
             * kind of thing that gets read as "window not computed yet".
             */
            $table->timestamp('offer_window_starts_at')->nullable()->after('offer_mode');
            $table->timestamp('offer_window_ends_at')->nullable()->after('offer_window_starts_at');
        });

        /*
         * Existing rows said `fixed` by the column default, but only the ones
         * that actually carry slots mean it. Stated explicitly so the column
         * is never merely defaulted — a value nobody wrote is one nobody can
         * be sure of.
         */
        DB::table('interviews')->update(['offer_mode' => 'fixed']);
    }

    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            $table->dropColumn([
                'offer_mode',
                'offer_window_starts_at',
                'offer_window_ends_at',
            ]);
        });
    }
};
