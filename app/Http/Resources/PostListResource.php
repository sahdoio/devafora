<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PostListResource extends JsonResource
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
            'author' => $this->author,
            'image' => $imageUrl,
            'readTime' => $this->read_time ? $this->read_time . ' min' : null,
            'tags' => $this->tags ?? [],
            'publishedAt' => $this->published_at?->format('d/m/Y'),
        ];
    }
}
