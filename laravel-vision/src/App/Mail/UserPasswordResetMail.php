<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent after an admin clicks "Reset password" on a user row in /users.
 * Carries the freshly generated temporary password — the recipient logs in with it
 * and is expected to change it via /users → edit profile (no magic links).
 */
class UserPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param string $userName recipient's display name
     * @param string $userEmail recipient's email address (used for greeting only)
     * @param string $temporaryPassword newly generated password
     * @param string $appName product name shown in the header/subject
     * @param string|null $appUrl product URL shown as a clickable footer link
     */
    public function __construct(
        public string $userName,
        public string $userEmail,
        public string $temporaryPassword,
        public string $appName = 'Vision',
        public ?string $appUrl = null,
    ) {}

    /**
     * @return Envelope subject line — kept short for inbox previews.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->appName} — your new password",
        );
    }

    /**
     * @return Content blade template used to render the HTML body
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.user-password-reset',
        );
    }
}
