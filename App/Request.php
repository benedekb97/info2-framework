<?php

namespace App;

class Request extends Base
{
    protected static $get;
    protected static $post;

    /**
     * @param $var_name
     * @return mixed
     */
    public static function get($var_name)
    {
        return self::$get[$var_name];
    }

    /**
     * @return array
     */
    public static function request()
    {
        return [
            'get' => self::$get,
            'post' => self::$post
        ];
    }

    /**
     * @param $var_name
     * @return mixed
     */
    public static function post($var_name)
    {
        return self::$post[$var_name];
    }

    /**
     * @return mixed
     */
    public static function type()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * @return array
     */
    public static function uri()
    {
        $uri = [];
        foreach (explode('/', $_SERVER['REQUEST_URI']) as $item) {
            $uri[] = $item;
        }

        array_shift($uri);

        return $uri;
    }

    public static function create()
    {
        foreach ($_GET as $key => $item) {
            self::$get[$key] = self::$mysql->real_escape_string($item);
        }

        foreach ($_POST as $key => $item) {
            self::$post[$key] = self::$mysql->real_escape_string($item);
        }
    }

    /**
     * @param $key
     * @param $value
     */
    public static function passGet($key, $value)
    {
        self::$get[$key] = self::$mysql->real_escape_string($value);
    }

    /**
     * @param $key
     * @param $value
     */
    public static function passPost($key, $value)
    {
        self::$post[$key] = self::$mysql->real_escape_string($value);
    }
}