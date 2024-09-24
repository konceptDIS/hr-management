<?php

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

$factory->define(App\User::class, function (Faker\Generator $faker) {
    $first_name = $faker->firstName;
    $last_name = $faker->lastName;
    $username = $first_name. $last_name;
    return [
        'name' => $first_name . " " . $last_name,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'username' => $username,//$faker->safeEmail,
        'password' => bcrypt(str_random(10)),
        'remember_token' => str_random(10),
        'department' => 'ICT',
        'section' => 'Testing',
        'designation' => 'Test Engineer',
        'area_office' => 'HQ',
        'region' => 'FCT',
        'resumption_date' => $faker->datetime($max = "now"),
        'staff_id' => '123456',
        'gender' => 'M',
        'phone_number' => '09098765432',
    ];
});

$factory->defineAs(App\User::class, 'director', function (Faker\Generator $faker) use ($factory) {
    $baseUser = $factory->raw('App\User');
    return array_merge($baseUser, ['is_contract_staff' => true]);
});

$factory->defineAs(App\User::class, 'newHire', function (Faker\Generator $faker) use ($factory) {
    $baseUser = $factory->raw('App\User');
    return array_merge($baseUser, [
        'resumption_date' => $faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now')
    ]);
});

$factory->define(App\Task::class, function (Faker\Generator $faker) {
    return [
        'name' => str_random(10),
    ];
});

$factory->define(App\LeaveRequest::class, function (Faker\Generator $faker) {
    return [
        'name' => str_random(10),
    ];
});

