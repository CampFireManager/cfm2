<?php

class Container_Request
{
    protected static $objRequest = null;
    
    protected static function GetHandler()
    {
        if (self::$objRequest == null) {
            self::$objRequest = new Base_Request();
        }
        return self::$objRequest;
    }
    
    protected static function reset()
    {
        self::$objRequest = null;
    }
    
    public static function getRequest()
    {
        $request = self::GetHandler();
        $request->parse();
    }
}