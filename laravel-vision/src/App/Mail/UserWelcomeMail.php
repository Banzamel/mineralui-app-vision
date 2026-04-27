<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent when an admin creates a new account in /users.
 * Carries the sign-in credentials — the recipient logs in with them and is expected to change
 * the password via /users → edit profile (no magic links, no activation step).
 */
class UserWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param string $userName recipient's display name
     * @param string $userEmail recipient's email — also their login
     * @param string $password initial password set by the admin
     * @param string $appName product name shown in the header/subject
     * @param string|null $appUrl product URL shown as a clickable footer link
     */
    public function __construct(
        public string $userName,
        public string $userEmail,
        public string $password,
        public string $appName = 'Vision',
        public ?string $appUrl = null,
    ) {}

    /**
     * @return Envelope subject line — kept short for inbox previews.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->appName} — welcome, your account is ready",
        );
    }

    /**
     * @return Content blade template used to render the HTML body
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.user-welcome',
        );
    }
}
