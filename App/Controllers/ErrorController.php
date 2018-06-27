<?php

namespace App\Controllers;


use App\Controller;
use HttpException;

class ErrorController extends Controller
{

    public static function badRequest(){
        return view('errors.400');
    }

    public static function notFound(){
        return view('errors.404');
    }
}