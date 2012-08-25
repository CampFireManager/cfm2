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
 * This class handles all request calls.
 *
 * @category Container_Request
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */

class Container_Request
{
    protected static $objRequest = null;
    
    /**
     * This simple function makes the class into a singleton.
     *
     * @return Base_Request
     */
    protected static function GetHandler()
    {
        if (self::$objRequest == null) {
            self::$objRequest = new Base_Request();
        }
        return self::$objRequest;
    }
    
    /**
     * This flushes the singleton handler. Only really used in unit testing.
     * 
     * @return void
     */
    protected static function reset()
    {
        self::$objRequest = null;
    }
    
    /**
     * This function wrappers the parse function.
     * 
     * @param array  $arrGlobals A dependency injection entry for $GLOBALS
     * @param array  $arrServer  A dependency injection entry for $_SERVER
     * @param array  $arrRequest A dependency injection entry for $_REQUEST
     * @param array  $arrGet     A dependency injection entry for $_GET
     * @param array  $arrPost    A dependency injection entry for $_POST
     * @param array  $arrFiles   A dependency injection entry for $_FILES
     * @param string $strInput   A dependency injection entry for php://input
     * @param array  $arrSession A dependency injection entry for $_SESSION
     *
     * @return Base_Request
     */
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