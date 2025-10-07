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

        $content = $this->generateRichContent();

        // Generate banner image URL using Unsplash
        $imageTopics = ['code', 'programming', 'technology', 'computer', 'software', 'developer', 'laptop'];
        $randomTopic = fake()->randomElement($imageTopics);
        $imageUrl = "https://source.unsplash.com/1200x630/?{$randomTopic}," . fake()->numberBetween(1, 100);

        return [
            'profile_id' => Profile::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->paragraph(2),
            'content' => $content,
            'author' => 'DevAfora',
            'image' => $imageUrl,
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

    private function generateRichContent(): string
    {
        $codeExamples = [
            '<pre><code class="language-php">&lt;?php

namespace App\\Actions;

class ProcessPaymentAction
{
    public function execute(Order $order): Payment
    {
        // Validate order
        if (!$order->isValid()) {
            throw new InvalidOrderException();
        }

        // Process payment
        $payment = Payment::create([
            \'order_id\' => $order->id,
            \'amount\' => $order->total,
            \'status\' => \'pending\'
        ]);

        return $payment;
    }
}
</code></pre>',
            '<pre><code class="language-javascript">// Vue.js Composition API
import { ref, computed, onMounted } from \'vue\'

export default {
  setup() {
    const count = ref(0)
    const doubled = computed(() => count.value * 2)

    function increment() {
      count.value++
    }

    onMounted(() => {
      console.log(\'Component mounted!\')
    })

    return { count, doubled, increment }
  }
}
</code></pre>',
            '<pre><code class="language-typescript">interface User {
  id: number
  name: string
  email: string
  role: \'admin\' | \'user\'
}

async function fetchUser(id: number): Promise&lt;User&gt; {
  const response = await fetch(`/api/users/${id}`)

  if (!response.ok) {
    throw new Error(\'Failed to fetch user\')
  }

  return response.json()
}
</code></pre>',
            '<pre><code class="language-bash"># Deploy script
#!/bin/bash

echo "Starting deployment..."

# Pull latest changes
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# Run migrations
php artisan migrate --force

# Clear cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache

echo "Deployment complete!"
</code></pre>',
        ];

        $paragraphs = fake()->paragraphs(3);
        $content = '';

        $content .= '<h2>Introdução</h2>';
        $content .= '<p>' . $paragraphs[0] . '</p>';

        $content .= '<h2>Implementação</h2>';
        $content .= '<p>' . $paragraphs[1] . '</p>';
        $content .= fake()->randomElement($codeExamples);

        $content .= '<h3>Detalhes importantes</h3>';
        $content .= '<p>' . $paragraphs[2] . '</p>';
        $content .= '<p>Aqui está um exemplo de código inline: <code>const result = await fetchData()</code> que demonstra o uso.</p>';

        $content .= '<h2>Conclusão</h2>';
        $content .= '<p>' . fake()->paragraph() . '</p>';

        return $content;
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
