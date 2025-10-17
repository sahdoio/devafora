<?php

namespace App\Actions\Newsletter;

use App\Models\NewsletterSubscription;

class ToggleNewsletterStatusAction
{
    public function execute(NewsletterSubscription $subscription): NewsletterSubscription
    {
        if ($subscription->isSubscribed()) {
            $subscription->unsubscribe();
        } else {
            $subscription->resubscribe();
        }

        return $subscription;
    }
}