<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Which part of the project each requirement was read out of.
 *
 * The matching screen lists requirements under the heading "Requirements from
 * this job description", and for half of them that heading is simply wrong.
 * A recruiter looking at `Language · Japanese`, `Location · Works in one of
 * the project locations` and `Budget · Monthly rate within ¥1,200,000` goes
 * to the project form to find where they were typed, and finds nothing
 * resembling that list — because three different things are being shown as
 * one: taxonomy pivots the recruiter ticked, project columns they filled in,
 * and phrases a language model pulled out of the description.
 *
 * `source` already exists but answers a different question — *who* created the
 * row, the pipeline or a person — and the stale-sweep keys off it, so
 * overloading it would mean a form-derived row surviving a re-parse that no
 * longer mentions it.
 *
 * Derivable from `evidence` today, roughly, by string-matching the sentinel
 * the parser writes. That is a guess standing in for a fact the extractor
 * already knows at the moment it builds the row, so it is recorded instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_requirements', function (Blueprint $table) {
            /*
             * project_form — a pivot or column on the SES project form.
             * jd_text      — prose, pulled out of the description by the parser.
             * recruiter    — typed on the matching screen.
             * unstated     — offered so it can be set, but nothing states it yet.
             */
            $table->string('origin', 16)->default('jd_text')->after('source');
        });

        // Existing rows: the sentinel the JD parser writes for anything that
        // came from the form, plus the two kinds that have no prose source at
        // all. Everything else really was read out of the text.
        DB::table('project_requirements')
            ->where('evidence', 'Selected in the SES project form')
            ->orWhereIn('kind', ['location', 'budget'])
            ->update(['origin' => 'project_form']);

        DB::table('project_requirements')
            ->where('source', 'manual')
            ->update(['origin' => 'recruiter']);
    }

    public function down(): void
    {
        Schema::table('project_requirements', function (Blueprint $table) {
            $table->dropColumn('origin');
        });
    }
};
