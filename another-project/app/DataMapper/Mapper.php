<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 29.03.2019
 * Time: 22:48
 */

namespace App\DataMapper;


use App\User;
use Illuminate\Support\Collection;

abstract class Mapper
{
    abstract static public function map(User $user): Collection;
}
