<?php

namespace App;

use Exception;

require_once "bootstrap/autoload.php";
require_once "bootstrap/init.php";
try{
    echo Router::route();
}catch(Exception $exception){

}
