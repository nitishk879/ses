<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Testimonial::truncate();
        $datafile = File::get(public_path('/dataset/testimonials.json'));
        $testimonials = json_decode($datafile);

        foreach ($testimonials as $testimonial) {
            Testimonial::create([
                'content' => $testimonial->content,
                'rating' => $testimonial->rating ?? random_int(1, 5),
                'user_id' => $testimonial->user_id,
            ]);
        }
    }
}
