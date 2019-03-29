<?php

namespace App\Models;

use Crabler\Common\PaymentGateways\GatewayPaymentStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * Class Payment
 * @package App\Models
 *
 * @property integer $id
 * @property integer $account_id
 * @property float   $sum
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 * @property integer $type
 * @property integer $deal_id
 * @property string  $description
 * @property integer $status
 * @property string  $gateway_id
 * @property Deal $deal;
 *
 */
class Payment extends Model
{
    use SoftDeletes;

    //все пополнения баланса +к остатку на счете
    const TYPE_DEBET  = 1;
    //все списания со счета -от остатка на счете
    const TYPE_CREDIT = 2;

    //Создан, но деньги еще не списаны с (не зачислены на) баланс
    const STATUS_CREATED = 1;
    //Платеж осуществлен, деньги учтены на балансе
    const STATUS_PAID = 2;

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var string
     */
    protected $table = 'payments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * @var array
     */
    protected $casts = [];

    /**
     * @var array
     */
    protected $dates = [];

    /**
     * @return HasOne
     */
    public function account(): HasOne
    {
        return $this->hasOne(Account::class, 'id', 'account_id');
    }

    /**
     * @return HasOne
     */
    public function deal(): HasOne
    {
        return $this->hasOne(Deal::class, 'id', 'deal_id');
    }

    /**
     * Подтверждаем платеж, либо, если он уже подтвержден - ничего не делаем
     *
     */
    public function confirm()
    {
        if( $this->isConfirmed()) {
            return;
        }

        $this->status = self::STATUS_PAID;
        $this->save();

        $this->account->balance += $this->sum;
        $this->account->save();

        $this->load('account');
    }

    /**
     * Подтвержден платеж или нет
     *
     * @return bool
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Подтвержден платеж или нет
     *
     * @return bool
     */
    public function isWaitingComfirmation(): bool
    {
        return $this->gateway_status === GatewayPaymentStatus::WAITING_FOR_CAPTURE;
    }

    /**
     * @param string $status
     * @throws \Exception
     */

    public function updateGatewayStatus(string $status)
    {
        $this->gateway_status = $status;
        $this->save();

        if ($this->gateway_status === GatewayPaymentStatus::SUCCEEDED) {
            $this->confirm();
            return;
        }

        if (empty($this->gateway_id) || $this->gateway_status === GatewayPaymentStatus::CANCELED) {
            $this->delete();
        }
    }
}
