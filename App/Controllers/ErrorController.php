<?php

namespace App\Controllers;


use App\Controller;
use HttpException;

class ErrorController extends Controller
{

    public static function badRequest(){
        return parent::view('errors.400');
    }

    public static function notFound(){
        return parent::view('errors.404');
    }
}