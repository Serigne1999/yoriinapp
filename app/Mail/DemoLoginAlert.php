<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DemoLoginAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $demo_type;

    public function __construct($user, $demo_type)
    {
        $this->user = $user;
        $this->demo_type = $demo_type;
    }

    public function build()
    {
        return $this->subject('🧪 Connexion à la démo - '.$this->demo_type)
                    ->view('emails.demo_login_alert');
    }
}
