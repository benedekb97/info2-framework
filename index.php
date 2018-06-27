<?php

namespace App;

use Exception;

require_once "bootstrap/autoload.php";
require_once "bootstrap/init.php";
try{
    Router::route();

    if(isset($_SESSION['temp_file_name'])) {
        include 'temp/' . $_SESSION['temp_file_name'];

        unlink('temp/' . $_SESSION['temp_file_name']);
    }
}catch(Exception $exception){

}
