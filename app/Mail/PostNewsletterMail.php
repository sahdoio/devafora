<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PostNewsletterMail extends Mailable
{
    use Queueable;
    use SerializesModels;
    /**
     * Create a new message instance.
     */
    public function __construct(
        public Post $post
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->post->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Handle both external URLs and local storage paths for image
        $imageUrl = null;
        if ($this->post->image) {
            if (filter_var($this->post->image, FILTER_VALIDATE_URL)) {
                $imageUrl = $this->post->image; // External URL
            } else {
                // Generate full absolute URL for local storage
                $imageUrl = asset('storage/' . $this->post->image);
            }
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
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
