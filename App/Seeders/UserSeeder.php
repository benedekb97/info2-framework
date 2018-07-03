<?php

namespace App\Seeders;

use App\Internal\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
     public static function call()
     {
        User::create('benedekb97@gmail.com', 'thestump2010');
     }
}