<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Post\SendPostNewsletterAction;
use App\Http\Controllers\Controller;
use App\Support\Locale;
use App\Support\Posts\MarkdownPost;
use App\Support\Posts\PostRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function __construct(
        private readonly PostRepository $posts,
    ) {}

    public function index(): Response
    {
        $posts = [];

        foreach ($this->posts->slugs() as $slug) {
            $translations = [];

            foreach ($this->posts->localesOf($slug) as $locale) {
                $post = $this->posts->find($slug, $locale);

                if ($post instanceof MarkdownPost) {
                    $translations[] = [
                        'locale' => $locale,
                        'is_published' => $post->is_published,
                        'published_at' => $post->published_at?->toIso8601String(),
                        'newsletter_sent_at' => $post->newsletter_sent_at?->toIso8601String(),
                        'title' => $post->title,
                        'excerpt' => $post->excerpt,
                    ];
                }
            }

            if ($translations === []) {
                continue;
            }

            // Primary = default locale when present (localesOf is ordered pt, en).
            $primary = $translations[0];

            $posts[] = [
                'slug' => $slug,
                'title' => $primary['title'],
                'excerpt' => $primary['excerpt'],
                'available_locales' => array_column($translations, 'locale'),
                'translations' => $translations,
            ];
        }

        return Inertia::render('Admin/Posts/Index', [
            'posts' => $posts,
            'locales' => Locale::SUPPORTED,
        ]);
    }

    public function create(Request $request): Response
    {
        $locale = Locale::sanitize($request->query('lang'));

        return Inertia::render('Admin/Posts/Create', [
            'slug' => (string) $request->query('slug', ''),
            'locale' => $locale,
            'locales' => Locale::SUPPORTED,
            'template' => $this->template($locale),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePost($request);

        if ($this->posts->exists($validated['slug'], $validated['locale'])) {
            return back()
                ->withErrors(['slug' => "Já existe a versão ({$validated['locale']}) deste post."])
                ->withInput();
        }

        $this->posts->save($validated['slug'], $validated['markdown'], $validated['locale']);

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post criado com sucesso.');
    }

    public function edit(Request $request, string $slug): Response
    {
        $locale = Locale::sanitize($request->query('lang'));
        $existing = $this->posts->localesOf($slug);
        $exists = in_array($locale, $existing, true);

        if (! $exists && $existing === []) {
            abort(404);
        }

        return Inertia::render('Admin/Posts/Edit', [
            'slug' => $slug,
            'locale' => $locale,
            'exists' => $exists,
            'existingLocales' => $existing,
            'locales' => Locale::SUPPORTED,
            // When the translation does not exist yet, offer the scaffold to create it.
            'markdown' => $exists ? $this->posts->raw($slug, $locale) : $this->template($locale),
        ]);
    }

    public function update(Request $request, string $slug): RedirectResponse
    {
        $validated = $this->validatePost($request);
        $newSlug = $validated['slug'];

        if ($newSlug !== $slug && in_array($newSlug, $this->posts->slugs(), true)) {
            return back()
                ->withErrors(['slug' => 'Já existe um post com este slug.'])
                ->withInput();
        }

        // Rename the folder first so every language file and media moves with it.
        $this->posts->rename($slug, $newSlug);

        $this->posts->save($newSlug, $validated['markdown'], $validated['locale']);

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post salvo com sucesso.');
    }

    public function destroy(Request $request, string $slug): RedirectResponse
    {
        $locale = $request->query('lang');

        // Without a language, remove the whole post (all translations + media).
        if ($locale === null) {
            $this->posts->delete($slug);

            return redirect()->route('admin.posts.index')
                ->with('success', 'Post excluído com sucesso.');
        }

        // Delete a single translation; drop the folder if it was the last one.
        $locale = Locale::sanitize($locale);
        $this->posts->deleteLocale($slug, $locale);

        if ($this->posts->localesOf($slug) === []) {
            $this->posts->delete($slug);
        }

        return redirect()->route('admin.posts.index')
            ->with('success', "Tradução ({$locale}) excluída com sucesso.");
    }

    public function preview(Request $request, string $slug): Response
    {
        $locale = Locale::sanitize($request->query('lang'));

        return Inertia::render('Admin/Posts/Preview', [
            'post' => $this->posts->findOrFail($slug, $locale)->toDetailArray(),
        ]);
    }

    public function sendNewsletter(Request $request, string $slug, SendPostNewsletterAction $sendPostNewsletterAction): RedirectResponse
    {
        $locale = Locale::sanitize($request->query('lang'));
        $post = $this->posts->findOrFail($slug, $locale);

        // If newsletter was already sent, force resend (user already confirmed).
        $force = $post->newsletter_sent_at !== null;

        $result = $sendPostNewsletterAction->execute($post, $force);

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Upload a media file (image, PDF, ...) into the post's folder and return
     * its file name (to reference from the markdown) plus a preview URL.
     */
    public function uploadAsset(Request $request, string $slug): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:jpeg,png,jpg,gif,webp,svg,pdf,txt,zip', 'max:20480'],
        ]);

        $name = $this->posts->storeAsset($slug, $request->file('file'));

        return response()->json([
            'name' => $name,
            'url' => MarkdownPost::assetUrl($slug, $name),
        ]);
    }

    /**
     * @return array{slug: string, markdown: string, locale: string}
     */
    private function validatePost(Request $request): array
    {
        return $request->validate([
            'slug' => ['required', 'string', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'max:255'],
            'markdown' => ['required', 'string'],
            'locale' => ['required', 'string', Rule::in(Locale::SUPPORTED)],
        ]);
    }

    private function template(string $locale): string
    {
        $date = now()->toDateString();

        if ($locale === 'en') {
            return <<<MD
            ---
            title: 'Post title'
            excerpt: 'Short summary shown in cards and the newsletter email.'
            author: DevAfora
            image: ''
            tags:
              - tag1
            published_at: '{$date}'
            draft: true
            newsletter_sent_at: ~
            ---

            ## Subtitle

            Write your content in markdown here.

            ```php
            echo "Hello, world!";
            ```
            MD;
        }

        return <<<MD
        ---
        title: 'Título do post'
        excerpt: 'Resumo curto que aparece nos cards e no e-mail.'
        author: DevAfora
        image: ''
        tags:
          - tag1
        published_at: '{$date}'
        draft: true
        newsletter_sent_at: ~
        ---

        ## Subtítulo

        Escreva seu conteúdo em markdown aqui.

        ```php
        echo "Olá, mundo!";
        ```
        MD;
    }
}
