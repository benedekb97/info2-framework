<?php

namespace App\Controllers;

use App\Internal\Auth;
use App\Internal\Controller;
use App\Models\User;

class HomeController extends Controller
{

    public function index()
    {
        $users = User::all();

        if(Auth::check()){

            Auth::user()->setEmail("benedekb97@gmail.com");

            Auth::user()->save();
        }

        return view('index', ['users' => $users]);
    }
}