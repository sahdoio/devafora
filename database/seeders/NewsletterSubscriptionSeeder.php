<?php

namespace Database\Seeders;

use App\Models\NewsletterSubscription;
use Illuminate\Database\Seeder;

class NewsletterSubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        NewsletterSubscription::factory()->count(5)->create();
    }
}
