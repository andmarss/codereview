<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class VerifyUser
 * @package App
 *
 * @property integer $user_id
 * @property string $token
 *
 * @property User $user
 */

class VerifyUser extends Model
{
    protected $fillable = [
        'user_id',
        'token'
    ];

    public function user(): belongsTo
    {
       return $this->belongsTo(User::class);
    }
}
