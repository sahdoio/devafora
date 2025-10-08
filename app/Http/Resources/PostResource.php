<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Handle both external URLs and local storage paths for image
        $imageUrl = null;
        if ($this->image) {
            if (filter_var($this->image, FILTER_VALIDATE_URL)) {
                $imageUrl = $this->image; // External URL
            } else {
                $imageUrl = Storage::url($this->image); // Local storage
            }
        }

        return [
            'id' => $this->id,
            'profile_id' => $this->profile_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'author' => $this->author,
            'image' => $imageUrl,
            'read_time' => $this->read_time,
            'tags' => $this->tags ?? [],
            'published_at' => $this->published_at?->toISOString(),
            'is_published' => $this->is_published,
            'profile' => $this->whenLoaded('profile', function () {
                return [
                    'id' => $this->profile->id,
                    'name' => $this->profile->name,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
