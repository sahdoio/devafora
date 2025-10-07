# DevAfora

A customizable link aggregator built with Laravel 11, Vue 3, and Inertia.js featuring a modern dark interface, integrated blog system, and newsletter functionality.

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js 18 or higher
- SQLite3

## Installation

Clone the repository and install dependencies:

```
git clone https://github.com/sahdoio/devafora.git
cd devafora
composer install
npm install
```

Configure environment and database:

```
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
```

Start development servers:

```
php artisan serve
npm run dev
```

Access the application at http://localhost:8000

## Architecture

This project follows an Action-based architecture pattern where business logic is completely isolated from controllers. The separation of concerns is strictly enforced across the entire application.

### Action Pattern

All business logic resides in Action classes organized by domain. Each Action has a single execute method that performs one specific operation. Actions are injected into controllers via dependency injection and can be composed together for complex workflows.

Actions are located in app/Actions and organized into domain folders such as Profile, Links, Posts, and Newsletter. For example, subscribing to a newsletter involves the SubscribeToNewsletterAction which handles transaction management, duplicate checking, and calling other actions like SendWelcomeEmailAction.

### Controllers

Controllers are thin by design. They only receive Actions via dependency injection, call their execute methods, and return responses. Frontend controllers return Inertia.js views while API controllers return JSON responses.

Controllers are separated into Frontend and Api namespaces. Frontend controllers at app/Http/Controllers/Frontend handle web routes and return Inertia responses. Api controllers at app/Http/Controllers/Api handle API routes and return JSON responses.

### Resources Layer

All data transformation happens through Laravel Resources before reaching the frontend. This creates a consistent API contract and handles things like date formatting, URL generation, and field selection. Every model has at least one Resource class, and some have multiple for different contexts.

Resources are located in app/Http/Resources. For example, PostResource provides full post data for individual pages while PostListResource provides a lighter version for post listings.

### Models with Domain Logic

Models contain domain-specific business methods using Domain-Driven Design principles. Methods like publish, subscribe, addTag, and generateSlug encapsulate business rules that belong to the entity itself. Database queries are handled through Eloquent scopes for reusability.

Models are located in app/Models and include Profile, Link, Post, and NewsletterSubscription. Each model defines its relationships, scopes, and domain methods.

### Frontend Architecture

The frontend uses Vue 3 with TypeScript and Inertia.js for a seamless SPA experience without building a separate API. Components are organized into reusable pieces like LinkCard, PostCard, and NewsletterForm. Pages are Inertia views that receive data as props from controllers.

Pages are located in resources/js/pages while reusable components are in resources/js/components. The application uses Tailwind CSS 4 for styling and Vite for asset bundling.

## Project Structure

The application is organized into clear layers:

**Backend Structure:**
- app/Actions - Business logic organized by domain
- app/Http/Controllers/Frontend - Web controllers returning Inertia views
- app/Http/Controllers/Api - API controllers returning JSON
- app/Http/Resources - Data transformation layer
- app/Http/Requests - Form validation
- app/Models - Eloquent models with domain methods

**Frontend Structure:**
- resources/js/components - Reusable Vue components
- resources/js/pages - Inertia page components
- resources/css - Application styles

**Database Structure:**
- database/migrations - Database schema definitions
- database/factories - Model factories for testing and seeding
- database/seeders - Database seeders

## Database Schema

The application uses four main tables:

**profiles** - User profile information including name, bio, photo, and active status

**links** - Social media and external links associated with profiles, with support for custom icons, descriptions, ordering, and activation status

**posts** - Blog posts with title, slug, excerpt, content, author, featured image, read time estimation, tags stored as JSON, and publication status

**newsletter_subscriptions** - Email subscriptions with name, email, subscription status, and subscription/unsubscription timestamps

All tables include timestamps and appropriate foreign key relationships with cascade deletes where needed.

## Available Routes

**Frontend Routes:**
- GET / - Homepage displaying profile, links, and recent posts
- GET /posts/{slug} - Individual post page

**API Routes:**
- POST /api/newsletter/subscribe - Newsletter subscription endpoint accepting email and optional name

## Configuration

### Email Setup

To enable newsletter email functionality, configure your mail driver in the .env file. The application supports Mailtrap for development and any SMTP service for production. Set MAIL_MAILER, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, and MAIL_FROM_ADDRESS.

### Customization

Edit database/seeders/DatabaseSeeder.php to customize your profile information, social links, and initial posts. After editing, run php artisan migrate:fresh --seed to reset the database with your changes.

## Development

Run both servers in separate terminals during development. The Laravel server handles backend requests while Vite provides hot module replacement for frontend changes.

For production deployment, build assets with npm run build and optimize Laravel with php artisan optimize.

## License

MIT License
