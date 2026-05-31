<?php

declare(strict_types=1);

namespace App\Actions\Post;

use App\Mail\PostNewsletterMail;
use App\Models\NewsletterSubscription;
use App\Support\Posts\MarkdownPost;
use App\Support\Posts\PostRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPostNewsletterAction
{
    public function __construct(
        private readonly PostRepository $posts,
    ) {}

    /**
     * @return array{success: bool, message: string, sent_count: int, failed_count?: int}
     */
    public function execute(MarkdownPost $post, bool $force = false): array
    {
        // Check if newsletter was already sent (unless forcing resend).
        if (! $force && $post->newsletter_sent_at !== null) {
            return [
                'success' => false,
                'message' => 'Newsletter já foi enviada para este post.',
                'sent_count' => 0,
            ];
        }

        // Check if post is published.
        if (! $post->isLive()) {
            return [
                'success' => false,
                'message' => 'O post precisa estar publicado antes de enviar a newsletter.',
                'sent_count' => 0,
            ];
        }

        $subscriptions = NewsletterSubscription::where('is_active', true)
            ->whereNull('unsubscribed_at')
            ->get();

        if ($subscriptions->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Nenhum inscrito ativo encontrado.',
                'sent_count' => 0,
            ];
        }

        $sentCount = 0;
        $failedCount = 0;

        foreach ($subscriptions as $subscription) {
            try {
                Mail::to($subscription->email)->send(new PostNewsletterMail($post));
                $sentCount++;
            } catch (\Exception $e) {
                $failedCount++;
                Log::error('Failed to send newsletter to '.$subscription->email, [
                    'error' => $e->getMessage(),
                    'post_slug' => $post->slug,
                ]);
            }
        }

        // Record the send timestamp in the post's front matter.
        $this->posts->markNewsletterSent($post);

        return [
            'success' => true,
            'message' => sprintf('Newsletter enviada para %d inscrito(s).', $sentCount).
                        ($failedCount > 0 ? sprintf(' %d falharam.', $failedCount) : ''),
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
        ];
    }
}
