<?php

namespace App\Actions\Post;

use App\Actions\Image\UploadImageAction;
use App\Models\Post;
use Illuminate\Support\Str;

class UpdatePostAction
{
    public function __construct(
        private UploadImageAction $uploadImage
    ) {}

    public function execute(Post $post, array $data): Post
    {
        // Handle image upload if present
        if (isset($data['image']) && is_object($data['image'])) {
            // Delete old image if exists
            $this->uploadImage->deleteIfExists($post->image);

            // Upload new image
            $data['image'] = $this->uploadImage->execute($data['image'], 'posts');
        } elseif (!isset($data['image'])) {
            // Keep existing image if not provided
            unset($data['image']);
        }

        // Regenerate slug from title
        $data['slug'] = Str::slug($data['title']);

        // Handle publishing/unpublishing
        if (isset($data['is_published'])) {
            // If publishing for the first time
            if ($data['is_published'] && !$post->is_published) {
                $data['published_at'] = now();
            }

            // If unpublishing
            if (!$data['is_published'] && $post->is_published) {
                $data['published_at'] = null;
            }
        }

        $post->update($data);

        return $post->fresh();
    }
}
