<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 20.03.2019
 * Time: 14:12
 */

namespace Tests\traits;


use App\User;

trait TestTrait
{
    public static $loaded = false;

    public function loadData()
    {
        if(!self::$loaded) {
            resolve(\TestSeeder::class)->run();
            self::$loaded = true;
        }
    }

    public function getUser(string $name): User
    {
       return User::where(['name' => $name])->first();
    }
}
