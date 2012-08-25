<?php
/**
 * CampFire Manager is a scheduling tool predominently used at BarCamps to 
 * schedule talks based, mainly, on the number of people attending each talk
 * receives.
 *
 * PHP version 5
 *
 * @category Default
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */
/**
 * This class caches all the responses from the database searches.
 *
 * @category Base_Cache
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */

class Base_Cache
{
    // Broker Requirements
    public $arrCache = array();
    protected static $cache_handler = null;

    /**
     * An internal function to make this a singleton. This should only be used 
     * when being used to find objects of itself.
     *
     * @return Base_Cache
     */
    public static function getHandler()
    {
        if (self::$cache_handler == null) {
            self::$cache_handler = new self();
        }
        return self::$cache_handler;
    }

    /**
     * Empty the handler - used in unit testing only.
     * 
     * @return void
     */
    public static function flush()
    {
        if (self::$cache_handler != null) {
            self::$cache_handler = null;
        }
    }
}