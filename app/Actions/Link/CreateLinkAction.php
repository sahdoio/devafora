<?php

namespace App\Actions\Link;

use App\Models\Link;

class CreateLinkAction
{
    public function execute(array $data): Link
    {
        // Set default order if not provided
        if (!isset($data['order'])) {
            $data['order'] = Link::max('order') + 1;
        }

        // Set default active status if not provided
        if (!isset($data['is_active'])) {
            $data['is_active'] = true;
        }

        return Link::create($data);
    }
}
