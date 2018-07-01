<?php

namespace App\Middleware;


use App\Internal\Auth;
use App\Internal\Middleware;
use App\Internal\Router;

class AuthMiddleware extends Middleware
{
    public function check()
    {
        if(!Auth::check()) {
            Router::redirect('index');
        }

        return Auth::check();
    }
}