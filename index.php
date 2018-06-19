<?php

namespace App;

use Exception;

require_once "bootstrap/autoload.php";
require_once "bootstrap/init.php";
try{
    include Router::route()->getPath();
}catch(Exception $exception){

}
