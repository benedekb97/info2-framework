<?php

namespace App\Internal;


class Cache
{
    const CACHED_VIEWS_FILE = __DIR__ . "/../../cache/cached_views";

    protected static $cached_views;

    public static function setup()
    {
        if(!file_exists(__DIR__ . "/../../cache")){
            mkdir(__DIR__ . "/../../cache", 0777, true);
        }

        if (!file_exists(self::CACHED_VIEWS_FILE)) {
            $file = fopen(self::CACHED_VIEWS_FILE, "w");
            fclose($file);
        }

        $file = fopen(self::CACHED_VIEWS_FILE, "r");

        $contents = file_get_contents(self::CACHED_VIEWS_FILE);

        $explode = explode(';', $contents);

        array_shift($explode);

        foreach($explode as $view) {
            self::$cached_views[] = $view;
        }

        fclose($file);
    }

    public static function check(View $view)
    {
        if(env('VIEW_CACHING') == 'false')
            return false;

        if(self::$cached_views != null)
            return in_array($view->getName(), self::$cached_views);
        else
            return false;
    }

    public static function cache(View $view)
    {
        self::$cached_views[] = $view;

        $cached_file_name = $view->getName() . ".tmp.php";
        $cached_file = fopen(__DIR__ . "/../../cache/" . $cached_file_name, "w");

        $contents = "<?php namespace App\Internal; ?>" . ViewParser::parse($view);

        fwrite($cached_file, $contents);

        fclose($cached_file);


        $file = fopen(self::CACHED_VIEWS_FILE, "w");

        fwrite($file, ";". $view->getName());

        fclose($file);

        $_SESSION['cached_views'][] = $view->getName();
    }
}