<?php

namespace App\Http\Controllers;

use App\Enums\InterviewStatus;
use App\Models\Interview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $interviews = Interview::query()
            ->with([
                'project',
                'talent',
            ])
            ->when(
                $request->filled('project_id'),
                fn ($query) => $query->where(
                    'project_id',
                    $request->integer('project_id')
                )
            )
            ->when(
                $request->filled('talent_id'),
                fn ($query) => $query->where(
                    'talent_id',
                    $request->integer('talent_id')
                )
            )
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where(
                    'status',
                    $request->input('status')
                )
            )
            ->latest()
            ->paginate(
                $request->integer('per_page', 20)
            );

        return response()->json($interviews);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => [
                'required',
                'integer',
                'exists:projects,id',
            ],

            'talent_id' => [
                'required',
                'integer',
                'exists:talents,id',
            ],

            'channel' => [
                'nullable',
                'string',
                'max:50',
            ],

            'timezone' => [
                'nullable',
                'timezone',
            ],
        ]);

        $interview = Interview::create([
            ...$validated,
            'status' => InterviewStatus::PENDING,
        ]);

        return response()->json(
            $interview->load([
                'project',
                'talent',
            ]),
            201
        );
    }

    public function show(Interview $interview): JsonResponse
    {
        return response()->json(
            $interview->load([
                'project',
                'talent',
            ])
        );
    }

    public function update(
        Request $request,
        Interview $interview
    ): JsonResponse {
        $validated = $request->validate([
            'channel' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
            ],

            'timezone' => [
                'sometimes',
                'nullable',
                'timezone',
            ],

            'scheduled_at' => [
                'sometimes',
                'nullable',
                'date',
            ],
        ]);

        $interview->update($validated);

        return response()->json(
            $interview->fresh()
        );
    }

    public function destroy(Interview $interview): JsonResponse
    {
        $interview->delete();

        return response()->json([
            'message' => __('interview.deleted'),
        ]);
    }
}
