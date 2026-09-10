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
        Schema::create('interview_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_question_id')->constrained('interview_questions')->cascadeOnDelete();

            /*
             * Text answer when the interview channel
             * directly provides text.
             */
            $table->text('answer_text')->nullable();

            /*
             * Transcript generated from the candidate's
             * spoken response.
             */
            $table->text('transcript')->nullable();

            /*
             * Reference to the stored audio recording.
             *
             * Do not store the audio binary in this table.
             */
            $table->string('audio_path')->nullable();

            /*
             * Duration of the candidate's audio response.
             */
            $table->unsignedInteger('audio_duration_seconds')
                ->nullable();

            /*
             * Timing of the actual answer.
             */
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            /*
             * STT/provider metadata, confidence,
             * provider IDs, etc.
             */
            $table->json('metadata')->nullable();
            $table->timestamps();

            /*
             * Currently one answer per question.
             */
            $table->unique('interview_question_id');

            $table->index([
                'interview_question_id',
                'started_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_answers');
    }
};
