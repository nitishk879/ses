<?php

namespace App\Http\Controllers;

use App\Models\InterviewQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterviewAnswerController extends Controller
{
    public function show(
        InterviewQuestion $question
    ): JsonResponse {
        return response()->json(
            $question->answer
        );
    }

    public function store(
        Request $request,
        InterviewQuestion $question
    ): JsonResponse {
        if ($question->answer()->exists()) {
            return response()->json([
                'message' => __('interview.answer_already_exists'),
            ], 409);
        }

        $validated = $request->validate([
            'answer_text' => [
                'nullable',
                'string',
            ],

            'transcript' => [
                'nullable',
                'string',
            ],

            'audio_path' => [
                'nullable',
                'string',
                'max:2048',
            ],

            'audio_duration_seconds' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'started_at' => [
                'nullable',
                'date',
            ],

            'ended_at' => [
                'nullable',
                'date',
                'after_or_equal:started_at',
            ],

            'metadata' => [
                'nullable',
                'array',
            ],
        ]);

        /*
         * At least one actual answer representation
         * must be provided.
         */
        if (
            empty($validated['answer_text'] ?? null) &&
            empty($validated['transcript'] ?? null) &&
            empty($validated['audio_path'] ?? null)
        ) {
            return response()->json([
                'message' => __('interview.answer_required'),
            ], 422);
        }

        $answer = $question->answer()->create($validated);

        return response()->json(
            $answer->load('interviewQuestion'),
            201
        );
    }

    public function update(
        Request $request,
        InterviewQuestion $question
    ): JsonResponse {
        $answer = $question->answer;

        if (!$answer) {
            return response()->json([
                'message' => __('interview.answer_not_found'),
            ], 404);
        }

        $validated = $request->validate([
            'answer_text' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'transcript' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'audio_path' => [
                'sometimes',
                'nullable',
                'string',
                'max:2048',
            ],

            'audio_duration_seconds' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
            ],

            'metadata' => [
                'sometimes',
                'nullable',
                'array',
            ],
        ]);

        $answer->update($validated);

        return response()->json(
            $answer->fresh()
        );
    }
}
