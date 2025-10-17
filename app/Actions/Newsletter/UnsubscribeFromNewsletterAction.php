<?php

declare(strict_types=1);

namespace App\Actions\Newsletter;

use App\Models\NewsletterSubscription;

class UnsubscribeFromNewsletterAction
{
    public function execute(string $email): ?NewsletterSubscription
    {
        $subscription = NewsletterSubscription::where('email', $email)->first();

        if (!$subscription) {
            return null;
        }

        $subscription->unsubscribe();

        return $subscription;
    }
}
