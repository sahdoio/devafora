<?php

declare(strict_types=1);

namespace App\Support\Posts;

use App\Support\Locale;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * File-backed post store. Each post is a *folder* (a "page bundle") and may hold
 * one markdown file per language plus shared media:
 *
 *   content/posts/{slug}/
 *       index.md      ← Portuguese (default locale)
 *       index.en.md   ← English (optional translation)
 *       capa.png      ← images, PDFs and any other media (shared across languages)
 *       guia.pdf
 *
 * Media is referenced in the markdown by file name only (e.g. `![](capa.png)`)
 * and served through the `posts.asset` route. This is the single place that
 * knows where posts are stored and how to read/write them.
 */
class PostRepository
{
    public function __construct(
        private readonly MarkdownPostParser $parser,
    ) {}

    /**
     * Absolute path to the posts root directory.
     */
    public function directory(): string
    {
        return base_path('content/posts');
    }

    /**
     * Absolute path to a single post's folder.
     */
    public function postDirectory(string $slug): string
    {
        return $this->directory().DIRECTORY_SEPARATOR.$slug;
    }

    /**
     * The index file name for a given locale (default locale uses bare index.md).
     */
    public function filename(string $locale): string
    {
        return $locale === Locale::DEFAULT ? 'index.md' : "index.{$locale}.md";
    }

    /**
     * Absolute path to a post's index file for a locale.
     */
    public function path(string $slug, ?string $locale = null): string
    {
        return $this->postDirectory($slug).DIRECTORY_SEPARATOR.$this->filename($this->resolve($locale));
    }

    /**
     * All posts that exist in the given locale (including drafts), newest first.
     *
     * @return Collection<int, MarkdownPost>
     */
    public function all(?string $locale = null): Collection
    {
        $locale = $this->resolve($locale);

        if (! File::isDirectory($this->directory())) {
            return collect();
        }

        return collect(File::directories($this->directory()))
            ->map(fn (string $dir): string => basename($dir))
            ->filter(fn (string $slug): bool => $this->exists($slug, $locale))
            ->map(fn (string $slug): ?MarkdownPost => $this->find($slug, $locale))
            ->filter()
            ->sortByDesc(fn (MarkdownPost $post): int => $post->published_at?->getTimestamp() ?? PHP_INT_MAX)
            ->values();
    }

    /**
     * Published posts visible on the public site for the given locale, newest first.
     *
     * @return Collection<int, MarkdownPost>
     */
    public function published(?string $locale = null): Collection
    {
        return $this->all($locale)
            ->filter(fn (MarkdownPost $post): bool => $post->isLive())
            ->values();
    }

    /**
     * @return Collection<int, MarkdownPost>
     */
    public function latest(int $limit, ?string $locale = null): Collection
    {
        return $this->published($locale)->take($limit)->values();
    }

    public function exists(string $slug, ?string $locale = null): bool
    {
        return File::exists($this->path($slug, $locale));
    }

    public function find(string $slug, ?string $locale = null): ?MarkdownPost
    {
        $locale = $this->resolve($locale);

        if (! $this->exists($slug, $locale)) {
            return null;
        }

        return $this->parser->parse($slug, File::get($this->path($slug, $locale)), $locale);
    }

    /**
     * Full raw file content (front matter + body) for the editor.
     */
    public function raw(string $slug, ?string $locale = null): string
    {
        return File::get($this->path($slug, $locale));
    }

    public function findOrFail(string $slug, ?string $locale = null): MarkdownPost
    {
        return $this->find($slug, $locale) ?? throw new NotFoundHttpException("Post [{$slug}] not found.");
    }

    /**
     * Find a published post for the locale or 404 (public-facing).
     */
    public function findPublishedOrFail(string $slug, ?string $locale = null): MarkdownPost
    {
        $post = $this->find($slug, $locale);

        if (! $post instanceof MarkdownPost || ! $post->isLive()) {
            throw new NotFoundHttpException("Post [{$slug}] not found.");
        }

        return $post;
    }

    /**
     * Every post folder slug (regardless of language), newest first by default version.
     *
     * @return array<int, string>
     */
    public function slugs(): array
    {
        if (! File::isDirectory($this->directory())) {
            return [];
        }

        return collect(File::directories($this->directory()))
            ->map(fn (string $dir): string => basename($dir))
            ->filter(fn (string $slug): bool => $this->localesOf($slug) !== [])
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Which locales a post has a file for (default locale first).
     *
     * @return array<int, string>
     */
    public function localesOf(string $slug): array
    {
        return array_values(array_filter(
            Locale::SUPPORTED,
            fn (string $locale): bool => File::exists($this->path($slug, $locale)),
        ));
    }

    public function isPublishedInAnyLocale(string $slug): bool
    {
        foreach ($this->localesOf($slug) as $locale) {
            if ($this->find($slug, $locale)?->isLive()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Persist raw index content for the given slug + locale, creating the folder.
     */
    public function save(string $slug, string $rawMarkdown, ?string $locale = null): void
    {
        File::ensureDirectoryExists($this->postDirectory($slug));
        File::put($this->path($slug, $locale), $rawMarkdown);
    }

    /**
     * Rename a post folder (preserving every language file and media inside it).
     */
    public function rename(string $from, string $to): void
    {
        if ($from === $to) {
            return;
        }

        File::moveDirectory($this->postDirectory($from), $this->postDirectory($to));
    }

    /**
     * Delete a post and all of its languages/media.
     */
    public function delete(string $slug): void
    {
        if (File::isDirectory($this->postDirectory($slug))) {
            File::deleteDirectory($this->postDirectory($slug));
        }
    }

    /**
     * Delete a single language version of a post (folder/media stay).
     */
    public function deleteLocale(string $slug, string $locale): void
    {
        if ($this->exists($slug, $locale)) {
            File::delete($this->path($slug, $locale));
        }
    }

    /**
     * Stamp `newsletter_sent_at` in the post's front matter (in its own locale file).
     */
    public function markNewsletterSent(MarkdownPost $post): void
    {
        [$frontMatter, $body] = $this->parser->split($this->raw($post->slug, $post->locale));

        $frontMatter['newsletter_sent_at'] = now()->toIso8601String();

        $this->save($post->slug, $this->parser->compose($frontMatter, $body), $post->locale);
    }

    /**
     * Resolve the absolute path of a media file inside a post folder, or null
     * if it escapes the folder, is an index file, or does not exist.
     */
    public function assetPath(string $slug, string $relative): ?string
    {
        $base = $this->postDirectory($slug);
        $target = realpath($base.DIRECTORY_SEPARATOR.$relative);

        if ($target === false) {
            return null;
        }

        // Never serve the markdown index files themselves.
        if (preg_match('/^index(\.[a-z]{2})?\.md$/i', basename($target))) {
            return null;
        }

        // Guard against path traversal: the resolved path must stay inside the folder.
        if (! str_starts_with($target, realpath($base).DIRECTORY_SEPARATOR)) {
            return null;
        }

        return File::isFile($target) ? $target : null;
    }

    /**
     * Store an uploaded media file inside the post folder and return its
     * file name (to reference from the markdown).
     */
    public function storeAsset(string $slug, UploadedFile $file): string
    {
        File::ensureDirectoryExists($this->postDirectory($slug));

        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            .'-'.Str::random(6).'.'.$file->getClientOriginalExtension();

        $file->move($this->postDirectory($slug), $name);

        return $name;
    }

    private function resolve(?string $locale): string
    {
        return Locale::sanitize($locale ?? app()->getLocale());
    }
}
