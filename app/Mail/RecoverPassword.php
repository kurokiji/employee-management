<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecoverPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $title;
    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $title, $data)
    {
        $this->subject = $subject;
        $this->data = $data;
        $this->title = $title;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)->view('notification');
    }
}
