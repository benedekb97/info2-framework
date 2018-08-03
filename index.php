<?php

namespace App;

use App\Internal\Cache;
use App\Internal\Router;
use Exception;

// Autoload classes
require_once "bootstrap/autoload.php";

// Initialise static variables in classes, and such
require_once "bootstrap/init.php";

try{
    // Does too much to write in such a small space
    Router::route();

    if(isset($_SESSION['current_view'])) {
        include 'cache/' . Cache::getFileName($_SESSION['current_view']);

        unset($_SESSION['current_view']);
        if(isset($_SESSION['temp_passed_variables'])){
            unset($_SESSION['temp_passed_variables']);
        }
    }
}catch(Exception $exception){
    die($exception->getMessage());
}
