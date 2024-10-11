<?php

namespace Database\Seeders;

use App\Models\SubCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SubCategory::truncate();
        $datafile = File::get(public_path('/dataset/sub_categories.json'));
        $subCategories = json_decode($datafile);


        foreach ($subCategories as $sub_category) {
            SubCategory::create([
                'title' => $sub_category->title,
                'slug' => Str::replace('-', '_', $sub_category->slug),
                'display_order' => $sub_category->display_order,
                'category_id' => $sub_category->category_id,
            ]);
        }
    }
}
