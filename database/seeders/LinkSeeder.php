<?php

namespace Database\Seeders;

use App\Models\Link;
use App\Models\Profile;
use Illuminate\Database\Seeder;

class LinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profile = Profile::where('is_active', true)->first();

        if ($profile) {
            Link::factory()->youtube()->create(['profile_id' => $profile->id]);
            Link::factory()->tiktok()->create(['profile_id' => $profile->id]);
            Link::factory()->twitter()->create(['profile_id' => $profile->id]);
            Link::factory()->instagram()->create(['profile_id' => $profile->id]);
            Link::factory()->github()->create(['profile_id' => $profile->id]);
        }
    }
}
