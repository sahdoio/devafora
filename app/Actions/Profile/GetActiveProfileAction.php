<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\Profile;

class GetActiveProfileAction
{
    public function execute(): ?Profile
    {
        return Profile::query()
            ->active()
            ->first();
    }
}
