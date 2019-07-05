<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class RegisterCancel
 * @package App\Mail
 *
 * @property int $id
 * @property string $cause
 */

class RegisterCancel extends Mailable
{
    use Queueable, SerializesModels;

    public $id;
    public $cause;

    /**
     * RegisterCancel constructor.
     * @param int $id
     * @param string $cause
     */
    public function __construct(int $id, string $cause)
    {
        $this->id = $id;
        $this->cause = $cause;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Отказ в подтверждении регистрации')
            ->markdown('nova::auth.emails.register.cancel')
            ->with([
                'url' => url('/information/specialist/' . $this->id),
                'cause' => $this->cause
            ]);
    }
}
