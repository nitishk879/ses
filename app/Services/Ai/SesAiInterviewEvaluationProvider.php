<?php

namespace App\Services\Ai;

use App\Contracts\InterviewEvaluationProvider;
use App\Models\AiJdParse;
use App\Models\InterviewAttempt;
use App\Services\InterviewAiService;
use RuntimeException;

/**
 * Evaluates an interview by asking ses-ai-service to read the transcript
 * against the job description.
 *
 * Replaces {@see MockInterviewEvaluationProvider}, which returned a hardcoded
 * 85/90/82 for every candidate — fine as a placeholder while the schema was
 * being built, and actively dangerous once it is wired to a screen anybody
 * makes decisions from, because a constant is indistinguishable from a real
 * score in the UI.
 *
 * The mock is kept and still bound in `testing`: a test that asserts the
 * evaluation pipeline should not depend on a language model's judgement.
 */
class SesAiInterviewEvaluationProvider implements InterviewEvaluationProvider
{
    public function __construct(
        private readonly InterviewAiService $ai,
    ) {
    }

    /**
     * @return array<string, mixed> in exactly the shape
     *         {@see \App\Services\InterviewEvaluationService} validates
     */
    public function evaluate(InterviewAttempt $attempt): array
    {
        $attempt->loadMissing([
            'interview.project',
            'interview.talent',
            'questions',
        ]);

        $interview = $attempt->interview;
        $project = $interview?->project;
        $talent = $interview?->talent;

        if (! $project || ! $talent) {
            throw new RuntimeException('Interview attempt is not linked to a project and talent.');
        }

        $jd = AiJdParse::firstWhere('project_id', $project->id);

        if (! $jd) {
            // Without the JD there is nothing to evaluate *against*, and
            // scoring "communication" alone is exactly the failure mode the
            // task sheet calls out.
            throw new RuntimeException(
                "Project {$project->id} has no stored JD parse; parse it before evaluating."
            );
        }

        $result = $this->ai->evaluate(
            $project,
            $talent,
            $jd->payload ?? [],
            $this->questionPayload($attempt),
            $attempt->transcript ?? [],
            $attempt->duration_seconds,
        );

        return [
            'technical_fit' => $result['technical_fit'],
            'jd_fit' => $result['jd_fit'],
            'communication' => $result['communication'],

            'summary' => $result['summary'] ?? null,
            'strengths' => $result['strengths'] ?? [],
            'gaps' => $result['gaps'] ?? [],
            'evidence' => $result['evidence'] ?? [],

            'recommendation' => $result['recommendation'] ?? null,

            'provider' => $result['provider'] ?? 'ses-ai-service',
            'model' => $result['model'] ?? null,
            'prompt_version' => $result['prompt_version'] ?? null,

            // Carried through so the reader can see what the scores rest on.
            // A 78 from a fully-answered interview and a 78 from one answered
            // question are not the same number, and only this says which.
            'metadata' => [
                'questions_asked' => $result['questions_asked'] ?? null,
                'questions_answered' => $result['questions_answered'] ?? null,
                'coverage' => $result['coverage'] ?? null,
                'warnings' => $result['warnings'] ?? [],
            ],
        ];
    }

    /**
     * The questions as the AI service expects them.
     *
     * `intent` comes back out of the metadata it was stored with, so an answer
     * can be mapped to the requirement the question was there to establish.
     *
     * @return array<int, array<string, mixed>>
     */
    private function questionPayload(InterviewAttempt $attempt): array
    {
        return $attempt->questions
            ->sortBy('sequence')
            ->map(fn ($question) => [
                'order' => (int) $question->sequence,
                'text' => (string) $question->question_text,
                'intent' => $question->metadata['intent'] ?? 'verify_claimed_skill',
                'subject' => $question->skill_area,
                'expected_seconds' => (int) ($question->metadata['expected_seconds'] ?? 48),
            ])
            ->values()
            ->all();
    }
}
