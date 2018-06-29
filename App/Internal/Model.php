<?php

namespace App\Internal;


class Model extends Base
{
    protected $table;
    protected $attributes;

    public function save()
    {
        $fields = "(";

        foreach($this->attributes as $attribute) {
            $fields .= "$attribute, ";
        }

        $new_fields = "";

        for($i = 0; $i < strlen($fields) -2; $i++) {
            $new_fields .= $fields[$i];
        }

        $new_fields .= ")";

        dd($new_fields);
    }
}