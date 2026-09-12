<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Time-slot offers, and the invitation that carries them.
 *
 * Tasks 5-7: a shortlisted candidate is invited, offered three times by email,
 * and picks one. The chosen time lands in `interviews.scheduled_at`, which is
 * exactly what the existing scheduler already watches — so selection hands off
 * to the call pipeline with no further plumbing.
 *
 * Two decisions worth stating, because neither is obvious from the columns:
 *
 * **Offering is not reserving.** Several candidates may be offered the same
 * window. Reserving on offer would idle most of the calendar waiting for
 * replies that often never come; instead the first candidate to confirm takes
 * it, and a later one is told plainly that it has gone and shown what is left.
 * The unique index on (starts_at) for *selected* rows is what enforces that,
 * not application politeness.
 *
 * **The token lives on the interview, not on the slot.** One link opens a page
 * showing every option, which is what the task sheet describes and what a
 * candidate expects; a per-slot link would mean three links in one email and
 * no way to see them side by side.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interview_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_id')
                ->constrained('interviews')
                ->cascadeOnDelete();

            /*
             * Stored in UTC, always. `interviews.timezone` says which zone the
             * candidate should see them rendered in — a slot that means 10:00
             * in Tokyo and 10:00 in Delhi is two different instants, and only
             * one of them is the one we will dial at.
             */
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');

            /*
             * offered | selected | expired | released
             */
            $table->string('status', 16)->default('offered')->index();

            /*
             * 1, 2, 3 — the order they were presented in. Kept so a recruiter
             * reading the record later can see what was offered, not just what
             * was taken.
             */
            $table->unsignedTinyInteger('position');

            $table->timestamp('selected_at')->nullable();
            $table->timestamps();

            /*
             * An interview cannot offer the same instant twice, and cannot
             * offer the same position twice.
             */
            $table->unique(['interview_id', 'starts_at']);
            $table->unique(['interview_id', 'position']);

            // The lookup the generator does when checking what is already taken.
            $table->index(['status', 'starts_at']);
        });

        Schema::table('interviews', function (Blueprint $table) {
            /*
             * Opaque, single-use link the candidate follows. 64 hex characters
             * of CSPRNG output.
             *
             * Stored in the clear rather than hashed: it is already sitting in
             * the candidate's inbox, so hashing protects only against a reader
             * of this column who cannot also read the mail log — a narrow
             * threat next to the cost of losing the ability to look a link up.
             * It is invalidated the moment a slot is chosen, which is the
             * control that actually matters.
             */
            $table->string('invitation_token', 64)->nullable()->unique()->after('metadata');
            $table->timestamp('invitation_sent_at')->nullable()->after('invitation_token');

            /*
             * After this, the offer is stale: the slots inside it may have been
             * taken by others and the times may have passed.
             */
            $table->timestamp('invitation_expires_at')->nullable()->after('invitation_sent_at');
            $table->timestamp('slot_selected_at')->nullable()->after('invitation_expires_at');

            /*
             * The match score this invitation was issued on. Recorded because
             * the threshold is a setting that will be tuned, and a shortlist
             * decision has to remain explainable after it changes.
             */
            $table->unsignedTinyInteger('match_score')->nullable()->after('slot_selected_at');

            $table->index(['status', 'invitation_expires_at']);
        });
    }

    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            /*
             * Indexes come off before the columns they cover, and the unique
             * one is named explicitly.
             *
             * `->unique()` on the column above creates
             * `interviews_invitation_token_unique`, which `dropColumn` does not
             * clean up. Leaving it in place made this rollback fail with
             * "error in index ... after drop column: no such column" — on
             * SQLite immediately, and on MySQL as an orphaned index. A
             * rollback is what you reach for when a deploy has gone wrong, so
             * it failing then is the worst possible time to find out.
             */
            $table->dropUnique(['invitation_token']);
            $table->dropIndex(['status', 'invitation_expires_at']);

            $table->dropColumn([
                'invitation_token',
                'invitation_sent_at',
                'invitation_expires_at',
                'slot_selected_at',
                'match_score',
            ]);
        });

        // Dropped last: its rows reference `interviews`, and on a driver that
        // enforces foreign keys the parent must still be intact.
        Schema::dropIfExists('interview_slots');
    }
};
