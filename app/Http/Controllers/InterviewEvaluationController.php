<?php

namespace App\Http\Controllers;

use App\Enums\InterviewEvaluationStatusEnum;
use App\Jobs\EvaluateInterviewAttemptJob;
use App\Models\InterviewAttempt;
use App\Models\InterviewEvaluation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterviewEvaluationController extends Controller
{
    public function show(InterviewAttempt $interviewAttempt): JsonResponse
    {
        $evaluation = $interviewAttempt->evaluation;

        if (!$evaluation) {
            return response()->json([
                'message' => 'Interview evaluation not found.',
            ], 404);
        }

        return response()->json([
            'data' => $evaluation,
        ]);
    }

    public function store(Request $request, InterviewAttempt $interviewAttempt): JsonResponse
    {
        /*
         * An evaluation should only be created for a completed attempt.
         */
        if ($interviewAttempt->status->value !== 'completed') {
            return response()->json([
                'message' => 'Only completed interview attempts can be evaluated.',
            ], 422);
        }

        /*
         * Prevent duplicate evaluation records.
         */

//        $evaluation = $interviewAttempt->evaluation;
//        if ($evaluation) {
//            if ($evaluation->status === InterviewEvaluationStatusEnum::COMPLETED) {
//                return response()->json([
//                    'message' => 'This interview attempt has already been evaluated.',
//                ], 409);
//            }
//
//            if ($evaluation->status === InterviewEvaluationStatusEnum::EVALUATING) {
//                return response()->json([
//                    'message' => 'Evaluation is already in progress.',
//                    'data' => $evaluation,
//                ], 202);
//            }
//
//            if ($evaluation->status === InterviewEvaluationStatusEnum::FAILED) {
//                $evaluation->update([
//                    'status' => InterviewEvaluationStatusEnum::PENDING,
//                    'failure_reason' => null,
//                ]);
//            }
//        } else {
//            $evaluation = InterviewEvaluation::create([
//                'interview_attempt_id' => $interviewAttempt->id,
//                'status' => InterviewEvaluationStatusEnum::PENDING,
//            ]);
//        }

        if ($interviewAttempt->evaluation()->exists()) {
            return response()->json([
                'message' => 'This interview attempt has already been evaluated.',
            ], 409);
        }

        $evaluation = InterviewEvaluation::create([
            'interview_attempt_id' => $interviewAttempt->id,
            'status' => InterviewEvaluationStatusEnum::PENDING,
        ]);

        /**
         * dispatch the attempt interview
         */
        EvaluateInterviewAttemptJob::dispatch($interviewAttempt->id);

        return response()->json([
            'message' => 'Interview evaluation queued.',
            'data' => $evaluation,
        ], 201);
    }
}
