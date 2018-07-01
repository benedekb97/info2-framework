<?php

namespace App\Internal;

class View
{
    private $path;
    private $contents;
    private $variables;
    private $name;

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

        $this->name = $name;

        $name = str_replace('.', '/', $name);

        $name .= '.fasz.php';

        $name = "views/" . $name;

        $this->path = $name;

        $this->variables = $variables;

        if($this->variables != null) {
            $_SESSION['temp_passed_variables'] = $this->variables;
        }

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

    public function getName()
    {
        return $this->name;
    }

    public function isCached()
    {
        return Cache::check($this);
    }
}