<?php

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
        Schema::create('interview_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_id')->constrained('interviews')->cascadeOnDelete();

            /*
             * Attempt number for this interview.
             *
             * Example:
             * 1 = first attempt
             * 2 = retry
             * 3 = second retry
             */
            $table->unsignedInteger('attempt_number');

            /*
             * Execution status.
             */
            $table->string('status')->default('pending')->index();

            /*
             * Actual channel used for this attempt.
             *
             * Examples:
             * google_meet
             * phone
             */
            $table->string('channel')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            /*
             * Actual duration in seconds.
             *
             * The application will enforce the
             * five-minute interview limit.
             */
            $table->unsignedInteger('duration_seconds')->nullable();

            /*
             * Reason when an attempt fails.
             */
            $table->text('failure_reason')->nullable();

            /*
             * External provider reference.
             *
             * Example:
             * Google Meet / telephony provider call ID.
             */
            $table->string('provider_reference')->nullable();

            /*
             * Provider-specific or execution-specific data.
             */
            $table->json('metadata')->nullable();
            $table->timestamps();

            /*
             * An interview cannot have two attempts
             * with the same attempt number.
             */
            $table->unique([
                'interview_id',
                'attempt_number',
            ]);

            $table->index([
                'interview_id',
                'status',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_attempts');
    }
};
