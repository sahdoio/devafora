<?php

declare(strict_types=1);

namespace App\Actions\Post;

use App\Actions\Image\UploadImageAction;
use App\Data\PostData;
use App\Models\Post;
use Illuminate\Support\Str;

class CreatePostAction
{
    public function __construct(
        private readonly UploadImageAction $uploadImageAction
    ) {}

    public function execute(PostData $data): Post
    {
        $attributes = $data->toArray();

        // Handle image upload if present
        if ($data->image && is_object($data->image)) {
            $attributes['image'] = $this->uploadImageAction->execute($data->image, 'posts');
        }

        // Generate slug from title
        $attributes['slug'] = Str::slug($data->title);

        // Set published_at timestamp if publishing
        if ($data->is_published && !isset($attributes['published_at'])) {
            $attributes['published_at'] = now();
        }

        return Post::create($attributes);
    }
}
