<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Make "one interview per instant" an invariant the database holds.
 *
 * The slots migration claims this is already true — "the unique index on
 * (starts_at) for *selected* rows is what enforces that, not application
 * politeness". No such index was ever created. What enforced it was a
 * `SELECT ... WHERE status = 'selected' AND starts_at = ?` run inside the
 * confirming transaction, and that check cannot work:
 *
 *   Candidate A confirms slot #10 at 10:00. Candidate B confirms slot #20,
 *   also at 10:00. Each transaction locks *its own* row — different rows, so
 *   neither waits. Each then runs the check, and under REPEATABLE READ each
 *   reads a snapshot taken before the other wrote. Both see the instant free.
 *   Both commit. Two candidates are booked for 10:00 on a system configured
 *   with INTERVIEW_MAX_CONCURRENT_CALLS=1, so the second dial is refused and
 *   somebody sits waiting for a call that was never going to come.
 *
 * Check-then-act cannot be fixed by checking harder. The only thing that
 * serialises writers who touch no common row is a constraint, so this adds
 * one.
 *
 * It is a separate nullable column rather than an index on `starts_at`
 * because the constraint applies to *selected* rows only — several candidates
 * are deliberately offered the same window, and that is the design. A partial
 * index would say so directly but MySQL has none, so the predicate is carried
 * in the data instead: `reserved_instant` holds the start time while the row
 * is SELECTED and NULL in every other state. Both MySQL and SQLite exclude
 * NULLs from uniqueness, so any number of offered rows coexist and at most one
 * booking can exist per instant.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interview_slots', function (Blueprint $table) {
            $table->timestamp('reserved_instant')->nullable()->after('selected_at');
        });

        $this->backfill();

        Schema::table('interview_slots', function (Blueprint $table) {
            $table->unique('reserved_instant', 'interview_slots_reserved_instant_unique');
        });
    }

    public function down(): void
    {
        Schema::table('interview_slots', function (Blueprint $table) {
            // Named explicitly: dropColumn does not remove the index, and an
            // orphaned one makes the *next* migration fail rather than this
            // one, which is a long way from the cause.
            $table->dropUnique('interview_slots_reserved_instant_unique');
            $table->dropColumn('reserved_instant');
        });
    }

    /**
     * Claim the instant for each booking that already exists.
     *
     * Done one row at a time, oldest booking first, so that if the data
     * already contains a double booking the *earliest* confirmation keeps the
     * instant and the later one is left NULL. The alternative — a single bulk
     * UPDATE — would simply fail when the unique index was added two lines
     * later, and a migration that cannot run on production data is a migration
     * that gets skipped.
     *
     * A row left NULL here is a pre-existing conflict this migration cannot
     * undo: both candidates were told they were booked. It is logged with its
     * id so somebody can go and ring one of them.
     */
    private function backfill(): void
    {
        $claimed = [];
        $conflicts = [];

        DB::table('interview_slots')
            ->where('status', 'selected')
            ->select('id', 'starts_at')
            // Oldest confirmation first, with `id` to break ties on rows that
            // predate `selected_at` being written.
            ->orderBy('selected_at')
            ->orderBy('id')
            ->chunk(500, function ($rows) use (&$claimed, &$conflicts) {
                foreach ($rows as $row) {
                    $key = (string) $row->starts_at;

                    if (isset($claimed[$key])) {
                        $conflicts[] = $row->id;

                        continue;
                    }

                    $claimed[$key] = true;

                    DB::table('interview_slots')
                        ->where('id', $row->id)
                        ->update(['reserved_instant' => $row->starts_at]);
                }
            });

        if ($conflicts !== []) {
            Log::warning('interview.double_booked_slots_found', [
                'slot_ids' => $conflicts,
                'note' => 'Two confirmations share one instant. The earlier one keeps it; '
                    .'these need a human to reschedule.',
            ]);
        }
    }
};
