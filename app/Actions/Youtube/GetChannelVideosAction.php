<?php

declare(strict_types=1);

namespace App\Actions\Youtube;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Fetches the latest uploads of a YouTube channel from its public RSS feed
 * (no API key required) and caches the result.
 */
class GetChannelVideosAction
{
    private const FEED = 'https://www.youtube.com/feeds/videos.xml';

    private const CACHE_KEY = 'youtube.devafora.videos';

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

        try {
            $response = Http::timeout(8)->get(self::FEED, ['channel_id' => $channelId]);

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

                $videos[] = [
                    'videoId' => $videoId,
                    'title' => (string) $entry->title,
                    'url' => 'https://www.youtube.com/watch?v='.$videoId,
                    'thumbnail' => 'https://i.ytimg.com/vi/'.$videoId.'/hqdefault.jpg',
                    'published_at' => Carbon::parse((string) $entry->published)->toIso8601String(),
                ];
            }

            return $videos;
        } catch (\Throwable $e) {
            report($e);

            return [];
        }
    }
}
