<?php

namespace App;

session_start();

// Include functions
require_once 'functions.php';

// Check debug mode and set php error reporting
if(env('DEBUG_MODE') == "PRODUCTION") {
    error_reporting(E_ERROR);
}elseif(env('DEBUG_MODE') == "DEBUG") {
    error_reporting(E_STRICT);
}else{
    error_reporting(E_ALL);
}

// Connect to database (as in env)
Base::db_connect(env('MYSQL_HOST'), env('MYSQL_USER'), env('MYSQL_PASSWORD'), env('MYSQL_DATABASE'));

// Set Request static variables
Request::create();

// Set Auth static variables
Auth::setup();

// Include the routes file, which sets all the available routes in the static Router class
require_once __DIR__ . "/../routes/routes.php";
