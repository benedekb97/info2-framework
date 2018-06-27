<?php

namespace App;


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

    public static function setup()
    {
        if(isset($_SESSION['user_id'])) {
            self::$user = new User($_SESSION['user_id']);
        }
    }
}