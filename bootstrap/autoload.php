<?php

spl_autoload_register(function ($class_name) {
    $class_name =  __DIR__ . "/../" . str_replace('\\', '/', $class_name) . ".php";

    require_once $class_name;
});