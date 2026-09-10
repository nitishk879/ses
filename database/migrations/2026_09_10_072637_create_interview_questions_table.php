<?php

use App\Enums\InterviewQuestionTypeEnum;
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
        Schema::create('interview_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_attempt_id')
                ->constrained('interview_attempts')
                ->cascadeOnDelete();

            /*
             * Position within this interview attempt.
             *
             * 1, 2, 3, 4...
             */
            $table->unsignedInteger('sequence');

            /*
             * Question category.
             */
            $table->string('type')
                ->default(InterviewQuestionTypeEnum::CORE->value);

            /*
             * Skill/competency being assessed.
             *
             * Examples:
             * technical
             * communication
             * problem_solving
             * experience
             */
            $table->string('skill_area')->nullable();

            /*
             * Actual question presented to the candidate.
             */
            $table->text('question_text');

            /*
             * Who/what generated the question.
             *
             * Examples:
             * system
             * ai
             * interviewer
             */
            $table->string('source')
                ->default('ai');

            /*
             * When the question was actually asked.
             */
            $table->timestamp('asked_at')->nullable();

            /*
             * Provider/prompt/question-generation metadata.
             */
            $table->json('metadata')->nullable();
            $table->timestamps();

            /*
             * Sequence must be unique within an attempt.
             */
            $table->unique([
                'interview_attempt_id',
                'sequence',
            ]);

            $table->index([
                'interview_attempt_id',
                'type',
            ]);

            $table->index([
                'interview_attempt_id',
                'asked_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_questions');
    }
};
