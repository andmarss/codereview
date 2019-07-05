<?php

use Faker\Generator as Faker;
use App\User;

$factory->define(\App\Specialist::class, function (Faker $faker) {
    return [
        'id' => $faker->unique()->randomNumber(7),
        // получаем id пользователя, у которого роль - пользователь
        // и у которого еще нет привязки к специалисту
        'user_id' => User::notDoctors()->get()->random()->id,
        'firstname' => $faker->firstName,
        'secondname' => $faker->name,
        'lastname' => $faker->lastName,
        'image' => $faker->imageUrl(300, 300),
        'description' => $faker->realText(rand(50, 250))
    ];
});
