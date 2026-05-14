<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StockAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
      public function __construct(
        public $product,
        public $admin
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🚨 Stock Alert - ' . $this->product->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.stock-alert',
            with: [
                'product' => $this->product,
                'admin' => $this->admin,
            ]
        );
    }


    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
