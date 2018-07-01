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

    public function kurva()
    {
        return view('kurva');
    }

    public function fasz()
    {
        $fasz = Request::get('fasz');

        return view('fasz', ['fasz' => $fasz]);
    }
}