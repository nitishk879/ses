<?php

use App\Enums\InterviewStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();

            $table->string('status')
                ->default(InterviewStatus::PENDING->value)
                ->index();

            /*
             * Interview channel.
             *
             * Examples:
             * - google_meet
             * - phone
             */
            $table->string('channel')->nullable();

            /*
             * Candidate's timezone used for scheduling/display.
             * Actual timestamps should be stored in UTC.
             */
            $table->string('timezone')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            /*
             * Actual interview duration in seconds.
             * This allows us to enforce the 5-minute limit
             * independently of timestamps.
             */
            $table->unsignedInteger('duration_seconds')->nullable();

            /*
             * Current/last failure reason.
             */
            $table->text('failure_reason')->nullable();

            /*
             * External provider reference.
             *
             * For example:
             * Google Meet event ID,
             * phone provider call ID, etc.
             */
            $table->string('provider_reference')->nullable();
            $table->json('metadata')->nullable();

            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            // The existing talent migration creates the singular `talent` table.
            $table->foreignId('talent_id')->constrained('talent')->cascadeOnDelete();

            $table->timestamps();

            $table->index([
                'project_id',
                'talent_id',
            ]);

            $table->index([
                'status',
                'scheduled_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interviews');
    }
};
