<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class DataChangeConfirm
 * @package App\Mail
 *
 * @property int $id
 */

class DataChangeConfirm extends Mailable
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
            ->subject('Подтверждение изменения данных')
            ->markdown('nova::auth.emails.data-change.confirm')
            ->with([
                'url' => url('/information/specialist/' . $this->id)
            ]);
    }
}
