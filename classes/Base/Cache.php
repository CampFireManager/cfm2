<?php

class Base_Cache
{
    // Broker Requirements
    public $arrCache = array();
    protected static $cache_handler = null;

    /**
     * An internal function to make this a singleton. This should only be used when being used to find objects of itself.
     *
     * @return object This class by itself.
     */
    public static function getHandler()
    {
        if (self::$cache_handler == null) {
            self::$cache_handler = new self();
        }
        return self::$cache_handler;
    }

}