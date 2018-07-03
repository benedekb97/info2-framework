<?php

namespace App\Internal;

use mysqli;

class Base
{
    /**
     * @var mysqli
     */
    protected static $mysql;

    protected static $models = ['User'];

    /**
     * @param $host
     * @param $username
     * @param $password
     * @param $db
     */
    public static function db_connect($host, $username, $password, $db)
    {
        self::$mysql = new mysqli($host, $username, $password, $db);

        self::$mysql->query("SET NAMES utf8");
    }

    /**
     * @return array[Model]
     */
    public static function getModels()
    {
        return self::$models;
    }

    public static function update()
    {
        foreach(self::getModels() as $model) {
            $model = 'App\Models\\' . $model;

            if(!$model::checkTable()) {
                $model::dropTable();
                $model::createTable();
            }
        }

        echo "Database updated!\n";
    }

    public static function seed()
    {
        Seeder::seed();

        echo "Database seeded!\n";
    }

    public static function trunc()
    {
        foreach(self::getModels() as $model) {
            $model = 'App\Models\\' . $model;

            $model::trunc();
        }

        echo "Database truncated!\n";
    }
}