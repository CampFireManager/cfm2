<?php

class Container_Request
{
    protected static $self = null;
    
    protected static function GetHandler()
    {
        if (self::$self == null) {
            self::$self = new self();
        }
        return self::$self;
    }
    
    protected static function reset()
    {
        self::$self = null;
    }
}

class Container_Request_Testable extends Container_Request
{
    public static function GetHandler()
    {
        return parent::GetHandler();
    }
    
    public static function reset()
    {
        parent::reset();
    }
}