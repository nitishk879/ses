<?php

namespace Database\Seeders;

use App\Enums\InterviewQuestionTypeEnum;
use App\Models\InterviewAttempt;
use App\Models\InterviewQuestion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InterviewQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attempt = InterviewAttempt::query()->first();

        if (!$attempt) {
            $this->command->warn(
                'InterviewQuestionSeeder skipped: no interview attempt exists.'
            );

            return;
        }

        $questions = [
            [
                'sequence' => 1,
                'type' => InterviewQuestionTypeEnum::CORE,
                'skill_area' => 'experience',
                'question_text' => 'Could you briefly introduce yourself and describe your recent professional experience?',
                'source' => 'system',
            ],
            [
                'sequence' => 2,
                'type' => InterviewQuestionTypeEnum::JD_SPECIFIC,
                'skill_area' => 'technical',
                'question_text' => 'Can you describe a project where you used the main technologies required for this position?',
                'source' => 'ai',
            ],
            [
                'sequence' => 3,
                'type' => InterviewQuestionTypeEnum::CANDIDATE_SPECIFIC,
                'skill_area' => 'problem_solving',
                'question_text' => 'Looking at your previous experience, what was the most challenging problem you had to solve?',
                'source' => 'ai',
            ],
            [
                'sequence' => 4,
                'type' => InterviewQuestionTypeEnum::FOLLOW_UP,
                'skill_area' => 'communication',
                'question_text' => 'What approach did you take, and what was the outcome?',
                'source' => 'ai',
            ],
        ];

        foreach ($questions as $question) {
            InterviewQuestion::create([
                'interview_attempt_id' => $attempt->id,
                ...$question,
            ]);
        }
    }
}
