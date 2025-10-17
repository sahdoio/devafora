<?php

declare(strict_types=1);

namespace App\Actions\Newsletter;

use App\Models\NewsletterSubscription;

class ToggleNewsletterStatusAction
{
    public function execute(NewsletterSubscription $newsletterSubscription): NewsletterSubscription
    {
        if ($newsletterSubscription->isSubscribed()) {
            $newsletterSubscription->unsubscribe();
        } else {
            $newsletterSubscription->resubscribe();
        }

        return $newsletterSubscription;
    }
}
