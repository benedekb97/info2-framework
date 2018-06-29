<?php

namespace App\Internal;


use App\Models\User;

class Auth
{
    private static $user;

    public static function user()
    {
        return self::$user;
    }

    public static function check()
    {
        return self::$user != null;
    }

    // If the user is set set static $user variable the currently logged in user
    public static function setup()
    {
        if(isset($_SESSION['user_id'])) {
            self::$user = new User($_SESSION['user_id']);
        }
    }
}