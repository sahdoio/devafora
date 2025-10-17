<?php

declare(strict_types=1);

namespace App\Actions\Newsletter;

use App\Models\NewsletterSubscription;

class DeleteNewsletterSubscriptionAction
{
    public function execute(NewsletterSubscription $newsletterSubscription): bool
    {
        return $newsletterSubscription->delete();
    }
}
