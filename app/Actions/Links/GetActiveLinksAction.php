<?php

declare(strict_types=1);

namespace App\Actions\Links;

use App\Models\Link;
use Illuminate\Database\Eloquent\Collection;

class GetActiveLinksAction
{
    public function execute(?int $profileId = null): Collection
    {
        $query = Link::query()
            ->active()
            ->ordered();

        if ($profileId !== null && $profileId !== 0) {
            $query->where('profile_id', $profileId);
        }

        return $query->get();
    }
}
