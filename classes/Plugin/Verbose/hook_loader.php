<?php

class Plugin_Verbose
{
    function hook_createRecord($object)
    {
        var_dump($object);
    }
}