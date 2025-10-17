<?php

declare(strict_types=1);

namespace App\Actions\Newsletter;

use App\Mail\NewsletterWelcomeMail;
use App\Models\NewsletterSubscription;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmailAction
{
    public function execute(NewsletterSubscription $newsletterSubscription): void
    {
        Mail::to($newsletterSubscription->email)
            ->send(new NewsletterWelcomeMail($newsletterSubscription));
    }
}
