<?php

namespace App\Controllers;

use App\Internal\Controller;
use App\Internal\Request;
use App\Models\User;

class HomeController extends Controller
{

    /**
     * @return \App\Internal\View
     */
    public function index()
    {
        $users = User::all();

        return view('index', ['users' => $users]);
    }

    /**
     * @return \App\Internal\View
     */
    public function stump()
    {
        return view('stump', ['user' => Request::get('user')]);
    }
}