<?php

declare(strict_types=1);

namespace App\Actions\Post;

use App\Actions\Image\UploadImageAction;
use App\Data\PostData;
use App\Models\Post;
use Illuminate\Support\Str;

class UpdatePostAction
{
    public function __construct(
        private readonly UploadImageAction $uploadImageAction
    ) {}

    public function execute(Post $post, PostData $data): Post
    {
        $attributes = $data->toArray();

        // Handle image upload if present
        if ($data->image && is_object($data->image)) {
            // Delete old image if exists and it's not a URL
            if ($post->image && !filter_var($post->image, FILTER_VALIDATE_URL)) {
                $this->uploadImageAction->deleteIfExists($post->image);
            }

            // Upload new image
            $attributes['image'] = $this->uploadImageAction->execute($data->image, 'posts');
        } elseif (!$data->image) {
            // Keep existing image if not provided
            unset($attributes['image']);
        }

        // Regenerate slug from title
        $attributes['slug'] = Str::slug($data->title);

        // Handle publishing/unpublishing
        // If publishing for the first time
        if ($data->is_published && !$post->is_published) {
            $attributes['published_at'] = now();
        }

        // If unpublishing
        if (!$data->is_published && $post->is_published) {
            $attributes['published_at'] = null;
        }

        $post->update($attributes);

        return $post->fresh();
    }
}
