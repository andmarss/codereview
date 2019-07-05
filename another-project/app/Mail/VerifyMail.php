<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class VerifyMail
 * @package App\Mail
 *
 * @property User $user
 */

class VerifyMail extends Mailable
{
    use Queueable, SerializesModels;
    /**
     * @var User $user
     */
    public $user;

    /**
     * VerifyMail constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Регистрация нового пользователя')
            ->markdown('nova::auth.emails.verify')
            ->with([
                'url' => url(route('verify', ['token' => $this->user->verify_user->token]))
            ]);
    }
}
