<?php

namespace App\Actions\Posts;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class GetPublishedPostsAction
{
    public function execute(?int $profileId = null): Collection
    {
        $query = Post::query()
            ->published()
            ->latest();

        if ($profileId) {
            $query->where('profile_id', $profileId);
        }

        return $query->get();
    }
}
