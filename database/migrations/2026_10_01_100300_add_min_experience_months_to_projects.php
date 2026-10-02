<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How much experience a project asks for, as the recruiter typed it.
 *
 * Until now the only source of an experience requirement was a language model
 * reading the description and finding a phrase like "3年以上". Most
 * descriptions say no such thing, so most projects had no experience
 * requirement at all — and the project form had no field to give one.
 *
 * A new column rather than the existing `projects.experience`: that one is a
 * legacy JSON list (`[1,2,3]` in the seeds) with no form control and no
 * defined meaning, and `apply-for-project.blade.php` still iterates it with
 * `json_decode()` — storing a number there would break that page.
 *
 * Stored in months, the unit the resume parser reports and the scorer
 * compares in. The form asks for years and months and adds them up.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Null means "not required", which is different from zero only in
            // that zero is never written: an empty form stores null.
            $table->unsignedSmallInteger('min_experience_months')->nullable()->after('experience');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('min_experience_months');
        });
    }
};
