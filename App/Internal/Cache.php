<?php

namespace App\Internal;


class Cache
{
    const CACHED_VIEWS_DIR = __DIR__ . "/../../cache";

    const DEFAULT_CACHE_TIMEOUT = 60*60;

    private static $cache_timeout;

    protected static $cached_views;

    public static function setup()
    {
        if(env('DEBUG_MODE') == 'DEBUG') {
            self::$cache_timeout = 10;
        }else{
            self::$cache_timeout = self::DEFAULT_CACHE_TIMEOUT;
        }

        if(!file_exists(self::CACHED_VIEWS_DIR)){
            mkdir(self::CACHED_VIEWS_DIR, 0777, true);
        }

        $files = scandir(self::CACHED_VIEWS_DIR);

        for($i = 0; $i < 2; $i++){
            array_shift($files);
        }

        if(env('VIEW_CACHING') == 'false') {
            foreach($files as $file) {
                unlink(self::CACHED_VIEWS_DIR . "/" . $file);
            }
        }

        foreach($files as $file) {
            $file_name_parts = explode('.', $file);

            $view_name = "";
            for($i = 0; $i < sizeof($file_name_parts) - 3; $i++) {
                $view_name .= $file_name_parts[$i]. ".";
            }

            $new_view_name = "";
            for($i = 0; $i < strlen($view_name) - 1; $i ++) {
                $new_view_name .= $view_name[$i];
            }

            $timestamp = $file_name_parts[sizeof($file_name_parts) -3];

            if(abs($timestamp - time()) > self::$cache_timeout) {
                unlink(self::CACHED_VIEWS_DIR . "/" . $file);
            }

            if(abs($timestamp - time()) < self::$cache_timeout && env('VIEW_CACHING') != 'false') {
                self::$cached_views[$new_view_name] = $timestamp;
            }

        }

    }

    public static function check(View $view)
    {
        if(env('VIEW_CACHING') == 'false')
            return false;

        if(self::$cached_views != null)
            return array_key_exists($view->getName(), self::$cached_views);
        else
            return false;
    }

    public static function cache(View $view)
    {
        $timestamp = time();

        self::$cached_views[$view->getName()] = $timestamp;

        $cached_file_name = $view->getName() . "." . $timestamp . ".tmp.php";

        $cached_file = fopen(__DIR__ . "/../../cache/" . $cached_file_name, "w");

        $contents = "<?php namespace App\Internal; ?>" . ViewParser::parse($view);

        fwrite($cached_file, $contents);

        fclose($cached_file);
    }

    public static function getFileName($view_name)
    {
        $timestamp = self::$cached_views[$view_name];

        return $view_name . "." . $timestamp . ".tmp.php";
    }
}