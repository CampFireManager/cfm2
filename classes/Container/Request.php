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
    
    public static function getRequest(
        $arrGlobals = null, 
        $arrServer = null,
        $arrRequest = null,
        $arrGet = null,
        $arrPost = null,
        $arrFiles = null,
        $strInput = null,
        $arrSession = null
    ) {
        $request = self::GetHandler();
        $request->parse(
            $arrGlobals, 
            $arrServer,
            $arrRequest,
            $arrGet,
            $arrPost,
            $arrFiles,
            $strInput,
            $arrSession
        );
        return $request;
    }
}