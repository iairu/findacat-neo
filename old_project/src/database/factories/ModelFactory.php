<?php

use App\Couple;
use App\Cat;
use App\CatMetadata;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Cat::class, function (Faker\Generator $faker) {
    $name = $faker->name;
    return [
        'id'         => $faker->uuid,
        'full_name'   => $name,
        'gender_id'  => rand(1, 2),
    ];
});

$factory->state(Cat::class, 'male', function (Faker\Generator $faker) {
    return ['gender_id' => 1];
});

$factory->state(Cat::class, 'female', function (Faker\Generator $faker) {
    return ['gender_id' => 2];
});

$factory->define(Couple::class, function (Faker\Generator $faker) {
    return [
        'id'         => $faker->uuid,
        'husband_id' => function () {
            return factory(Cat::class)->states('male')->create()->id;
        },
        'wife_id'    => function () {
            return factory(Cat::class)->states('female')->create()->id;
        },
    ];
});

$factory->define(CatMetadata::class, function (Faker\Generator $faker) {
    return [
        'id'      => $faker->uuid,
        'cat_id' => function () {
            return factory(Cat::class)->create()->id;
        },
        'key'     => $faker->name,
        'value'   => $faker->sentence,
    ];
});
