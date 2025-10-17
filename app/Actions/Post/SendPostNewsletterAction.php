<?php

declare(strict_types=1);

namespace App\Actions\Post;

use App\Mail\PostNewsletterMail;
use App\Models\NewsletterSubscription;
use App\Models\Post;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPostNewsletterAction
{
    public function execute(Post $post, bool $force = false): array
    {
        // Check if newsletter was already sent (unless forcing resend)
        if (!$force && $post->newsletter_sent_at !== null) {
            return [
                'success' => false,
                'message' => 'Newsletter already sent for this post.',
                'sent_count' => 0,
            ];
        }

        // Check if post is published
        if (!$post->is_published) {
            return [
                'success' => false,
                'message' => 'Post must be published before sending newsletter.',
                'sent_count' => 0,
            ];
        }

        // Get all active newsletter subscriptions
        $subscriptions = NewsletterSubscription::where('is_active', true)
            ->whereNull('unsubscribed_at')
            ->get();

        if ($subscriptions->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No active subscribers found.',
                'sent_count' => 0,
            ];
        }

        $sentCount = 0;
        $failedCount = 0;

        // Send emails to all subscribers
        foreach ($subscriptions as $subscription) {
            try {
                Mail::to($subscription->email)->send(new PostNewsletterMail($post));
                $sentCount++;
            } catch (\Exception $e) {
                $failedCount++;
                Log::error('Failed to send newsletter to ' . $subscription->email, [
                    'error' => $e->getMessage(),
                    'post_id' => $post->id,
                ]);
            }
        }

        // Mark newsletter as sent
        $post->newsletter_sent_at = now();
        $post->save();

        return [
            'success' => true,
            'message' => sprintf('Newsletter sent successfully to %d subscriber(s).', $sentCount) .
                        ($failedCount > 0 ? sprintf(' %d failed.', $failedCount) : ''),
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
        ];
    }
}
