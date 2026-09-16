<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Give "years of experience" somewhere to live.
 *
 * The talent form has always asked for it and `store()` has always validated
 * it, but the line that saved it was commented out and `Talent::$fillable`
 * listed an `experience` column that does not exist on the table — writing to
 * it throws "no such column". So the number was collected from the recruiter
 * on every registration and silently discarded.
 *
 * Deliberately separate from `experience_pr`, which is the free-text work
 * history, and from the matching engine's own figure: the match score reads
 * `total_experience_months` out of the parsed CV, never this column. This is
 * the recruiter's stated figure, shown on the profile.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('talent', function (Blueprint $table) {
            // Nullable: every existing row predates the column, and "not
            // recorded" is a different claim from "zero years".
            $table->unsignedTinyInteger('experience_years')
                ->nullable()
                ->after('experience_pr');
        });
    }

    public function down(): void
    {
        Schema::table('talent', function (Blueprint $table) {
            $table->dropColumn('experience_years');
        });
    }
};
