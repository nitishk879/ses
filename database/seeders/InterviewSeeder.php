<?php

namespace Database\Seeders;

use App\Enums\InterviewStatus;
use App\Models\Interview;
use App\Models\Project;
use App\Models\Talent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InterviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $project = Project::query()->first();
        $talent = Talent::query()->first();

        if (!$project || !$talent) {
            $this->command->warn(
                'InterviewSeeder skipped: project or talent records do not exist.'
            );

            return;
        }

        Interview::create([
            'project_id' => $project->id,
            'talent_id' => $talent->id,
            'status' => InterviewStatus::PENDING,
            'channel' => 'google_meet',
            'timezone' => 'Asia/Tokyo',
        ]);
    }
}
