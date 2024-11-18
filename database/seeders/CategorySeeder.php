<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::truncate();
        $datafile = File::get(public_path('/dataset/categories.json'));
        $categories = json_decode($datafile);


        foreach ($categories as $category) {
            Category::create([
                'title' => $category->title,
                'slug' => $category->slug ?? Str::slug($category->title, '_'),
                'display_order' => $category->display_order,
            ]);
        }
    }
}
