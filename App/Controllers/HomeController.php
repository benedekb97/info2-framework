<?php

namespace App\Controllers;

use App\Controller;
use App\ViewParser;

class HomeController extends Controller
{

    public function index()
    {
        return view('index');
    }
}