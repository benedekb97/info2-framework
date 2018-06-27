<?php

namespace App;

class View
{
    private $path;
    private $contents;
    private $variables;

    public static function render(View $view){
        include $view->getPath();
    }

    /**
     * View constructor.
     * @param $name
     * @param null $variables
     * @return View
     */
    public function __construct($name, $variables = null){

        $name = str_replace('.', '/', $name);

        $name .= '.fasz.php';

        $name = "views/" . $name;

        $this->path = $name;

        $this->variables = $variables;

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

    public function getVariables()
    {
        return $this->variables;
    }
}