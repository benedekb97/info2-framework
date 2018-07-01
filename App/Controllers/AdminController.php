<?php

namespace App\Controllers;

use App\Internal\Controller;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }
}