<?php

namespace App\Http\Controllers\Frontend;

use App\Actions\Posts\GetPostBySlugAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function show(string $slug, GetPostBySlugAction $getPost): Response
    {
        $post = $getPost->execute($slug);

        return Inertia::render('Post/Show', [
            'post' => PostResource::make($post)->resolve(),
        ]);
    }
}
