<?php

namespace App\Modules\Notifications\Mail;

use App\Models\ClubSetting;
use App\Modules\Members\Models\Member;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuotaReminderMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Member $member,
        public Carbon $vencimento,
        public int $dias = 0,
    ) {}

    public function envelope(): Envelope
    {
        $clube = ClubSetting::current()->nome_clube;

        return new Envelope(
            subject: 'Lembrete: quota a vencer · '.$clube,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quota-reminder',
            with: [
                'member' => $this->member,
                'vencimento' => $this->vencimento,
                'dias' => $this->dias,
                'settings' => ClubSetting::current(),
            ],
        );
    }
}
