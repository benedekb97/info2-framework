<?php

namespace App;

class View
{
    private $path;
    private $contents;

    public static function render(View $view){
        include $view->getPath();
    }

    public function __construct($name){

        $name = str_replace('.', '/', $name);

        $name .= '.fasz.php';

        $name = "views/" . $name;

        $this->path = $name;


        $this->contents = file_get_contents($this->path);

        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getContents()
    {
        return $this->contents;
    }
}