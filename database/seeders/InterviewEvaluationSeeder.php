<?php

namespace Database\Seeders;

use App\Models\InterviewAttempt;
use App\Models\InterviewEvaluation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InterviewEvaluationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attempt = InterviewAttempt::query()
            ->where('status', 'completed')
            ->whereDoesntHave('evaluation')
            ->first();

        if (!$attempt) {
            return;
        }

        InterviewEvaluation::factory()->create([
            'interview_attempt_id' => $attempt->id,
        ]);
    }
}
