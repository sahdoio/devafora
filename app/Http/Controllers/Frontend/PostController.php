<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Actions\Posts\GetPostBySlugAction;
use App\Actions\Posts\GetPublishedPostsAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostListResource;
use App\Http\Resources\PostResource;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(GetPublishedPostsAction $getPublishedPostsAction): Response
    {
        $posts = $getPublishedPostsAction->execute();

        return Inertia::render('Post/Index', [
            'posts' => PostListResource::collection($posts)->resolve(),
        ]);
    }

    public function show(string $slug, GetPostBySlugAction $getPostBySlugAction): Response
    {
        $post = $getPostBySlugAction->execute($slug);

        return Inertia::render('Post/Show', [
            'post' => PostResource::make($post)->resolve(),
        ]);
    }
}
