<?php

namespace App;

class View
{
    private $path;

    public static function render(View $view){
        include $view->getPath();
    }

    public function __construct($name){
        $name = str_replace('.', '/', $name);

        $name .= '.php';

        $name = "views/" . $name;

        $this->path = $name;

        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }
}