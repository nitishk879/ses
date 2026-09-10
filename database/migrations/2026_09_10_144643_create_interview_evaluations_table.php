<?php

use App\Enums\InterviewEvaluationStatusEnum;
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
        Schema::create('interview_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_attempt_id')
                ->constrained('interview_attempts')
                ->cascadeOnDelete();

            $table->string('status')
                ->default(InterviewEvaluationStatusEnum::PENDING->value);

            /*
             * Evaluation scores are stored from 0 to 100.
             *
             * Technical Fit:     40%
             * JD Fit:            35%
             * Communication:     25%
             */
            $table->decimal('technical_fit', 5, 2)->nullable();
            $table->decimal('jd_fit', 5, 2)->nullable();
            $table->decimal('communication', 5, 2)->nullable();

            /*
             * Final weighted score.
             *
             * Calculated by the backend, not trusted from AI/user input.
             */
            $table->decimal('overall_score', 5, 2)->nullable();

            $table->text('summary')->nullable();

            /*
             * Structured AI output.
             */
            $table->json('strengths')->nullable();
            $table->json('gaps')->nullable();
            $table->json('evidence')->nullable();

            /*
             * Example:
             * recommended
             * maybe_recommended
             * not_recommended
             * insufficient_data
             */
            $table->string('recommendation')->nullable();

            /*
             * AI execution information.
             */
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->string('prompt_version')->nullable();

            $table->timestamp('evaluated_at')->nullable();

            $table->text('failure_reason')->nullable();

            /*
             * Provider-specific information without
             * coupling the database to a particular AI provider.
             */
            $table->json('metadata')->nullable();

            $table->timestamps();

            /*
             * One evaluation per interview attempt for now.
             *
             * If later we need evaluation history/re-evaluation,
             * this constraint can be changed and an evaluation_runs
             * concept can be introduced.
             */
            $table->unique('interview_attempt_id');

            $table->index(['interview_attempt_id', 'status']);
            $table->index(['status', 'evaluated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_evaluations');
    }
};
