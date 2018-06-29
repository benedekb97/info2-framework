<?php

namespace App\Internal;


class Model extends Base
{
    protected $table;
    protected $attributes;

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
}