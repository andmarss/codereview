<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use \Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Specialist
 * @package App
 *
 * @property int $id
 * @property int $user_id
 * @property string $firstname
 * @property string $secondname
 * @property string $lastname
 * @property string $description
 * @property string $image
 * @property boolean $is_deletion_requested
 *
 * @property User $user
 * @property SpecialistReview $reviews
 */

class Specialist extends Model
{
    /**
     * @var string $table
     */
    protected $table = 'specialists';
    /**
     * @var array $fillable
     */
    protected $fillable = [
        'id',
        'user_id',
        'firstname',
        'secondname',
        'lastname',
        'description',
        'image',
        'is_deletion_requested'
    ];
    /**
     * @var array $dates
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];
    /**
     * @var array $casts
     */
    protected $casts = ['id' => 'integer'];
    /**
     * @var string $keyType
     */
    protected $keyType = 'integer';
    /**
     * @var bool $incrementing
     */
    public $incrementing = false;
    /**
     * @var string $primaryKey
     */
    protected $primaryKey = 'id';
    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
       return $this->hasOne(User::class, 'id', 'user_id');
    }
    /**
     * @return HasMany
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(SpecialistReview::class, 'specialist_id', 'id');
    }
    /**
     * @return UserFile|null
     */
    public function image()
    {
        if(is_null($this->image)) return null;

       return UserFile::where('path', 'like', '%' . UserFile::getPath($this->user_id) . '%')->first();
    }
}
