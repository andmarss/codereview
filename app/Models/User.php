<?php

namespace App\Models;

use App\DealHandlers\Clients\Client;
use App\Notifications\ResetPasswordMailNotification;
use Crabler\Common\Models\Traits\Parameterizable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Class User
 * @package App\Models
 *
 * @property string  $email
 * @property string  $name
 * @property integer $bitrix_contact_id
 * @property array   $params
 *
 * @property Account $account
 * @property ClientType $client_type
 * @property Client $client
 */
class User extends Authenticatable
{
    use Notifiable, Parameterizable;

    protected $c;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];
    /**
     * @var array
     */
    protected $casts = [];

    /**
     * Возвращает отношение к {@see Account::class}
     *
     * @return HasOne
     */
    public function account(): HasOne
    {
        return $this->hasOne(Account::class, 'user_id', 'id');
    }

    /**
     * Возвращает отношение к {@see ClientType::class}
     *
     * @return HasOne
     */
    public function client_type(): HasOne
    {
       return $this->hasOne(ClientType::class, 'id', 'client_type_id');
    }

    /**
     * @return Client
     */
    public function getClientAttribute(): Client
    {
        if(!is_null($this->c) && $this->c->getType() === $this->client_type->id) {
            return $this->c;
        }

        $class = "App\\DealHandlers\\Clients\\" . $this->client_type->id;

        return $this->c = new $class($this);
    }

    /**
     * Получить счет пользователя
     *
     * @return Account
     */
    public function getAccount() :Account
    {
        if ($this->account) {
            return $this->account;
        }

        $acc = new Account();
        $acc->user_id = $this->id;
        $acc->save();

        $this->load('account');

        return $this->account;
    }
    /**
     * @return string
     * @throws \RuntimeException
     */
    public function getBdUrl()
    {
        if (empty($this->bd_token)) {
            $this->bd_token = str_random(32);
            $this->save();
        }

        return route('nova.api.check-token', ['bd_token' => $this->bd_token]);
    }
    /**
     * Возвращает количество оплаченных заказов
     * @return int
     */
    public function getDealCount(): int
    {
       return isset($this->params['statistic']) ? (int) $this->params['statistic']['deal_count'] : 0;
    }
    /**
     * Супер-пользователь или обычный пользователь
     * @return bool
     */
    public function isSuperUser(): bool
    {
       return isset($this->params['is_su']) ? $this->params['is_su'] : false;
    }

    /**
     * Отправляет уведомление о сбросе пароля
     *
     * @param string $token
     */
    public function sendPasswordResetNotification($token)
    {
       $this->notify(new ResetPasswordMailNotification($token));
    }
}
