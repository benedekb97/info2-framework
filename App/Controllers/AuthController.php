<?php

namespace App\Controllers;

use App\Controller;
use App\Models\User;
use App\Request;
use App\Router;

class AuthController extends Controller
{

    public static function login(){

        if(User::authenticate(Request::post('email'), Request::post('password'))){
            $email = Request::post('email');

            $_SESSION['user_id'] = User::findByEmail($email)->getId();

            Router::redirect('index');
            die();
        }else{
            Router::redirect('index.login');
        }
    }

    public static function logout(){
        unset($_SESSION['user_id']);

        Router::redirect('index');
    }
}