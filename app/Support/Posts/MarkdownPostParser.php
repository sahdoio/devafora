<?php

declare(strict_types=1);

namespace App\Support\Posts;

use Illuminate\Support\Carbon;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\FrontMatter\Data\SymfonyYamlFrontMatterParser;
use League\CommonMark\Extension\FrontMatter\FrontMatterParser;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TaskList\TaskListExtension;
use League\CommonMark\MarkdownConverter;
use Symfony\Component\Yaml\Yaml;

/**
 * Parses a raw `.md` file (YAML front matter + body) into a {@see MarkdownPost}
 * and renders the body to HTML.
 *
 * Note: the GitHub-flavored extension bundle is intentionally NOT used as a whole
 * because it ships DisallowedRawHtmlExtension, which strips <iframe> tags and would
 * break YouTube embeds. We register only the safe-to-keep GFM pieces and allow raw
 * HTML (the author is trusted).
 */
class MarkdownPostParser
{
    private const WORDS_PER_MINUTE = 200;

    private readonly MarkdownConverter $converter;

    private readonly FrontMatterParser $frontMatterParser;

    public function __construct()
    {
        $environment = new Environment([
            'html_input' => 'allow',
            'allow_unsafe_links' => true,
        ]);

        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new TableExtension);
        $environment->addExtension(new StrikethroughExtension);
        $environment->addExtension(new AutolinkExtension);
        $environment->addExtension(new TaskListExtension);

        $this->converter = new MarkdownConverter($environment);
        $this->frontMatterParser = new FrontMatterParser(new SymfonyYamlFrontMatterParser);
    }

    public function parse(string $slug, string $raw, string $locale = \App\Support\Locale::DEFAULT): MarkdownPost
    {
        [$frontMatter, $body] = $this->split($raw);

        $html = $this->rewriteAssetUrls((string) $this->converter->convert($body), $slug);

        return new MarkdownPost(
            slug: $slug,
            locale: $locale,
            title: (string) ($frontMatter['title'] ?? $slug),
            excerpt: (string) ($frontMatter['excerpt'] ?? ''),
            author: isset($frontMatter['author']) ? (string) $frontMatter['author'] : null,
            image: isset($frontMatter['image']) ? (string) $frontMatter['image'] : null,
            tags: $this->normalizeTags($frontMatter['tags'] ?? []),
            published_at: $this->toDate($frontMatter['published_at'] ?? null),
            is_published: ! ($frontMatter['draft'] ?? false),
            read_time: $this->readTime($frontMatter['read_time'] ?? null, $html),
            newsletter_sent_at: $this->toDate($frontMatter['newsletter_sent_at'] ?? null),
            html: $html,
            markdown: $body,
        );
    }

    /**
     * Split raw file content into [frontMatter array, body markdown].
     *
     * @return array{0: array<string, mixed>, 1: string}
     */
    public function split(string $raw): array
    {
        $result = $this->frontMatterParser->parse($raw);

        $frontMatter = $result->getFrontMatter();

        return [
            is_array($frontMatter) ? $frontMatter : [],
            $result->getContent(),
        ];
    }

    /**
     * Compose a raw `.md` file from a front matter array and a markdown body.
     *
     * @param  array<string, mixed>  $frontMatter
     */
    public function compose(array $frontMatter, string $body): string
    {
        $yaml = Yaml::dump($frontMatter, inline: 4, indent: 2, flags: Yaml::DUMP_NULL_AS_TILDE);

        return "---\n".$yaml."---\n\n".ltrim($body)."\n";
    }

    /**
     * Rewrite relative `src`/`href` URLs in the rendered HTML so they point at
     * the post's media folder via the asset route. Absolute URLs, root-relative
     * paths, anchors and special schemes are left untouched.
     */
    private function rewriteAssetUrls(string $html, string $slug): string
    {
        return (string) preg_replace_callback(
            '/\b(src|href)="([^"]*)"/i',
            function (array $m) use ($slug): string {
                $url = $m[2];

                if ($url === '' || preg_match('~^([a-z][a-z0-9+.\-]*:|//|/|#)~i', $url)) {
                    return $m[0];
                }

                return $m[1].'="'.MarkdownPost::assetUrl($slug, $url).'"';
            },
            $html,
        );
    }

    /**
     * @param  mixed  $tags
     * @return array<int, string>
     */
    private function normalizeTags($tags): array
    {
        if (is_string($tags)) {
            $tags = array_map('trim', explode(',', $tags));
        }

        if (! is_array($tags)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn ($tag): string => (string) $tag,
            $tags,
        ), static fn (string $tag): bool => $tag !== ''));
    }

    private function toDate($value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function readTime($explicit, string $html): int
    {
        if (is_numeric($explicit) && (int) $explicit > 0) {
            return (int) $explicit;
        }

        // Count words from the rendered HTML's text. strip_tags() is safe here
        // because angle brackets in code are already entity-escaped (&lt;/&gt;).
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5);
        $words = str_word_count($text);

        return max(1, (int) ceil($words / self::WORDS_PER_MINUTE));
    }
}
