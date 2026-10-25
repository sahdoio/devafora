<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Actions\Profile\GetActiveProfileAction;
use App\Actions\Youtube\GetChannelVideosAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use App\Support\Posts\MarkdownPost;
use App\Support\Posts\PostRepository;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(
        GetActiveProfileAction $getActiveProfileAction,
        GetChannelVideosAction $getChannelVideosAction,
        PostRepository $posts
    ): Response {
        $profile = $getActiveProfileAction->execute();

        return Inertia::render('Home', [
            'profile' => $profile instanceof Profile ? ProfileResource::make($profile) : null,
            'posts' => $posts->latest(6)
                ->map(fn (MarkdownPost $post): array => $post->toListArray())
                ->values()
                ->all(),
            // Deferred: a cold cache means a slow YouTube call, so don't block first paint on it.
            'videos' => Inertia::defer(fn (): array => $getChannelVideosAction->execute(12)),
        ]);
    }
}
