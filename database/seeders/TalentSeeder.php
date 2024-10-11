<?php

namespace Database\Seeders;

use App\Models\Talent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class TalentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Talent::truncate();
        $datafile = File::get(public_path('/json/talents.json'));
        $talents = json_decode($datafile);

        foreach ($talents as $talent) {
            Talent::create([
                'resume' => $talent->resume,
                'availability' => $talent->availability,
                'joining_date' => $talent->joining_date,
                'affiliation' => $talent->affiliation,
                'quasi_delegation_possible' => $talent->quasi_delegation_possible,
                'available_for_contract' => $talent->available_for_contract,
                'available_for_dispatch' => $talent->available_for_dispatch,
                'request_for_contract' => $talent->request_for_contract,
                'remote_work_preferred' => $talent->remote_work_preferred,
                'work_location_prefer' => $talent->work_location_prefer,
                'experience_pr' => $talent->experience_pr,
                'qualifications' => $talent->qualifications,
                'min_monthly_price' => $talent->min_monthly_price,
                'max_monthly_price' => $talent->max_monthly_price,
                'other_desire_conditions' => $talent->other_desire_conditions,
                'user_id' => $talent->user_id,
                'company_id' => $talent->company_id,
            ]);
        }
    }
}
