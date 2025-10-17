<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LinkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'profile_id' => $this->profile_id,
            'title' => $this->title,
            'description' => $this->description,
            'url' => $this->url,
            'icon' => $this->icon,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'profile' => $this->whenLoaded('profile', fn(): array => [
                'id' => $this->profile->id,
                'name' => $this->profile->name,
            ]),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
