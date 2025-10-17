<?php

declare(strict_types=1);

namespace App\Actions\Link;

use App\Data\LinkData;
use App\Models\Link;

class UpdateLinkAction
{
    public function execute(Link $link, LinkData $data): Link
    {
        $link->update($data->toArray());

        return $link->fresh();
    }
}
