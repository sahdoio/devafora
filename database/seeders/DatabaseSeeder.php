<?php

namespace Database\Seeders;

use App\Models\Link;
use App\Models\NewsletterSubscription;
use App\Models\Post;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@devafora.com',
        ]);

        // Create active profile
        $profile = Profile::factory()->create([
            'name' => 'DevAfora',
            'bio' => 'Engenheiro de Software Sênior com mais de uma década de experiência em desenvolvimento de software. Minha jornada começou em 2010 e, desde então, aprimorei minhas habilidades em desenvolvimento backend, tecnologias frontend, automação de servidores e design de arquitetura.',
            'photo' => 'https://ui-avatars.com/api/?name=DevAfora&size=256&background=3b82f6&color=ffffff&bold=true',
            'is_active' => true,
        ]);

        // Create social media links (exactly as specified)
        Link::factory()->youtube()->create(['profile_id' => $profile->id]);
        Link::factory()->tiktok()->create(['profile_id' => $profile->id]);
        Link::factory()->twitter()->create(['profile_id' => $profile->id]);
        Link::factory()->instagram()->create(['profile_id' => $profile->id]);
        Link::factory()->github()->create(['profile_id' => $profile->id]);

        // Create 3 published posts
        Post::factory()->count(3)->published()->create([
            'profile_id' => $profile->id,
        ]);

        // Create 5 newsletter subscriptions
        NewsletterSubscription::factory()->count(5)->create();
    }
}
