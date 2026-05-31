<?php

declare(strict_types=1);

namespace App\Mail;

use App\Support\Posts\MarkdownPost;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PostNewsletterMail extends Mailable
{
    use Queueable;

    public function __construct(
        public MarkdownPost $post
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->post->title,
        );
    }

    public function content(): Content
    {
        $imageUrl = $this->post->imageUrl();
        if ($imageUrl !== null && ! preg_match('#^https?://#i', $imageUrl)) {
            // Make app-relative paths (e.g. /posts/{slug}/assets/...) absolute.
            $imageUrl = url($imageUrl);
        }

        return new Content(
            view: 'emails.post-newsletter',
            with: [
                'post' => $this->post,
                'url' => route('posts.show', $this->post->slug),
                'imageUrl' => $imageUrl,
            ],
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
