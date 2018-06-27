<?php

namespace App;

use App\Models\User;

session_start();

require_once 'functions.php';

if(env('DEBUG_MODE') == "PRODUCTION") {
    error_reporting(E_ERROR);
}elseif(env('DEBUG_MODE') == "DEBUG") {
    error_reporting(E_STRICT);
}else{
    error_reporting(E_ALL);
}

Base::db_connect(env('MYSQL_HOST'), env('MYSQL_USER'), env('MYSQL_PASSWORD'), env('MYSQL_DATABASE'));

Request::create();

Auth::setup();

require_once __DIR__ . "/../routes/routes.php";
