<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MagicLinkMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $token;
    public $site;
    public $company;
    public $isInvitation = false;
    public $siteName = null;
    public $companyName = null;

    /**
     * Create a new message instance.
     */
    public function __construct(string $token, $entity = null, $isInvitation = false)
    {
        $this->token = $token;
        $this->isInvitation = $isInvitation;

        if ($entity instanceof \App\Models\Site) {
            $this->site = $entity;
            $this->siteName = $entity->name;
        } elseif ($entity instanceof \App\Models\Company) {
            $this->company = $entity;
            $this->companyName = $entity->name;
        } else {
            // Legacy support - assume it's a site
            $this->site = $entity;
            $this->siteName = $entity ? $entity->name : null;
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Sign in to ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.magic-link',
            with: [
                'token' => $this->token,
                'site' => $this->site,
                'company' => $this->company,
                'isInvitation' => $this->isInvitation,
                'siteName' => $this->siteName,
                'companyName' => $this->companyName,
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
