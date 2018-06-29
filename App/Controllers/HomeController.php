<?php

namespace App\Controllers;

use App\Internal\Controller;
use App\Models\User;

class HomeController extends Controller
{

    public function index()
    {
        $users = User::all();

        return view('index', ['users' => $users]);
    }
}