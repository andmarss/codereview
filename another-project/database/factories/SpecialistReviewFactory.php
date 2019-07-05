<?php

use Faker\Generator as Faker;
use \App\SpecialistReview;
use \App\User;

$factory->define(App\SpecialistReview::class, function (Faker $faker) {
    return [
        'specialist_id' => function() {
            return User::getDoctors()->random()->id;
        },
        'author' => $faker->firstName . ' ' . $faker->lastName,
        'rate' => rand(SpecialistReview::RATING_ONE, SpecialistReview::RATING_FIVE),
        'text' => $faker->realText(rand(50, 250))
    ];
});
