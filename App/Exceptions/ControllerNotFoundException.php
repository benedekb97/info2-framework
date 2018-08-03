<?php

namespace App\Exceptions;

use Exception;

class ControllerNotFoundException extends Exception
{

    public function __construct($controller)
    {
        $line = $this->getLine();
        $file = $this->getFile();

        $this->message = "<style>h1{font-weight:normal;}</style>";
        $this->message .= "<h1><b>ControllerNotFoundException</b> thrown on line <b>$line</b> in file <b>$file</b></h1>";
        $this->message .= "<br>";
        $this->message .= "<h2>Controller '$controller' not found.</h2>";
    }
}