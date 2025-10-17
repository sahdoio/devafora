<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Models\Link;
use App\Models\NewsletterSubscription;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

class GetDashboardStatsAction
{
    public function execute(): array
    {
        $totalPosts = Post::count();
        $publishedPosts = Post::where('is_published', true)->count();
        $draftPosts = Post::where('is_published', false)->count();

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
            ->map(fn($item): array => [
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
