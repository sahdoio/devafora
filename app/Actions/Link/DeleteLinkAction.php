<?php

declare(strict_types=1);

namespace App\Actions\Link;

use App\Models\Link;

class DeleteLinkAction
{
    public function execute(Link $link): bool
    {
        return $link->delete();
    }
}
