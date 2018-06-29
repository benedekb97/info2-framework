<?php

namespace App;

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
        include 'temp/' . $_SESSION['cached_views'][$_SESSION['current_view']];

        unset($_SESSION['current_view']);
        if(isset($_SESSION['temp_passed_variables'])){
            unset($_SESSION['temp_passed_variables']);
        }
    }
}catch(Exception $exception){
    echo "Controller not found";
}
