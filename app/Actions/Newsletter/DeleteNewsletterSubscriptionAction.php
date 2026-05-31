<?php

declare(strict_types=1);

namespace App\Actions\Newsletter;

use App\Models\NewsletterSubscription;

class DeleteNewsletterSubscriptionAction
{
    public function execute(NewsletterSubscription $newsletterSubscription): bool
    {
        // Eloquent's delete() returns bool|null (null when the model no longer
        // exists, e.g. a double delete). Cast so the declared bool return holds.
        return (bool) $newsletterSubscription->delete();
    }
}
