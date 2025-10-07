<?php

namespace App\Actions\Newsletter;

use App\Mail\NewsletterWelcomeMail;
use App\Models\NewsletterSubscription;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmailAction
{
    public function execute(NewsletterSubscription $subscription): void
    {
        Mail::to($subscription->email)
            ->send(new NewsletterWelcomeMail($subscription));
    }
}
