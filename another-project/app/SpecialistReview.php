<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class SpecialistReview
 * @package App
 *
 * @property int $id
 * @property int $specialist_id,
 * @property string $author
 * @property string $text
 * @property int $rate
 * @property string $answer_text
 * @property string $answered_at
 *
 * @property Specialist $specialist
 */

class SpecialistReview extends Model
{
    const RATING_ONE = 1;
    const RATING_TWO = 2;
    const RATING_THREE = 3;
    const RATING_FOUR = 4;
    const RATING_FIVE = 5;
    /**
     * @var string $table
     */
    protected $table = 'specialist_reviews';
    /**
     * @var array $fillable
     */
    protected $fillable = [
        'specialist_id',
        'author',
        'text',
        'rate',
        'answer_text',
        'answered_at'
    ];
    /**
     * @var array $dates
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'answered_at'
    ];
    /**
     * @return HasOne
     */
    public function specialist(): HasOne
    {
        return $this->hasOne(Specialist::class, 'id','specialist_id');
    }
}
