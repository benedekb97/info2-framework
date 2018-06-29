<?php

namespace App\Internal;


class Controller
{

    public function __invoke(){
        return true;
    }

    protected static function view($view_name){
        return new View($view_name);
    }

}