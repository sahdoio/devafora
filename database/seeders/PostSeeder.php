<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Profile;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profile = Profile::where('is_active', true)->first();

        if ($profile) {
            Post::factory()->count(3)->published()->create([
                'profile_id' => $profile->id,
            ]);
        }
    }
}
