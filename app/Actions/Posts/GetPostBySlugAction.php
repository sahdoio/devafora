<?php

namespace App\Actions\Posts;

use App\Models\Post;

class GetPostBySlugAction
{
    public function execute(string $slug): Post
    {
        return Post::query()
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();
    }
}
