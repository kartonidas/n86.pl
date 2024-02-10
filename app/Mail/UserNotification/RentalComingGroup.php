<?php

namespace App\Mail\UserNotification;

use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Libraries\Helper;
use App\Models\ConfigNotification;

class RentalComingGroup extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public $data, public ConfigNotification $notification, public DateTime $comingDate)
    {
    }
    
    public function getTitle()
    {
        $totalComing = count($this->data);
        
        return sprintf("Przypomnienie: za %d %s %s się %d %s",
            $this->notification["days"],
            Helper::plurals($this->notification["days"], "dzień", "dni", "dni"),
            Helper::plurals($totalComing, "rozpocznie się", "rozpoczną się", "rozpocznie się"),
            $totalComing,
            Helper::plurals($totalComing, "wynajem", "wynajmy", "wynajmów")
        );
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->getTitle(),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $view = 'emails.' . $this->locale . '.user-notification.rental-coming-group';
        if(!view()->exists($view))
            $view = 'emails.'.config("api.default_language").'.user-notification.rental-coming-group';
        
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
