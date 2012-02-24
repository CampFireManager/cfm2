<?php

class Plugin_Verbose
{
    function hook_createRecord($object)
    {
        echo "(" . $object->sql . ") {";
        foreach ($object->sql_value as $valuename => $value) {
            echo "$valuename - $value, ";
        }
        echo "}\r\n";
    }
}