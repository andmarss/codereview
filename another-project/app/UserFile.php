<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class UserFile
 * @package App
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $type
 * @property string $path
 * @property integer $status
 * @property string $old_name
 *
 * @property User $user
 */

class UserFile extends Model
{
    // файла нет
    const STATUS_NO_FILE = 0;
    // файл загружен
    const STATUS_UPLOADED = 1;

    protected $fillable = [
        'user_id', 'name', 'type', 'path', 'status', 'old_name'
    ];

    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
       return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * @param int $id
     * @return string
     */
    public static function getPath(int $id): string
    {
       $dir = public_path() . '/img/uploads/userfiles/' . $id;

       if(!is_dir($dir)) {
           mkdir($dir, 0755, true);
       }

       return '/img/uploads/userfiles/' . $id;
    }
}
