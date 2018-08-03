<?php

namespace App\Controllers;

use App\Internal\Controller;
use App\Models\User;
use App\Internal\Request;
use App\Internal\Router;

class AuthController extends Controller
{
    /**
     * @return string
     * @throws \App\Exceptions\RouteNotFoundException
     */
    public function login(){

        if(User::authenticate(Request::post('email'), Request::post('password'))){
            $email = Request::post('email');

            $_SESSION['user_id'] = User::findByEmail($email)->getId();

            Router::redirect('index');
        }else{
            Router::redirect('auth.login');
        }

        return self::class;
    }

    /**
     * @throws \App\Exceptions\RouteNotFoundException
     */
    public function logout()
    {
        unset($_SESSION['user_id']);

        Router::redirect('index');
    }
}