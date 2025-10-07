# DevAfora

A customizable link aggregator built with Laravel 11, Vue 3, and Inertia.js. Features a dark-themed interface, integrated blog system, and newsletter functionality powered by Postmark.

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat&logo=laravel&logoColor=white)
![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?style=flat&logo=vue.js&logoColor=white)
![TypeScript](https://img.shields.io/badge/TypeScript-5-3178C6?style=flat&logo=typescript&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-4-38B2AC?style=flat&logo=tailwind-css&logoColor=white)

## Features

- Link aggregator with sortable, toggleable links
- Blog system with HTML/Markdown support
- Newsletter subscription with Postmark integration
- Responsive dark-themed UI
- SPA architecture using Inertia.js
- Actions pattern for business logic isolation
- TypeScript for type safety
- Optimized queries with eager loading

## Tech Stack

**Backend:** Laravel 11, SQLite, Postmark
**Frontend:** Vue 3, TypeScript, Inertia.js, Tailwind CSS 4, Vite

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- SQLite3

## Installation

```bash
git clone https://github.com/sahdoio/devafora.git
cd devafora
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
```

Configure Postmark (optional) in `.env`:
```env
MAIL_MAILER=postmark
MAIL_FROM_NAME='Your Name'
MAIL_FROM_ADDRESS=your@email.com
MAIL_CC=copy@email.com
POSTMARK_TOKEN='your-token-here'
```

Start development servers:
```bash
php artisan serve
npm run dev
```

Access at `http://localhost:8000`

## Project Structure

```
app/
├── Actions/Newsletter/          # Business logic
├── Http/Controllers/Frontend/   # Frontend controllers
├── Http/Resources/              # API resources
├── Models/                      # Eloquent models
└── Mail/                        # Mailables

resources/
├── js/components/               # Vue components
├── js/layouts/                  # Application layouts
├── js/pages/                    # Inertia pages
└── views/emails/                # Email templates
```

## Architecture

Business logic is isolated in Action classes:

```php
class SubscribeToNewsletterAction
{
    public function execute(string $email, ?string $name = null): NewsletterSubscription
    {
        return DB::transaction(function () use ($email, $name) {
            $subscription = NewsletterSubscription::firstOrNew(['email' => $email]);

            if ($subscription->exists && $subscription->isSubscribed()) {
                return $subscription;
            }

            $subscription->name = $name ?? $subscription->name;
            $subscription->subscribe();

            $this->sendWelcomeEmail->execute($subscription);

            return $subscription;
        });
    }
}
```

Data presentation through Laravel Resources:

```php
class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'publishedAt' => $this->published_at?->format('Y-m-d'),
            'readTime' => $this->read_time ? "{$this->read_time} min" : null,
            'tags' => $this->tags,
        ];
    }
}
```

Models with domain logic:

```php
class Post extends Model
{
    public function generateSlug(): void
    {
        $this->slug = Str::slug($this->title);
    }

    public function publish(): void
    {
        $this->is_published = true;
        $this->published_at = now();
        $this->save();
    }
}
```

## Database Schema

**profiles**
- id, name, bio, photo, is_active, timestamps

**links**
- id, profile_id (FK), title, description, url, icon, order, is_active, timestamps

**posts**
- id, profile_id (FK), title, slug (unique), excerpt, content, author, image, read_time, tags (json), published_at, is_published, timestamps

**newsletter_subscriptions**
- id, email (unique), name, is_active, subscribed_at, unsubscribed_at, timestamps

## Customization

Edit seeders to customize profile, links, and posts:

```php
// ProfileSeeder.php
Profile::factory()->create([
    'name' => 'Your Name',
    'bio' => 'Your bio...',
    'photo' => '/images/your-photo.jpg',
]);

// LinkSeeder.php
$links = [
    [
        'title' => 'GitHub',
        'description' => 'My projects',
        'url' => 'https://github.com/username',
        'icon' => 'github',
        'order' => 0,
    ],
];
```

Create new posts via Tinker:

```bash
php artisan tinker
```

```php
$profile = Profile::first();
$profile->posts()->create([
    'title' => 'My Article',
    'slug' => 'my-article',
    'excerpt' => 'Brief description...',
    'content' => '<p>HTML content...</p>',
    'author' => 'Your Name',
    'read_time' => 5,
    'tags' => ['Tag1', 'Tag2'],
    'is_published' => true,
    'published_at' => now(),
]);
```

## Testing

```bash
php artisan test
```

## Production Build

```bash
npm run build
php artisan optimize
```

## Security

- CSRF protection on all forms
- Laravel validation on all inputs
- SQL injection protection via Eloquent
- XSS protection with trusted content only
- Rate limiting on public endpoints

## Deployment

1. Set up production environment variables
2. Run `php artisan migrate --force`
3. Run `php artisan db:seed --force`
4. Build assets with `npm run build`
5. Optimize with `php artisan optimize`
6. Configure queue worker for background jobs
7. Set up cron for scheduled tasks if needed

## License

MIT License. See [LICENSE](LICENSE) file for details.
