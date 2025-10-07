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
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'author' => $this->author,
            'image' => $imageUrl,
            'readTime' => $this->read_time ? "{$this->read_time} min" : null,
            'readTimeMinutes' => $this->read_time,
            'tags' => $this->tags ?? [],
            'publishedAt' => $this->published_at?->format('d/m/Y'),
            'publishedAtFull' => $this->published_at?->toISOString(),
            'isPublished' => $this->is_published,
            'createdAt' => $this->created_at?->toISOString(),
        ];
    }
}
