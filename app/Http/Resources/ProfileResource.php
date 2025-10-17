<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Check if photo is external URL or local path
        $photoUrl = null;
        if ($this->photo) {
            if (filter_var($this->photo, FILTER_VALIDATE_URL)) {
                // External URL - use as is
                $photoUrl = $this->photo;
            } else {
                // Local file - use Storage::url
                $photoUrl = Storage::url($this->photo);
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'bio' => $this->bio,
            'photo' => $photoUrl,
            'isActive' => $this->is_active,
            'linksCount' => $this->whenCounted('links'),
            'postsCount' => $this->whenCounted('posts'),
            'createdAt' => $this->created_at?->toISOString(),
        ];
    }
}
