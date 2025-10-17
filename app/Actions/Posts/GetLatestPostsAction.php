<?php

declare(strict_types=1);

namespace App\Actions\Posts;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class GetLatestPostsAction
{
    public function execute(int $limit = 3, ?int $profileId = null): Collection
    {
        $query = Post::query()
            ->published()
            ->latest();

        if ($profileId !== null && $profileId !== 0) {
            $query->where('profile_id', $profileId);
        }

        return $query->limit($limit)->get();
    }
}
