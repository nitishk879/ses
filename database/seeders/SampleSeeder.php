<?php

namespace Database\Seeders;

use App\Models\Sample;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class SampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Sample::truncate();
        $datafile = File::get(public_path('/dataset/sample-data.json'));
        $samples = json_decode($datafile);

        foreach ($samples as $sample) {
            Sample::create([
                "data_category" => $sample->data_category,
                "data_medium_section" => $sample->data_medium_section,
                "data_sub_section" => $sample->data_sub_section,
                "remarks" => $sample->remarks,
                "text_data" => $sample->text_data,
                "numerical_data" => $sample->numerical_data,
                "date_data" => $sample->date_data,
            ]);
        }
    }
}
