<?php

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
        GetActiveProfileAction $getProfile,
        GetActiveLinksAction $getLinks,
        GetLatestPostsAction $getLatestPosts
    ): Response {
        $profile = $getProfile->execute();
        $links = $getLinks->execute($profile?->id);
        $posts = $getLatestPosts->execute(limit: 3, profileId: $profile?->id);

        return Inertia::render('Home', [
            'profile' => $profile ? ProfileResource::make($profile) : null,
            'links' => LinkResource::collection($links)->resolve(),
            'posts' => PostListResource::collection($posts)->resolve(),
        ]);
    }
}
