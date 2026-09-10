<?php

namespace App\Jobs;

use App\Models\InterviewAttempt;
use App\Services\InterviewEvaluationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EvaluateInterviewAttemptJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public int $interviewAttemptId
    ) {
    }

    public function handle(
        InterviewEvaluationService $evaluationService
    ): void {
        $attempt = InterviewAttempt::findOrFail(
            $this->interviewAttemptId
        );

        $evaluationService->evaluate($attempt);
    }
}
