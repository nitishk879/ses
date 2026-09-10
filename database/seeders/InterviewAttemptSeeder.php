<?php

namespace Database\Seeders;

use App\Models\Interview;
use App\Models\InterviewAttempt;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InterviewAttemptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $interview = Interview::query()->first();

        if (!$interview) {
            $this->command->warn(
                'InterviewAttemptSeeder skipped: no interview exists.'
            );

            return;
        }

        InterviewAttempt::create([
            'interview_id' => $interview->id,
            'attempt_number' => rand(1, 3),
            'status' => 'completed',
            'channel' => $interview->channel,
            'started_at' => now()->subMinutes(5),
            'ended_at' => now(),
            'duration_seconds' => 300,
        ]);
    }
}
