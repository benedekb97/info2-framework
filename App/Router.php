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
        $request_uri = Request::uri();
        $request_type = Request::type();

        $matches = [];

        foreach (self::$routes as $route) {
            $matches[] = [
                'route' => $route,
                'index' => self::check($route, $request_uri) > 0,
                'request' => ($route['type'] == $request_type)
            ];
        }

        $bad_request = false;
        $found = false;
        foreach ($matches as $match) {
            if ($match['index'] && $match['request']) {

                foreach ($match['route']['uri'] as $key => $value) {
                    if (self::needsVar($value)) {
                        Request::passGet(self::getVarName($value), $request_uri[$key]);
                    }
                }

                $controller = 'App\Controllers\\' . ucfirst(explode('@',$match['route']['controller'])[0]);

                $controller = new $controller;

                if($controller() != true){
                    throw new ControllerNotFoundException;
                }

                $function = explode('@',$match['route']['controller'])[1];

                $file_contents = self::TEMP_FILE_HEADER . ViewParser::parse($controller->$function());

                $temp_file = self::generateTempFile();

                fwrite($temp_file, $file_contents);

                return true;
            }

            if($match['index'] && !$match['request']){
                $bad_request = true;
            }

            if($match['index']){
                $found = true;
            }
        }

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