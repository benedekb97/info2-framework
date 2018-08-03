<?php

namespace App\Exceptions;

use Exception;

class RouteNotFoundException extends Exception
{
    public function __construct($route)
    {
        $line = $this->getLine();
        $file = $this->getFile();

        $this->message = "<style>h1{font-weight:normal;}</style>";
        $this->message .= "<h1><b>RouteNotFoundException</b> thrown on line <b>$line</b> in file <b>$file</b></h1>";
        $this->message .= "<br>";
        $this->message .= "<h2>Route '$route' not found.</h2><br><i>Check controller or routes file.</i>";
    }
}