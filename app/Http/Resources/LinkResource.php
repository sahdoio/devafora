<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LinkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'url' => $this->url,
            'icon' => $this->icon,
            'order' => $this->order,
            'isActive' => $this->is_active,
            'createdAt' => $this->created_at?->toISOString(),
        ];
    }
}
