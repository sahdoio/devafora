<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Actions\Links\GetActiveLinksAction;
use App\Actions\Posts\GetLatestPostsAction;
use App\Actions\Profile\GetActiveProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\LinkResource;
use App\Http\Resources\PostListResource;
use App\Http\Resources\ProfileResource;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(
        GetActiveProfileAction $getActiveProfileAction,
        GetActiveLinksAction $getActiveLinksAction,
        GetLatestPostsAction $getLatestPostsAction
    ): Response {
        $profile = $getActiveProfileAction->execute();
        $links = $getActiveLinksAction->execute($profile?->id);
        $posts = $getLatestPostsAction->execute(limit: 3, profileId: $profile?->id);

        return Inertia::render('Home', [
            'profile' => $profile instanceof \App\Models\Profile ? ProfileResource::make($profile) : null,
            'links' => LinkResource::collection($links)->resolve(),
            'posts' => PostListResource::collection($posts)->resolve(),
        ]);
    }
}
