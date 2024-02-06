<?php

namespace App\Mail\UserNotification;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Libraries\Helper;
use App\Models\ConfigNotification;

class RentalEndingSingle extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public $data, public ConfigNotification $notification)
    {
    }
    
    public function getTitle()
    {
        return __('Ending rental: ');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->getTitle() . $this->data["rental"]["full_number"] . ", " . $this->data["item"]["name"]
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $view = 'emails.' . $this->locale . '.user-notification.rental-ending-single';
        if(!view()->exists($view))
            $view = 'emails.'.config("api.default_language").'.user-notification.rental-ending-single';
        
        return new Content(
            view: $view,
            with: [
                "title" => $this->getTitle(),
            ]
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
