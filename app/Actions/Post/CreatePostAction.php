<?php

namespace App\Actions\Post;

use App\Actions\Image\UploadImageAction;
use App\Models\Post;
use Illuminate\Support\Str;

class CreatePostAction
{
    public function __construct(
        private UploadImageAction $uploadImage
    ) {}

    public function execute(array $data): Post
    {
        // Handle image upload if present
        if (isset($data['image']) && is_object($data['image'])) {
            $data['image'] = $this->uploadImage->execute($data['image'], 'posts');
        }

        // Generate slug from title
        $data['slug'] = Str::slug($data['title']);

        // Set default published status if not provided
        if (!isset($data['is_published'])) {
            $data['is_published'] = false;
        }

        // Set published_at timestamp if publishing
        if ($data['is_published'] && !isset($data['published_at'])) {
            $data['published_at'] = now();
        }

        return Post::create($data);
    }
}
