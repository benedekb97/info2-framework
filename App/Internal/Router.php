<?php

namespace App\Internal;

use App\Controllers\ErrorController;
use App\Exceptions\ControllerNotFoundException;
use App\Exceptions\RouteNotFoundException;
use Exception;

class Router
{
    const TEMP_FILE_HEADER = "<?php namespace App\Internal;";

    protected static $routes;

    private static $prefixing;

    public static function get($uri, $controller, $name = '', $middleware = null){

        if(self::isPrefixActive()) {
            if($uri == ''){
                $uri = self::$prefixing['uri'];
            }else{
                $uri = self::$prefixing['uri'] . '/' . $uri;
            }
            $name = self::$prefixing['name'] . '.' .$name;
            $middleware = self::$prefixing['middleware'];
        }

        self::$routes[] = [
            'uri' => explode('/', $uri),
            'controller' => $controller,
            'type' => 'GET',
            'name' => $name,
            'middleware' => $middleware
        ];
    }

    public static function post($uri, $controller, $name = '', $middleware = null){

        if(self::isPrefixActive()) {
            if($uri == ''){
                $uri = self::$prefixing['uri'];
            }else{
                $uri = self::$prefixing['uri'] . '/' . $uri;
            }
            $name = self::$prefixing['name'] . '.' .$name;
            $middleware = self::$prefixing['middleware'];
        }

        self::$routes[] = [
            'uri' => explode('/', $uri),
            'controller' => $controller,
            'type' => 'POST',
            'name' => $name,
            'middleware' => $middleware
        ];
    }

    public static function group($uri_prefix, $name_prefix, $callback, $middleware = null)
    {
        self::startPrefix($uri_prefix, $name_prefix, $middleware);

        $callback();

        self::endPrefix();
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

    private static function generateUseStatement()
    {
        $tmp = "use App\\Models\\{";
        $new_use = "";

        foreach(Base::getModels() as $model) {
            $tmp .= $model . ", ";
        }

        for($i = 0; $i < strlen($tmp) - 2; $i++) {
            $new_use .= $tmp[$i];
        }

        $new_use .= "};";

        return $new_use;
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

    /**
     * @return bool
     * @throws ControllerNotFoundException
     * @throws Exception
     */
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
                'request' => ($route['type'] == $request_type),
                'middleware' => $route['middleware']
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
                $controller_name = 'App\Controllers\\' . ucfirst(explode('@',$match['route']['controller'])[0]);

                // creates an instance of it
                $controller = new $controller_name;

                // if the controller doesn't return true when called as a function it throws an exception
                if($controller() != true){
                    throw new ControllerNotFoundException($controller_name);
                }

                // finds the function that needs to be called in the controller
                $function = explode('@',$match['route']['controller'])[1];

                if($match['middleware'] != null) {
                    $middleware = 'App\Middleware\\' . $match['middleware'];

                    $middleware = new $middleware;

                    if(!$middleware->check()) {
                        $_SESSION['current_view'] = 'errors.500';

                        if(!Cache::check(view('errors.500'))) {
                            Cache::cache(view('errors.500'));
                        }

                        return false;
                    }
                }

                try {
                    $view = $controller->$function();
                } catch(Exception $exception) {
                    throw $exception;
                }

                if($view != null) {
                    $_SESSION['current_view'] = $view->getName();
                }


                if(!Cache::check($view)) {
                    Cache::cache($view);
                }

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

        if($bad_request == true){
            if(!Cache::check(view('errors.400'))) {
                Cache::cache(view('errors.400'));
            }
            $_SESSION['current_view'] = 'errors.400';
            return false;
        }elseif(!$found){
            if(!Cache::check(view('errors.404'))) {
                Cache::cache(view('errors.404'));
            }
            $_SESSION['current_view'] = 'errors.404';
            return false;
        }

        if(Cache::check(view('errors.404'))) {
            Cache::cache(view('errors.404'));
        }
        $_SESSION['current_view'] = 'errors.404';
        return false;

    }

    /**
     * @param $name
     * @param array $vars
     * @return string
     * @throws RouteNotFoundException
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

        throw new RouteNotFoundException($name);
    }

    /**
     * @param $route
     * @param array $vars
     * @throws RouteNotFoundException
     */
    public static function redirect($route, $vars = [])
    {

        try{
            header("Location: ".self::getLink($route, $vars));
        } catch(RouteNotFoundException $exception) {
            throw $exception;
        }
    }

    private static function startPrefix($uri_prefix, $name_prefix, $middleware)
    {
        if(self::isPrefixActive()){
            self::$prefixing = [
                'active' => true,
                'level' => self::$prefixing['level']+1,
                'uri' => self::$prefixing['uri']."/".$uri_prefix,
                'name' => self::$prefixing['name'].'.'.$name_prefix,
                'middleware' => $middleware
            ];
            return true;
        }else{
            self::$prefixing = [
                'level' => self::$prefixing['level']+1,
                'active' => true,
                'uri' => $uri_prefix,
                'name' => $name_prefix,
                'middleware' => $middleware
            ];
            return true;
        }

    }

    private static function endPrefix()
    {
        $new_uri = "";

        $exploded_uri = explode('/', self::$prefixing['uri']);

        if(sizeof($exploded_uri) > 1) {
            for($i = 0; $i < sizeof($exploded_uri) - 1; $i++) {
                $new_uri .= $exploded_uri[$i] . "/";
            }
        }

        $new_name = "";

        $exploded_name = explode('.', self::$prefixing['name']);

        if(sizeof($exploded_name) > 1) {
            for($i = 0; $i < sizeof($exploded_name) - 1; $i++) {
                $new_name .= $exploded_name[$i] . ".";
            }
        }

        $name = "";

        for($i = 0; $i < strlen($new_name) - 1; $i++) {
            $name .= $new_name[$i];
        }

        self::$prefixing = [
            'level' => self::$prefixing['level']-1,
            'active' => self::$prefixing['level'] != 0,
            'uri' => $new_uri,
            'name' => $name,
            'middleware' => null
        ];

    }

    private static function isPrefixActive()
    {
        return self::$prefixing['active'] == true;
    }
}