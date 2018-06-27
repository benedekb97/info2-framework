<?php

namespace App;

use App\Models\User;
use Exception;

require_once "bootstrap/autoload.php";
require_once "bootstrap/init.php";


try{
    Router::route();

    if(isset($_SESSION['temp_file_name'])) {
        include 'temp/' . $_SESSION['temp_file_name'];


        unlink('temp/' . $_SESSION['temp_file_name']);
        unset($_SESSION['temp_file_name']);
        unset($_SESSION['temp_passed_variables']);
    }
}catch(Exception $exception){

}
