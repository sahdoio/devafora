# DevAfora - Linktree Clone com Laravel & Vue.js

Um agregador de links personalizado estilo Linktree, construído com arquitetura limpa e tecnologias modernas. Apresenta um design dark elegante, blog integrado, newsletter com Postmark e código totalmente testável seguindo os princípios de Clean Architecture.

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat&logo=laravel&logoColor=white)
![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?style=flat&logo=vue.js&logoColor=white)
![TypeScript](https://img.shields.io/badge/TypeScript-5-3178C6?style=flat&logo=typescript&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-4-38B2AC?style=flat&logo=tailwind-css&logoColor=white)

## ✨ Features

- 🔗 **Agregador de Links** - Centralize todos seus links sociais em um único lugar
- 📝 **Blog Integrado** - Sistema completo de blog com suporte a Markdown/HTML
- 💌 **Newsletter** - Integração com Postmark para emails transacionais
- 🎨 **Design Moderno** - Interface dark elegante com animações suaves
- 📱 **Responsivo** - Funciona perfeitamente em desktop e mobile
- ⚡ **SPA com Inertia.js** - Experiência de Single Page Application sem APIs REST
- 🏗️ **Arquitetura Limpa** - Actions, Resources e Controllers magros
- 🔒 **Type Safe** - TypeScript no frontend para maior segurança
- 📊 **Otimizado** - Eager loading, query optimization e asset bundling

## 🛠️ Stack Tecnológico

### Backend
- **Laravel 11** - Framework PHP com Eloquent ORM
- **SQLite** - Banco de dados leve e portátil
- **Postmark** - Serviço de email transacional
- **Actions Pattern** - Lógica de negócio isolada e reutilizável

### Frontend
- **Vue.js 3** - Framework JavaScript reativo
- **TypeScript** - Superset tipado do JavaScript
- **Inertia.js** - Adapter para SPAs com Laravel
- **Tailwind CSS 4** - Framework CSS utility-first
- **Vite** - Build tool ultrarrápido

## 📋 Pré-requisitos

- PHP 8.2+
- Composer
- Node.js 18+ & NPM
- SQLite3

## 🚀 Instalação

1. **Clone o repositório**
```bash
git clone https://github.com/sahdoio/devafora.git
cd devafora
```

2. **Instale as dependências PHP**
```bash
composer install
```

3. **Instale as dependências JavaScript**
```bash
npm install
```

4. **Configure o ambiente**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configure o banco de dados**
```bash
touch database/database.sqlite
php artisan migrate --seed
```

6. **Configure o Postmark (opcional)**

Adicione suas credenciais no `.env`:
```env
MAIL_MAILER=postmark
MAIL_FROM_NAME='Seu Nome'
MAIL_FROM_ADDRESS=seu@email.com
MAIL_CC=copia@email.com
POSTMARK_TOKEN='seu-token-aqui'
```

7. **Inicie o servidor de desenvolvimento**

Em um terminal:
```bash
php artisan serve
```

Em outro terminal:
```bash
npm run dev
```

Acesse: `http://localhost:8000`

## 📁 Estrutura do Projeto

```
app/
├── Actions/
│   └── Newsletter/              # Lógica de negócio isolada
│       ├── SubscribeToNewsletterAction.php
│       └── SendNewsletterWelcomeEmailAction.php
├── Http/
│   ├── Controllers/
│   │   └── Frontend/            # Controllers para frontend
│   │       ├── HomeController.php
│   │       ├── BlogController.php
│   │       └── NewsletterController.php
│   └── Resources/               # Camada de apresentação
│       ├── ProfileResource.php
│       ├── LinkResource.php
│       ├── PostResource.php
│       └── NewsletterSubscriptionResource.php
├── Models/                      # Models Eloquent com lógica de domínio
│   ├── Profile.php
│   ├── Link.php
│   ├── Post.php
│   └── NewsletterSubscription.php
└── Mail/                        # Mailables
    └── NewsletterWelcomeMail.php

resources/
├── js/
│   ├── components/              # Componentes Vue reutilizáveis
│   │   ├── LinkCard.vue
│   │   ├── Newsletter.vue
│   │   ├── PostPreview.vue
│   │   └── SocialLink.vue
│   ├── layouts/                 # Layouts da aplicação
│   │   └── PublicLayout.vue
│   └── pages/                   # Páginas Inertia
│       ├── Home.vue
│       ├── Blog.vue
│       └── Welcome.vue
└── views/
    └── emails/                  # Templates de email
        └── newsletter/
            └── welcome.blade.php
```

## 🏗️ Arquitetura

### Actions Pattern

Toda lógica de negócio está encapsulada em Actions, mantendo controllers limpos:

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

### Resources

Laravel Resources transformam Models em JSON estruturado:

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
            'author' => $this->author,
            'publishedAt' => $this->published_at?->format('Y-m-d'),
            'readTime' => $this->read_time ? "{$this->read_time} min" : null,
            'image' => $this->image,
            'tags' => $this->tags,
        ];
    }
}
```

### Models com Lógica de Domínio

Models contêm métodos de negócio relevantes ao domínio:

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

## 🗄️ Banco de Dados

### Tabelas Principais

**profiles** - Informações do perfil
```sql
- id
- name
- bio
- photo
- is_active
- timestamps
```

**links** - Links sociais ordenáveis
```sql
- id
- profile_id (FK)
- title
- description
- url
- icon
- order
- is_active
- timestamps
```

**posts** - Artigos do blog
```sql
- id
- profile_id (FK)
- title
- slug (unique)
- excerpt
- content (longtext)
- author
- image
- read_time
- tags (json)
- published_at
- is_published
- timestamps
```

**newsletter_subscriptions** - Assinantes da newsletter
```sql
- id
- email (unique)
- name
- is_active
- subscribed_at
- unsubscribed_at
- timestamps
```

## 🎨 Customização

### Alterar Perfil

Edite o seeder `ProfileSeeder.php`:

```php
Profile::factory()->create([
    'name' => 'Seu Nome',
    'bio' => 'Sua bio aqui...',
    'photo' => '/images/seu-perfil.jpg',
]);
```

### Adicionar Links

Edite o seeder `LinkSeeder.php`:

```php
$links = [
    [
        'title' => 'GitHub',
        'description' => 'Meus projetos',
        'url' => 'https://github.com/seu-usuario',
        'icon' => 'github',
        'order' => 0,
    ],
];
```

### Criar Novo Post

Use o Tinker ou crie via seeder:

```bash
php artisan tinker
```

```php
$profile = Profile::first();
$profile->posts()->create([
    'title' => 'Meu Novo Artigo',
    'slug' => 'meu-novo-artigo',
    'excerpt' => 'Descrição curta...',
    'content' => '<p>Conteúdo HTML aqui...</p>',
    'author' => 'Seu Nome',
    'read_time' => 5,
    'tags' => ['Tag1', 'Tag2'],
    'is_published' => true,
    'published_at' => now(),
]);
```

## 🧪 Testes

```bash
php artisan test
```

## 📦 Build para Produção

```bash
npm run build
php artisan optimize
```

## 🔒 Segurança

- CSRF Protection em todos os formulários
- Validação de dados com Laravel Validation
- SQL Injection protection via Eloquent
- XSS Protection com `v-html` apenas em conteúdo confiável
- Rate Limiting nos endpoints públicos

## 🚀 Deploy

### Requisitos de Produção
- PHP 8.2+
- Composer
- Node.js (para build)
- SQLite ou MySQL/PostgreSQL
- Nginx ou Apache

### Passos para Deploy

1. Clone e configure o ambiente
2. Configure variáveis de ambiente de produção
3. Execute migrations: `php artisan migrate --force`
4. Execute seeders: `php artisan db:seed --force`
5. Build assets: `npm run build`
6. Otimize: `php artisan optimize`
7. Configure queue worker para emails
8. Configure cron para schedule (se necessário)

## 🤝 Contribuindo

Contribuições são bem-vindas! Por favor:

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📝 Boas Práticas Implementadas

- ✅ **Actions Pattern** - Lógica de negócio isolada
- ✅ **Resources** - Camada de apresentação consistente
- ✅ **Type Safety** - TypeScript no frontend
- ✅ **Eager Loading** - Evita N+1 queries
- ✅ **Transactions** - Garantia de consistência
- ✅ **Queue Jobs** - Emails enviados em background
- ✅ **Validation** - Dados sempre validados
- ✅ **SOLID Principles** - Código limpo e manutenível

## 📚 Aprendizados

Este projeto demonstra:
- Arquitetura limpa com Laravel
- Integração Vue.js + TypeScript com Inertia
- Padrões de design (Actions, Resources, Repository)
- Integração com serviços externos (Postmark)
- Otimização de performance
- Code organization e best practices

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 👤 Autor

**Lucas Sahdo**

- GitHub: [@sahdoio](https://github.com/sahdoio)
- Twitter: [@devafora](https://twitter.com/devafora)
- YouTube: [@devafora](https://youtube.com/@devafora)

## 🙏 Agradecimentos

- Laravel Team
- Vue.js Team
- Inertia.js Team
- Tailwind CSS Team
- Toda a comunidade open source

---

⭐️ Se este projeto foi útil, considere dar uma estrela no GitHub!
