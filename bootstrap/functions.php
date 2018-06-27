<?php

use App\View;

function env($env_name)
{
    $env_location = __DIR__. '/../.env';

    $env = fopen($env_location, 'r');

    $contents = fread($env, filesize($env_location));

    $contents = explode("\n",$contents);

    foreach($contents as $line){
        if(strlen($line) != 0){
            $line = explode('=',$line);

            if($line[0] == $env_name){
                $value = "";

                if(sizeof($line) > 2){
                    foreach($line as $key => $line_array_part){
                        if($key != 0 && $key != sizeof($line)-1){
                            $value .= $line_array_part."=";
                        }
                        if($key != 0 && $key == sizeof($line)-1){
                            $value .= $line_array_part;
                        }
                    }

                }else{
                    $value = $line[1];
                }

                if(strlen($value)>0 && ($value[strlen($value)-1] == "\r" || $value[strlen($value)-1] == "\n")){
                    $value = substr($value, 0, -1);
                }

                return $value;
            }
        }
    }

    fclose($env);

    return false;
}

function view($view_name, $variables)
{
    return new View($view_name, $variables);
}

function dd($variable)
{
    ?>
    <pre>
        <?php
        var_dump($variable);
        ?>
    </pre>
    <?php

    die();
}