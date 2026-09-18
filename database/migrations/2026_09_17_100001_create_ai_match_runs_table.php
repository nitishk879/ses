<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** One press of "Analyze / Match Candidates", from queued to emailed. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_match_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();

            // Who pressed the button — they are who gets the email. Nullable
            // because a run started from the console has no user, and
            // nullOnDelete because a departed recruiter must not cascade away
            // the record of a run that happened.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('status', 16)->default('queued');
            $table->string('batch_id', 36)->nullable();

            // Written once at finalisation, from one grouped query.
            $table->unsignedInteger('candidates_total')->default(0);
            $table->unsignedInteger('scored')->default(0);
            $table->unsignedInteger('parse_failures')->default(0);
            // Cleared every must-have AND reached the threshold.
            $table->unsignedInteger('matched')->default(0);
            // Cleared the threshold but has a must-have nobody could verify.
            $table->unsignedInteger('needs_review')->default(0);

            $table->unsignedTinyInteger('threshold')->default(70);

            $table->text('failure_reason')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            // Set the moment the summary email goes out; re-entry checks it.
            $table->timestamp('notified_at')->nullable();

            $table->timestamps();

            // "The latest run for this project", which every screen asks for.
            $table->index(['project_id', 'created_at'], 'ai_match_runs_latest');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_match_runs');
    }
};
