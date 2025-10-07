<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = fake()->randomElement([
            'Como dominar Laravel em 2025',
            'Vue.js vs React: qual escolher?',
            'Arquitetura limpa com PHP',
            'Testes automatizados que funcionam',
            'Deploy perfeito com GitHub Actions',
            'APIs RESTful: guia completo',
            'TypeScript para iniciantes',
            'Docker na prática',
            'Microserviços com Laravel',
            'Performance em aplicações web',
        ]);

        return [
            'profile_id' => Profile::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->paragraph(2),
            'content' => '<p>' . implode('</p><p>', fake()->paragraphs(5)) . '</p>',
            'author' => 'DevAfora',
            'image' => null,
            'read_time' => fake()->numberBetween(5, 15),
            'tags' => fake()->randomElements([
                'Laravel', 'Vue.js', 'PHP', 'JavaScript', 'TypeScript',
                'Docker', 'DevOps', 'API', 'Testing', 'Performance',
                'Architecture', 'Clean Code', 'React', 'Node.js'
            ], fake()->numberBetween(2, 4)),
            'published_at' => now()->subDays(fake()->numberBetween(1, 30)),
            'is_published' => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'published_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }
}
