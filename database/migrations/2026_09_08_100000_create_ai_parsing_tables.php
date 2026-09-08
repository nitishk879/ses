<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Storage for the AI parsing service.
 *
 * SES stays the system of record: the Python service computes, SES stores. The
 * three tables are deliberately separate from `projects` / `talent` so a
 * re-parse never touches recruiter-entered data, and so dropping the feature
 * is a matter of dropping tables.
 *
 * `source_hash` is what keeps this cheap. It is the SHA-256 of the normalized
 * source text as computed by the parser; when it is unchanged there is nothing
 * to re-parse, and a whole candidate pool can be re-scored without a single
 * LLM call.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_jd_parses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();

            // One current parse per project. A re-parse updates this row rather
            // than appending, so readers never have to work out which of
            // several rows is the live one.
            $table->unique('project_id');

            $table->string('parser_version', 32);
            $table->string('source_hash', 64);
            $table->json('payload');
            $table->timestamp('parsed_at');
            $table->timestamps();

            // Cheap "has the JD text changed since we parsed it?" lookup.
            $table->index(['project_id', 'source_hash']);
        });

        Schema::create('ai_resume_parses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('talent_id')->constrained('talent')->cascadeOnDelete();
            $table->unique('talent_id');

            $table->string('parser_version', 32);
            $table->string('source_hash', 64);
            $table->json('payload');
            $table->timestamp('parsed_at');
            $table->timestamps();

            $table->index(['talent_id', 'source_hash']);
        });

        Schema::create('ai_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('talent_id')->constrained('talent')->cascadeOnDelete();

            // One score per (project, talent). Re-scoring overwrites.
            $table->unique(['project_id', 'talent_id']);

            $table->unsignedTinyInteger('score');
            $table->json('payload');           // dimensions, reasons, blockers
            $table->string('scorer_version', 32);

            // The parse versions this score was derived from. Without them a
            // stale score is indistinguishable from a fresh one after the
            // parser changes.
            $table->string('jd_source_hash', 64);
            $table->string('resume_source_hash', 64);

            $table->timestamp('scored_at');
            $table->timestamps();

            // The index that powers "top 5 for this project".
            $table->index(['project_id', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_matches');
        Schema::dropIfExists('ai_resume_parses');
        Schema::dropIfExists('ai_jd_parses');
    }
};
