<?php

namespace Database\Factories;

use App\Models\Link;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

class LinkFactory extends Factory
{
    protected $model = Link::class;

    public function definition(): array
    {
        return [
            'profile_id' => Profile::factory(),
            'title' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'url' => fake()->url(),
            'icon' => fake()->randomElement(['youtube', 'tiktok', 'twitter', 'instagram', 'github']),
            'order' => fake()->numberBetween(0, 10),
            'is_active' => true,
        ];
    }

    public function youtube(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'YouTube',
            'description' => 'Tutoriais e conteúdo sobre programação',
            'url' => 'https://youtube.com/@devafora',
            'icon' => 'youtube',
            'order' => 0,
        ]);
    }

    public function tiktok(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'TikTok',
            'description' => 'Dicas rápidas de programação',
            'url' => 'https://tiktok.com/@devafora',
            'icon' => 'tiktok',
            'order' => 1,
        ]);
    }

    public function twitter(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'X (Twitter)',
            'description' => 'Atualizações e reflexões sobre tech',
            'url' => 'https://twitter.com/devafora',
            'icon' => 'twitter',
            'order' => 2,
        ]);
    }

    public function instagram(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'Instagram',
            'description' => 'Bastidores e conteúdo visual',
            'url' => 'https://instagram.com/devafora',
            'icon' => 'instagram',
            'order' => 3,
        ]);
    }

    public function github(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'GitHub',
            'description' => 'Meus projetos open source',
            'url' => 'https://github.com/devafora',
            'icon' => 'github',
            'order' => 4,
        ]);
    }
}
