# DevAfora

Um agregador de links personalizável construído com Laravel 11, Vue 3 e Inertia.js. Apresenta uma interface dark moderna, sistema de blog integrado e funcionalidade de newsletter.

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat&logo=laravel&logoColor=white)
![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?style=flat&logo=vue.js&logoColor=white)
![TypeScript](https://img.shields.io/badge/TypeScript-5-3178C6?style=flat&logo=typescript&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-4-38B2AC?style=flat&logo=tailwind-css&logoColor=white)

## Características

- 🔗 Agregador de links com ordenação e ativação/desativação
- 📝 Sistema de blog com suporte HTML/Markdown
- 📧 Sistema de newsletter com validação
- 🎨 Interface dark moderna e responsiva
- ⚡ SPA utilizando Inertia.js
- 🏗️ Arquitetura Action-based para lógica de negócio
- 🔒 TypeScript para type safety
- 🚀 Queries otimizadas com eager loading
- 📦 Componentes Vue reutilizáveis

## Stack Tecnológica

**Backend:** Laravel 11, SQLite
**Frontend:** Vue 3, TypeScript, Inertia.js, Tailwind CSS 4, Vite

## Requisitos

- PHP 8.2+
- Composer
- Node.js 18+
- SQLite3

## Instalação

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

Inicie os servidores de desenvolvimento:
```bash
php artisan serve
npm run dev
```

Acesse em `http://localhost:8000`

### Configuração Opcional de E-mail

Para ativar o envio de e-mails da newsletter, configure no `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=seu_username
MAIL_PASSWORD=sua_senha
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@devafora.com
MAIL_FROM_NAME="DevAfora"
```

## Estrutura do Projeto

```
app/
├── Actions/                     # Lógica de negócio organizada por domínio
│   ├── Profile/                 # Actions relacionadas a perfis
│   ├── Links/                   # Actions relacionadas a links
│   ├── Posts/                   # Actions relacionadas a posts
│   └── Newsletter/              # Actions relacionadas à newsletter
├── Http/
│   ├── Controllers/
│   │   ├── Frontend/            # Controllers que retornam views Inertia
│   │   │   ├── HomeController.php
│   │   │   └── PostController.php
│   │   └── Api/                 # Controllers de API que retornam JSON
│   │       └── NewsletterController.php
│   ├── Resources/               # Laravel Resources para formatação de dados
│   │   ├── ProfileResource.php
│   │   ├── LinkResource.php
│   │   ├── PostResource.php
│   │   └── PostListResource.php
│   └── Requests/                # Form Requests para validação
│       └── NewsletterSubscribeRequest.php
└── Models/                      # Eloquent Models com lógica de domínio
    ├── Profile.php
    ├── Link.php
    ├── Post.php
    └── NewsletterSubscription.php

database/
├── migrations/                  # Database migrations
├── factories/                   # Model factories com dados realistas
└── seeders/                     # Database seeders

resources/js/
├── components/                  # Componentes Vue reutilizáveis
│   ├── LinkCard.vue
│   ├── PostCard.vue
│   └── NewsletterForm.vue
└── pages/                       # Páginas Inertia
    ├── Home.vue
    └── Post/
        └── Show.vue

routes/
├── web.php                      # Rotas web (Inertia)
└── api.php                      # Rotas de API
```

## Arquitetura

### Padrão Action-Based

Toda a lógica de negócio está isolada em classes Action:

```php
// app/Actions/Newsletter/SubscribeToNewsletterAction.php
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

            return $subscription;
        });
    }
}
```

### Controllers Limpos

Controllers apenas chamam Actions e retornam views/JSON:

```php
// app/Http/Controllers/Frontend/HomeController.php
class HomeController extends Controller
{
    public function __invoke(
        GetActiveProfileAction $getProfile,
        GetActiveLinksAction $getLinks,
        GetLatestPostsAction $getLatestPosts
    ): Response {
        $profile = $getProfile->execute();
        $links = $getLinks->execute($profile?->id);
        $posts = $getLatestPosts->execute(limit: 3, profileId: $profile?->id);

        return Inertia::render('Home', [
            'profile' => ProfileResource::make($profile),
            'links' => LinkResource::collection($links),
            'posts' => PostListResource::collection($posts),
        ]);
    }
}
```

### Resources para Formatação de Dados

TODOS os dados são formatados via Laravel Resources antes de ir ao frontend:

```php
// app/Http/Resources/PostResource.php
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
            'readTime' => $this->read_time ? "{$this->read_time} min" : null,
            'tags' => $this->tags ?? [],
            'publishedAt' => $this->published_at?->format('d/m/Y'),
        ];
    }
}
```

### Models com Lógica de Domínio (DDD)

Models podem conter regras de negócio quando faz sentido:

```php
// app/Models/Post.php
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

    public function addTag(string $tag): void
    {
        $tags = $this->tags ?? [];
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->tags = $tags;
            $this->save();
        }
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

## Personalização

### Editando o Perfil Principal

Edite o seeder para personalizar seu perfil e links:

```php
// database/seeders/DatabaseSeeder.php
$profile = Profile::factory()->create([
    'name' => 'Seu Nome',
    'bio' => 'Sua bio aqui...',
    'photo' => null, // ou caminho para sua foto
    'is_active' => true,
]);
```

### Customizando Links

Edite os links sociais no seeder:

```php
Link::factory()->create([
    'profile_id' => $profile->id,
    'title' => 'GitHub',
    'description' => 'Meus projetos open source',
    'url' => 'https://github.com/seu-usuario',
    'icon' => 'github',
    'order' => 0,
    'is_active' => true,
]);
```

### Criando Novos Posts

Via Tinker:

```bash
php artisan tinker
```

```php
$profile = Profile::first();
$post = $profile->posts()->create([
    'title' => 'Meu Artigo',
    'slug' => 'meu-artigo',
    'excerpt' => 'Breve descrição do artigo...',
    'content' => '<p>Conteúdo HTML do artigo...</p>',
    'author' => 'Seu Nome',
    'read_time' => 5,
    'tags' => ['Laravel', 'Vue.js'],
    'is_published' => true,
    'published_at' => now(),
]);
```

Ou usando o método do model:

```php
$post->publish(); // Publica o post
$post->addTag('TypeScript'); // Adiciona uma tag
$post->generateSlug(); // Gera slug automaticamente
```

## Rotas Disponíveis

### Frontend (Inertia.js)
- `GET /` - Página principal com perfil, links e posts
- `GET /posts/{slug}` - Visualização de post individual

### API
- `POST /api/newsletter/subscribe` - Inscrição na newsletter
  - Body: `{ "email": "email@example.com", "name": "Nome (opcional)" }`
  - Response: `{ "message": "...", "data": {...} }`

## Componentes Vue

### LinkCard.vue
Componente para exibir um card de link social:
```vue
<LinkCard :link="link" />
```

### PostCard.vue
Componente para exibir um card de post na listagem:
```vue
<PostCard :post="post" />
```

### NewsletterForm.vue
Formulário de inscrição na newsletter com validação e feedback:
```vue
<NewsletterForm />
```

## Testes

```bash
php artisan test
```

## Build de Produção

```bash
npm run build
php artisan optimize
```

## Segurança

- ✅ Proteção CSRF em todos os formulários
- ✅ Validação Laravel em todas as entradas
- ✅ Proteção contra SQL injection via Eloquent
- ✅ Proteção XSS com sanitização de conteúdo
- ✅ Form Requests com mensagens personalizadas
- ✅ Rate limiting em endpoints públicos

## Deploy

1. Configure as variáveis de ambiente de produção
2. Execute `php artisan migrate --force`
3. Execute `php artisan db:seed --force`
4. Compile os assets: `npm run build`
5. Otimize: `php artisan optimize`
6. Configure workers de fila se necessário
7. Configure cron jobs se necessário

## Contribuindo

Pull requests são bem-vindos! Para mudanças maiores, abra uma issue primeiro para discutir o que você gostaria de mudar.

## Licença

MIT License. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

---

**Desenvolvido com ❤️ usando Laravel + Vue.js + Inertia.js**
