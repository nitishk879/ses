<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** The requirements a recruiter can mark as must-have for one project. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();

            // skill | experience | language | location | budget — the closed
            // set the scorer can actually answer yes/no about.
            $table->string('kind', 16);

            // Stable identity across re-parses.
            $table->string('requirement_key', 191);
            $table->unique(['project_id', 'requirement_key'], 'project_requirements_unique');

            $table->string('label');

            // The one column the recruiter owns. Nothing automated writes it.
            $table->boolean('is_mandatory')->default(false);

            // kind=experience: the threshold to compare against. Stored in
            // months because that is what the resume parser reports.
            $table->unsignedSmallInteger('min_months')->nullable();
            // kind=language: "N2", "business", "native".
            $table->string('level', 32)->nullable();

            // 'ai' — extracted from the JD; 'manual' — typed by the recruiter
            // because the parser missed it. A manual row is never removed by a
            // re-parse.
            $table->string('source', 8)->default('ai');

            // Whether the current JD parse still contains this requirement.
            $table->boolean('in_latest_parse')->default(true);

            $table->text('evidence')->nullable();

            // The JD's own ordering, so the list a recruiter ticks reads in
            // the order the document states things.
            $table->unsignedSmallInteger('position')->default(0);

            $table->timestamps();

            // Powers "the must-haves for this project", which is read once per
            // candidate on every scoring run.
            $table->index(['project_id', 'is_mandatory'], 'project_requirements_mandatory');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_requirements');
    }
};
