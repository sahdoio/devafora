<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Models\Link;
use App\Models\NewsletterSubscription;
use App\Support\Posts\PostRepository;

class GetDashboardStatsAction
{
    public function __construct(
        private readonly PostRepository $posts,
    ) {}

    public function execute(): array
    {
        // Count distinct posts (folders), regardless of language.
        $slugs = $this->posts->slugs();
        $totalPosts = count($slugs);
        $publishedPosts = count(array_filter(
            $slugs,
            fn (string $slug): bool => $this->posts->isPublishedInAnyLocale($slug),
        ));
        $draftPosts = $totalPosts - $publishedPosts;

        $totalLinks = Link::count();
        $activeLinks = Link::where('is_active', true)->count();

        $totalSubscriptions = NewsletterSubscription::count();
        $activeSubscriptions = NewsletterSubscription::where('is_active', true)->count();

        // Newsletter growth - last 30 days
        $newsletterGrowth = NewsletterSubscription::where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($item): array => [
                'date' => $item->date,
                'count' => $item->count,
            ]);

        // Recent subscriptions - last 7 days
        $recentSubscriptions = NewsletterSubscription::where('created_at', '>=', now()->subDays(7))
            ->count();

        return [
            'posts' => [
                'total' => $totalPosts,
                'published' => $publishedPosts,
                'draft' => $draftPosts,
            ],
            'links' => [
                'total' => $totalLinks,
                'active' => $activeLinks,
            ],
            'newsletter' => [
                'total' => $totalSubscriptions,
                'active' => $activeSubscriptions,
                'recent' => $recentSubscriptions,
                'growth' => $newsletterGrowth,
            ],
        ];
    }
}
