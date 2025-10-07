<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PostListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'author' => $this->author,
            'image' => $this->image ? Storage::url($this->image) : null,
            'readTime' => $this->read_time ? "{$this->read_time} min" : null,
            'tags' => $this->tags ?? [],
            'publishedAt' => $this->published_at?->format('d/m/Y'),
        ];
    }
}
