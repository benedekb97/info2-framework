<?php

namespace App;

use Exception;

// Autoload classes
require_once "bootstrap/autoload.php";

// Initialise static variables in classes, and such
require_once "bootstrap/init.php";


try{
    // Does too much to write in such a small space
    Router::route();

    if(isset($_SESSION['temp_file_name'])) {
        include 'temp/' . $_SESSION['temp_file_name'];


        unlink('temp/' . $_SESSION['temp_file_name']);
        unset($_SESSION['temp_file_name']);
        unset($_SESSION['temp_passed_variables']);
    }
}catch(Exception $exception){

}
