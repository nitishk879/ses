<?php

namespace App\Http\Controllers;

use App\Enums\InterviewQuestionTypeEnum;
use App\Models\InterviewAttempt;
use App\Models\InterviewQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InterviewQuestionController extends Controller
{
    public function index(
        InterviewAttempt $attempt
    ): JsonResponse {
        $questions = $attempt->questions()->get();

        return response()->json($questions);
    }

    public function store(
        Request $request,
        InterviewAttempt $attempt
    ): JsonResponse {
        $validated = $request->validate([
            'type' => [
                'required',
                Rule::enum(InterviewQuestionTypeEnum::class),
            ],

            'skill_area' => [
                'nullable',
                'string',
                'max:100',
            ],

            'question_text' => [
                'required',
                'string',
            ],

            'source' => [
                'nullable',
                'string',
                'max:50',
            ],

            'metadata' => [
                'nullable',
                'array',
            ],
        ]);

        $nextSequence = (
                $attempt->questions()->max('sequence') ?? 0
            ) + 1;

        $question = $attempt->questions()->create([
            ...$validated,
            'sequence' => $nextSequence,
            'source' => $validated['source'] ?? 'ai',
        ]);

        return response()->json($question, 201);
    }

    public function show(
        InterviewAttempt $attempt,
        InterviewQuestion $question
    ): JsonResponse {
        $this->ensureQuestionBelongsToAttempt(
            $attempt,
            $question
        );

        return response()->json(
            $question->load('answer')
        );
    }

    public function destroy(
        InterviewAttempt $attempt,
        InterviewQuestion $question
    ): JsonResponse {
        $this->ensureQuestionBelongsToAttempt(
            $attempt,
            $question
        );

        /*
         * Do not allow deletion after the question
         * has actually been asked.
         */
        if ($question->asked_at !== null) {
            return response()->json([
                'message' => __('interview.question_already_asked'),
            ], 409);
        }

        $question->delete();

        return response()->json([
            'message' => __('interview.question_deleted'),
        ]);
    }

    private function ensureQuestionBelongsToAttempt(
        InterviewAttempt $attempt,
        InterviewQuestion $question
    ): void {
        abort_unless(
            $question->interview_attempt_id === $attempt->id,
            404
        );
    }
}
