<?php

declare(strict_types=1);

namespace App\Actions\Link;

use App\Data\LinkData;
use App\Models\Link;

class CreateLinkAction
{
    public function execute(LinkData $data): Link
    {
        // Set default order if not provided
        if ($data->order === null) {
            $data->order = Link::max('order') + 1;
        }

        return Link::create($data->toArray());
    }
}
