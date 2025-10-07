<?php

namespace App\Actions\Newsletter;

use App\Models\NewsletterSubscription;
use Illuminate\Support\Facades\DB;

class SubscribeToNewsletterAction
{
    public function __construct(
        private SendWelcomeEmailAction $sendWelcomeEmail
    ) {}

    public function execute(string $email, ?string $name = null): NewsletterSubscription
    {
        return DB::transaction(function () use ($email, $name) {
            $subscription = NewsletterSubscription::firstOrNew(['email' => $email]);

            if ($subscription->exists && $subscription->isSubscribed()) {
                return $subscription;
            }

            $isNewSubscription = !$subscription->exists;

            $subscription->name = $name ?? $subscription->name;
            $subscription->subscribe();

            if ($isNewSubscription) {
                $this->sendWelcomeEmail->execute($subscription);
            }

            return $subscription;
        });
    }
}
