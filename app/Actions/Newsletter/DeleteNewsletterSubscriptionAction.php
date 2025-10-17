<?php

namespace App\Actions\Newsletter;

use App\Models\NewsletterSubscription;

class DeleteNewsletterSubscriptionAction
{
    public function execute(NewsletterSubscription $subscription): bool
    {
        return $subscription->delete();
    }
}