<?php

namespace App\Http\Controllers;

use App\Models\Interview;
use App\Models\InterviewAttempt;
use App\Services\InterviewAttemptLifeCycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterviewAttemptController extends Controller
{
    public function index(Interview $interview): JsonResponse
    {
        $attempts = $interview->attempts()
            ->latest('attempt_number')
            ->paginate(
                request()->integer('per_page', 20)
            );

        return response()->json($attempts);
    }

    public function store(
        Request $request,
        Interview $interview
    ): JsonResponse {
        $validated = $request->validate([
            'channel' => [
                'nullable',
                'string',
                'max:50',
            ],
        ]);

        $lastAttemptNumber = $interview->attempts()
            ->max('attempt_number') ?? 0;

        $attempt = $interview->attempts()->create([
            'attempt_number' => $lastAttemptNumber + 1,
            'status' => 'pending',
            'channel' => $validated['channel'] ?? $interview->channel,
        ]);

        return response()->json(
            $attempt->load('interview'),
            201
        );
    }

    public function show(
        Interview $interview,
        InterviewAttempt $attempt
    ): JsonResponse {
        $this->ensureAttemptBelongsToInterview(
            $interview,
            $attempt
        );

        return response()->json(
            $attempt->load('interview')
        );
    }

    public function destroy(
        Interview $interview,
        InterviewAttempt $attempt
    ): JsonResponse {
        $this->ensureAttemptBelongsToInterview(
            $interview,
            $attempt
        );

        $attempt->delete();

        return response()->json([
            'message' => __('interview.attempt_deleted'),
        ]);
    }

    private function ensureAttemptBelongsToInterview(
        Interview $interview,
        InterviewAttempt $attempt
    ): void {
        abort_unless(
            $attempt->interview_id === $interview->id,
            404
        );
    }

    public function start(Interview $interview, InterviewAttempt $attempt, InterviewAttemptLifecycleService $lifecycle): JsonResponse
    {
        $this->ensureAttemptBelongsToInterview($interview, $attempt);
        $attempt = $lifecycle->start($attempt);
        return response()->json($attempt);
    }
    public function begin(Interview $interview, InterviewAttempt $attempt, InterviewAttemptLifecycleService $lifecycle): JsonResponse
    {
        $this->ensureAttemptBelongsToInterview($interview, $attempt);
        $attempt = $lifecycle->beginInterview($attempt);
        return response()->json($attempt);
    }
    public function complete(Interview $interview, InterviewAttempt $attempt, InterviewAttemptLifecycleService $lifecycle): JsonResponse
    {
        $this->ensureAttemptBelongsToInterview($interview, $attempt);
        $attempt = $lifecycle->complete($attempt);
        return response()->json($attempt);
    }
    public function fail(Request $request, Interview $interview, InterviewAttempt $attempt, InterviewAttemptLifecycleService $lifecycle): JsonResponse
    {
        $this->ensureAttemptBelongsToInterview($interview, $attempt);
        $validated = $request->validate([
            'reason' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);
        $attempt = $lifecycle->fail($attempt, $validated['reason']);
        return response()->json($attempt);
    }
    public function noAnswer(Request $request, Interview $interview, InterviewAttempt $attempt, InterviewAttemptLifecycleService $lifecycle): JsonResponse
    {
        $this->ensureAttemptBelongsToInterview($interview, $attempt);
        $validated = $request->validate([
            'reason' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);
        $attempt = $lifecycle->noAnswer($attempt, $validated['reason']);
        return response()->json($attempt);
    }
    public function cancel(Request $request, Interview $interview, InterviewAttempt $attempt, InterviewAttemptLifecycleService $lifecycle): JsonResponse
    {
        $this->ensureAttemptBelongsToInterview($interview, $attempt);
        $validated = $request->validate([
            'reason' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);
        $attempt = $lifecycle->cancel($attempt, $validated['reason']);
        return response()->json($attempt);
    }
}
