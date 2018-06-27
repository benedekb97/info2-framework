<?php

namespace App;

use App\Controllers\ErrorController;
use ControllerNotFoundException;

class Router
{
    const TEMP_FILE_HEADER = "<?php
    
    namespace App;?>";

    protected static $routes;

    public static function get($uri, $controller, $name = ''){
        self::$routes[] = [
            'uri' => explode('/', $uri),
            'controller' => $controller,
            'type' => 'GET',
            'name' => $name
        ];
    }

    public static function post($uri, $controller, $name = ''){
        self::$routes[] = [
            'uri' => explode('/', $uri),
            'controller' => $controller,
            'type' => 'POST',
            'name' => $name
        ];
    }

    protected static function needsVar($uri_section){
        if(strlen($uri_section) > 3){
            return $uri_section[0] == '{' && $uri_section[strlen($uri_section)-1] == '}';
        }else{
            return false;
        }
    }

    protected static function getVarName($uri_section){
        $uri_section = trim($uri_section, '{}');

        return $uri_section;
    }

    protected static function check($route, $uri){
        $match_index = 0;

        foreach($route['uri'] as $key => $value){
            if(isset($uri[$key]) && $uri[$key] == ""){
                $match_index = 0;
            }

            if(isset($uri[$key]) && $uri[$key] == $value){
                $match_index++;
            }elseif(!self::needsVar($value)){
                return 0;
            }

            if(isset($uri[$key]) && self::needsVar($value) && !is_numeric($uri[$key])){
                return 0;
            }

            if(isset($uri[$key]) && self::needsVar($value) && is_numeric($uri[$key])){
                $match_index++;
            }
        }

        if(sizeof($route['uri']) != sizeof($uri) && $uri[sizeof($uri)-1] != ''){
            return 0;
        }


        return $match_index;
    }

    public static function route()
    {
        // Gets the URI and the request type
        $request_uri = Request::uri();
        $request_type = Request::type();

        $matches = [];

        // goes through the routes (from routes.php) and creates array items for every one
        foreach (self::$routes as $route) {
            $matches[] = [
                'route' => $route,
                // index is a way of measuring how much the request URI matches a specific route if it doesn't match, it's 0. if it does it's >0
                'index' => self::check($route, $request_uri) > 0,
                // if request type matches then this is true
                'request' => ($route['type'] == $request_type)
            ];
        }

        $bad_request = false;
        $found = false;

        // Goes through all the matches
        foreach ($matches as $match) {

            // if the URI is correct and the request type is also correct
            if ($match['index'] && $match['request']) {

                // goes through the parts of the URI and checks if the controller is expecting a variable there
                foreach ($match['route']['uri'] as $key => $value) {
                    if (self::needsVar($value)) {
                        // if it is then the variable is passed on to the Request class
                        Request::passGet(self::getVarName($value), $request_uri[$key]);
                    }
                }

                // finds the controller the route is requesting
                $controller = 'App\Controllers\\' . ucfirst(explode('@',$match['route']['controller'])[0]);

                // creates an instance of it
                $controller = new $controller;

                // if the controller doesn't return true when called as a function it throws an exception
                if($controller() != true){
                    throw new ControllerNotFoundException;
                }

                // finds the function that needs to be called in the controller
                $function = explode('@',$match['route']['controller'])[1];

                // generates the contents of the temporary file to be included in index.php
                $file_contents = self::TEMP_FILE_HEADER . ViewParser::parse($controller->$function());

                // generates the temporary file
                $temp_file = self::generateTempFile();

                // writes the contents to it
                fwrite($temp_file, $file_contents);

                return true;
            }

            // if the URI is correct but the request type is not it sets $bad_request to true
            if($match['index'] && !$match['request']){
                $bad_request = true;
            }

            if($match['index']){
                $found = true;
            }
        }

        // TODO: Generate temp file for 404 and 400 here
        if($bad_request == true){
            return ViewParser::parse(ErrorController::badRequest());
        }elseif(!$found){
            return ViewParser::parse(ErrorController::notFound());
        }

        return ViewParser::parse(ErrorController::notFound());
    }

    public static function generateTempFile()
    {
        $file_name = "";

        $available_characters = "0123456789abcdef";

        $length = rand(16,32);

        for($i = 0; $i < $length; $i++) {
            $file_name .= $available_characters[rand(0,15)];
        }

        $file_name .= ".tmp.php";

        $_SESSION['temp_file_name'] = $file_name;

        return fopen(__DIR__ . "/../temp/" . $file_name, "w");
    }

    /**
     * @param $name
     * @param array $vars
     * @return bool|string
     */
    public static function getLink($name, $vars = []){

        foreach(self::$routes as $route){

            if($route['name'] == $name){
                $uri = "";
                foreach($route['uri'] as $uri_section){
                    if(self::needsVar($uri_section)){
                        $uri .= "/".$vars[self::getVarName($uri_section)];
                    }else{
                        $uri .= "/".$uri_section;
                    }

                }

                return $uri;
            }

        }

        return false;

    }

    public static function redirect($route, $vars = [])
    {
        header("Location: ".self::getLink($route, $vars));
    }
}