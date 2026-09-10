<?php

namespace Database\Seeders;

use App\Models\InterviewAnswer;
use App\Models\InterviewQuestion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InterviewAnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $question = InterviewQuestion::query()->whereDoesntHave('answer')->first();

        if (!$question) {
            $this->command->warn(
                'InterviewAnswerSeeder skipped: no unanswered question exists.'
            );

            return;
        }

        $startedAt = now()->subSeconds(45);

        InterviewAnswer::create([
            'interview_question_id' => $question->id,
            'answer_text' => null,
            'transcript' => 'I have several years of experience working on web applications and have primarily worked with Laravel and related technologies.',
            'audio_path' => null,
            'audio_duration_seconds' => 45,
            'started_at' => $startedAt,
            'ended_at' => $startedAt->copy()->addSeconds(45),
            'metadata' => [
                'source' => 'seed',
            ],
        ]);
    }
}
