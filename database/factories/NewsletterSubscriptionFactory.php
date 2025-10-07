<?php

namespace Database\Factories;

use App\Models\NewsletterSubscription;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsletterSubscriptionFactory extends Factory
{
    protected $model = NewsletterSubscription::class;

    public function definition(): array
    {
        $firstName = fake()->firstNameMale();
        $lastName = fake()->lastName();

        return [
            'email' => fake()->unique()->safeEmail(),
            'name' => "{$firstName} {$lastName}",
            'is_active' => true,
            'subscribed_at' => now()->subDays(fake()->numberBetween(1, 90)),
            'unsubscribed_at' => null,
        ];
    }

    public function unsubscribed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'unsubscribed_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }
}
