<?php

namespace App\Internal;


use App\Seeders\UserSeeder;

class Seeder extends Base
{
    public static function seed()
    {
        UserSeeder::call();
    }
}