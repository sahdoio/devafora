<?php

declare(strict_types=1);

namespace App\Support\Posts;

use Illuminate\Support\Carbon;

/**
 * Immutable representation of a single markdown post (content/posts/{slug}.md).
 *
 * Properties are snake_case on purpose so the value object can be handed straight
 * to the newsletter Blade view and serialized to the Vue components without mapping.
 */
class MarkdownPost
{
    /**
     * @param  array<int, string>  $tags
     */
    public function __construct(
        public readonly string $slug,
        public readonly string $locale,
        public readonly string $title,
        public readonly string $excerpt,
        public readonly ?string $author,
        public readonly ?string $image,
        public readonly array $tags,
        public readonly ?Carbon $published_at,
        public readonly bool $is_published,
        public readonly int $read_time,
        public readonly ?Carbon $newsletter_sent_at,
        public readonly string $html,
        public readonly string $markdown,
    ) {}

    /**
     * Whether the post should be visible on the public site.
     */
    public function isLive(): bool
    {
        return $this->is_published
            && $this->published_at !== null
            && $this->published_at->lessThanOrEqualTo(now());
    }

    /**
     * Build the public URL for a media file stored inside a post folder.
     */
    public static function assetUrl(string $slug, string $file): string
    {
        return '/posts/'.$slug.'/assets/'.ltrim($file, './');
    }

    /**
     * Resolve the cover image to a usable URL. External URLs and root-relative
     * paths are kept as-is; a bare file name is resolved to the post's media
     * folder via the asset route.
     */
    public function imageUrl(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (preg_match('#^([a-z][a-z0-9+.\-]*:|//|/)#i', $this->image)) {
            return $this->image;
        }

        return self::assetUrl($this->slug, $this->image);
    }

    /**
     * Shape consumed by post cards / listings (Post/Index, Home, PostCard).
     *
     * @return array<string, mixed>
     */
    public function toListArray(): array
    {
        return [
            'id' => $this->slug,
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'author' => $this->author,
            'image' => $this->imageUrl(),
            'read_time' => $this->read_time,
            'tags' => $this->tags,
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }

    /**
     * Shape consumed by the full post view (Post/Show, admin Preview).
     *
     * @return array<string, mixed>
     */
    public function toDetailArray(): array
    {
        return [
            ...$this->toListArray(),
            'content' => $this->html,
        ];
    }

    /**
     * Shape consumed by the admin listing/editor.
     *
     * @return array<string, mixed>
     */
    public function toAdminArray(): array
    {
        return [
            'slug' => $this->slug,
            'locale' => $this->locale,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'author' => $this->author,
            'tags' => $this->tags,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at?->toIso8601String(),
            'newsletter_sent_at' => $this->newsletter_sent_at?->toIso8601String(),
            'markdown' => $this->markdown,
        ];
    }
}
