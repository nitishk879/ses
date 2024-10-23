<?php

namespace Database\Seeders;

use App\Models\Industry;
use App\Models\Location;
use App\Models\SubCategory;
use App\Models\Talent;
use App\Models\User;
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
        $datafile = File::get(public_path('/dataset/talent.json'));
        $talents = json_decode($datafile);

        foreach ($talents as $talent) {
            $current = Talent::create([
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
                "characteristics" => $talent->characteristics,
                'qualifications' => $talent->qualifications,
                'experience' => $talent->experience ?? random_int(1,5),
                'min_monthly_price' => $talent->min_monthly_price,
                'max_monthly_price' => $talent->max_monthly_price ?? $talent->min_monthly_price+300000,
                'other_desire_conditions' => $talent->other_desire_conditions,
                'user_id' => $talent->user_id ?? random_int(1, User::count()),
                'company_id' => $talent->company_id,
            ]);
            $current->industries()->attach([random_int(1, Industry::count()), random_int(1, Industry::count())]);
            $current->locations()->attach([random_int(1, Location::count()), random_int(1, Location::count()), random_int(1, Location::count())]);
            $current->subcategories()->attach([random_int(1, SubCategory::count()), random_int(1, SubCategory::count()), random_int(1, SubCategory::count())]);
        }
    }
}
