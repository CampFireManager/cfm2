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
 * This class obtains manipulates all the configuration data for the service. It
 * handles local configuration (per-server), global configuration (per-site) and
 * secure configuration (api keys, password salts etc.)
 *
 * @category Container_Hook
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */

class Container_Hook
{
    protected static $objHook = null;
    
    /**
     * This protected function helps make this class a singleton
     *
     * @return Base_Hook
     */
    protected static function GetHandler()
    {
        if (self::$objHook == null) {
            self::$objHook = new Base_Hook();
        }
        return self::$objHook;
    }
    
    /**
     * This protected function lets us reset the class for Unit Testing
     * 
     * @return void
     */
    protected static function reset()
    {
        self::$objHook = null;
    }
    
    /**
     * Instantiate the class, load the config and return the handler.
     *
     * @param string  $strFileName  The filename to load
     * @param boolean $doReloadFile Force a clear and reload
     *
     * @return Base_Hook
     */
    public static function Load($strFileName = null, $doReloadFile = false)
    {
        if ($doReloadFile) {
            self::reset();
        }
        $objHook = self::GetHandler();
        if (! $objHook->isFileLoaded()) {
            try {
                if ($strFileName == null) {
                    $strFileName = 'plugin.php';
                }
                $objHook->Load($strFileName);
            } catch (Exception $e) {
                throw $e;
            }
        }
        return $objHook;
    }
}