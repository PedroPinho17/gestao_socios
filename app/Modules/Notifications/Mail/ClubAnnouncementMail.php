<?php

namespace App\Modules\Notifications\Mail;

use App\Models\ClubSetting;
use App\Modules\Members\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClubAnnouncementMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Member $member,
        public string $assunto,
        public string $corpoHtml,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->assunto,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.club-announcement',
            with: [
                'member' => $this->member,
                'corpoHtml' => $this->corpoHtml,
                'settings' => ClubSetting::current(),
            ],
        );
    }
}
