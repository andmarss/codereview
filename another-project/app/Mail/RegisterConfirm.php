<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class RegisterConfirm
 * @package App\Mail
 *
 * @property int $id
 */

class RegisterConfirm extends Mailable
{
    use Queueable, SerializesModels;

    public $id;

    /**
     * RegisterConfirm constructor.
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Подтверждение регистрации пользователя')
            ->markdown('nova::auth.emails.register.confirm')
            ->with([
                'url' => url('/information/specialist/' . $this->id)
            ]);
    }
}
