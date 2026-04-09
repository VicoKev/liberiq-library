<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class GenericMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $viewName;

    public string $subjectMail;

    public array $data;

    /**
     * Create a new message instance.
     */
    public function __construct(string $viewName, string $subjectMail, array $data)
    {
        $this->viewName = $viewName;
        $this->subjectMail = $subjectMail;
        $this->data = $data;
    }

    /**
     * Get the message build.
     */
    public function build()
    {
        return $this->view($this->viewName)
            ->subject($this->subjectMail)
            ->with($this->data);
    }
}
