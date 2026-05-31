<?php

declare(strict_types=1);

namespace App\Actions\Youtube;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fetches the latest uploads of a YouTube channel and caches the result.
 *
 * Uses the YouTube Data API v3 when an API key is configured (reliable from any
 * IP), otherwise falls back to the public RSS feed. The RSS feed can be
 * throttled or served reduced/empty on datacenter IPs (e.g. Laravel Cloud),
 * which is why the API key is preferred in production.
 */
class GetChannelVideosAction
{
    private const FEED = 'https://www.youtube.com/feeds/videos.xml';

    private const API = 'https://www.googleapis.com/youtube/v3/playlistItems';

    private const CACHE_KEY = 'youtube.devafora.videos';

    private const UA = 'Mozilla/5.0 (compatible; DevAforaBot/1.0; +https://devafora.com)';

    /**
     * @return array<int, array{videoId: string, title: string, url: string, thumbnail: string, published_at: string}>
     */
    public function execute(int $limit = 12): array
    {
        $videos = Cache::get(self::CACHE_KEY);

        if ($videos === null) {
            $videos = $this->fetch();

            // Only cache successful (non-empty) responses so a transient
            // failure doesn't pin an empty list for an hour.
            if ($videos !== []) {
                Cache::put(self::CACHE_KEY, $videos, now()->addHour());
            }
        }

        return array_slice($videos, 0, $limit);
    }

    /**
     * @return array<int, array{videoId: string, title: string, url: string, thumbnail: string, published_at: string}>
     */
    private function fetch(): array
    {
        $channelId = (string) config('services.youtube.devafora_channel');

        if ($channelId === '') {
            return [];
        }

        $apiKey = (string) config('services.youtube.api_key');

        $videos = $apiKey !== ''
            ? $this->fetchFromApi($channelId, $apiKey)
            : [];

        // Fall back to RSS if the API isn't configured or returned nothing.
        if ($videos === []) {
            $videos = $this->fetchFromRss($channelId);
        }

        if ($videos === []) {
            Log::warning('YouTube videos fetch returned nothing', [
                'channel' => $channelId,
                'used_api' => $apiKey !== '',
            ]);
        }

        return $videos;
    }

    /**
     * @return array<int, array{videoId: string, title: string, url: string, thumbnail: string, published_at: string}>
     */
    private function fetchFromApi(string $channelId, string $apiKey): array
    {
        // The uploads playlist id is the channel id with the "UC" prefix → "UU".
        $uploadsPlaylist = preg_replace('/^UC/', 'UU', $channelId);

        try {
            $response = Http::timeout(8)->get(self::API, [
                'part' => 'snippet',
                'playlistId' => $uploadsPlaylist,
                'maxResults' => 20,
                'key' => $apiKey,
            ]);

            if (! $response->successful()) {
                Log::warning('YouTube API request failed', ['status' => $response->status(), 'body' => $response->body()]);

                return [];
            }

            return collect($response->json('items', []))
                ->map(function (array $item): ?array {
                    $snippet = $item['snippet'] ?? [];
                    $videoId = $snippet['resourceId']['videoId'] ?? '';

                    if ($videoId === '') {
                        return null;
                    }

                    return $this->video(
                        (string) $videoId,
                        (string) ($snippet['title'] ?? ''),
                        (string) ($snippet['publishedAt'] ?? ''),
                    );
                })
                ->filter()
                ->values()
                ->all();
        } catch (\Throwable $e) {
            report($e);

            return [];
        }
    }

    /**
     * @return array<int, array{videoId: string, title: string, url: string, thumbnail: string, published_at: string}>
     */
    private function fetchFromRss(string $channelId): array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => self::UA,
                'Accept' => 'application/atom+xml, application/xml, text/xml',
            ])->timeout(8)->get(self::FEED, ['channel_id' => $channelId]);

            if (! $response->successful()) {
                return [];
            }

            $xml = @simplexml_load_string($response->body());

            if ($xml === false) {
                return [];
            }

            $namespaces = $xml->getNamespaces(true);
            $videos = [];

            foreach ($xml->entry as $entry) {
                $yt = $entry->children($namespaces['yt'] ?? 'http://www.youtube.com/xml/schemas/2015');
                $videoId = (string) $yt->videoId;

                if ($videoId === '') {
                    continue;
                }

                $videos[] = $this->video(
                    $videoId,
                    (string) $entry->title,
                    (string) $entry->published,
                );
            }

            return $videos;
        } catch (\Throwable $e) {
            report($e);

            return [];
        }
    }

    /**
     * @return array{videoId: string, title: string, url: string, thumbnail: string, published_at: string}
     */
    private function video(string $videoId, string $title, string $publishedAt): array
    {
        return [
            'videoId' => $videoId,
            'title' => $title,
            'url' => 'https://www.youtube.com/watch?v='.$videoId,
            'thumbnail' => 'https://i.ytimg.com/vi/'.$videoId.'/hqdefault.jpg',
            'published_at' => $publishedAt !== ''
                ? Carbon::parse($publishedAt)->toIso8601String()
                : '',
        ];
    }
}
