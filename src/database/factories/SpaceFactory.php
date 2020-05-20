<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Space;
use Faker\Generator as Faker;

$factory->define(Space::class, function (Faker $faker) {
    return [
        'name'          => '病院',
        'effect_id'     => config('const.effect_change_status'),
        'effect_num'    => config('const.piece_status_health')
    ];
});
