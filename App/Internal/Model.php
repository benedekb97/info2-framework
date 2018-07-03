<?php

namespace App\Internal;


class Model extends Base
{
    protected static $table;
    protected static $attributes;

    public function save()
    {
        $fields = "(";
        $values = "(";

        foreach($this->attributes as $attribute) {
            $fields .= "$attribute, ";
            $value = $this->$attribute;
            $values .= "'$value', ";
        }

        $new_fields = "";
        $new_values = "";

        for($i = 0; $i < strlen($fields) -2; $i++) {
            $new_fields .= $fields[$i];
        }

        for($i = 0; $i < strlen($values) - 2; $i++) {
            $new_values .= $values[$i];
        }

        $new_fields .= ")";
        $new_values .= ")";

        $query = "UPDATE $this->table SET $new_fields VALUES $new_values;";

        self::$mysql->query($query);
    }

    public static function getAttributes()
    {
        return static::$attributes;
    }

    public static function getTable()
    {
        return static::$table;
    }

    public static function checkTable()
    {
        $db_name = env('MYSQL_DATABASE');
        $table_name = static::getTable();

        $query = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='$db_name' AND TABLE_NAME='$table_name'";

        $table_query = parent::$mysql->query($query);

        $columns_query_text = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$db_name' AND TABLE_NAME='$table_name'";

        $columns_query = parent::$mysql->query($columns_query_text);

        while($row = $columns_query->fetch_assoc()) {
            $column_type = $row['COLUMN_TYPE'];
            $column_name = $row['COLUMN_NAME'];

            $column = [$column_name, $column_type];

            if(!in_array($column, self::getAttributes())) {
                foreach(self::getAttributes() as $attribute) {
                    if($attribute[0] == $column_name) {
                        $new_column_name = $attribute[0];
                        $new_column_type = $attribute[1];
                    }
                }

                $drop_column_query = "ALTER TABLE $table_name DROP COLUMN $column_name";
                parent::$mysql->query($drop_column_query);

                if(isset($new_column_name)) {
                    $create_column_query = "ALTER TABLE $table_name ADD $new_column_name $new_column_type";
                    parent::$mysql->query($create_column_query);
                }
            }
        }

        foreach(self::getAttributes() as $attribute) {
            $columns_query = parent::$mysql->query($columns_query_text);
            $done = false;

            while($row = $columns_query->fetch_assoc() && $done == false) {
                if($row['COLUMN_NAME'] == $attribute[0] && $row['COLUMN_TYPE'] == $attribute[1]) {
                    $done = true;
                }
            }

            if(!$done) {
                $create_column_query = "ALTER TABLE $table_name ADD $attribute[0] $attribute[1]";
                parent::$mysql->query($create_column_query);
            }
        }

        return $table_query->num_rows == 1;
    }

    public static function createTable()
    {
        $table_name = static::getTable();

        $query = "CREATE TABLE $table_name ( \n";

        foreach(static::getAttributes() as $attribute) {
            $query .= $attribute[0] . " " . $attribute[1] . ",\n";
        }

        $new_query = "";

        for($i = 0; $i < strlen($query) - 2; $i++) {
            $new_query .= $query[$i];
        }

        $new_query .= "\n)";

        $query = parent::$mysql->query($new_query);

        return $query != false;
    }

    public static function dropTable()
    {
        $table_name = self::getTable();

        $query = "DROP TABLE $table_name";

        parent::$mysql->query($query);
    }
}