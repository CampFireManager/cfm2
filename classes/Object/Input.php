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
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */
/**
 * This class defines the object for PDO to retrieve Text only Input data.
 * 
 * @category Object_Input
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Object_Input extends Abstract_GenericObject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
        'strSender' => array('type' => 'varchar', 'length' => 255),
        'strInterface' => array('type' => 'varchar', 'length' => 255),
        'textMessage' => array('type' => 'text'),
        'isActioned' => array('type' => 'tinyint', 'length' => 1),
        'intNativeID' => array('type' => 'integer', 'length' => 25),
        'lastChange' => array('type' => 'datetime')
    );
    protected $strDBTable = "input";
    protected $strDBKeyCol = "intInputID";
    protected $reqAdminToMod = true;
    // Local Object Requirements
    protected $intInputID = null;
    protected $strSender = null;
    protected $strInterface = null;
    protected $textMessage = null;
    protected $isActioned = false;
    protected $intNativeID = null;
    protected $lastChange = null;
    
    /**
     * Get all objects in this table
     * 
     * @return array The array of objects matching this search
     */
    public static function brokerAll()
    {
        if (Object_User::isSystem()) {
            return parent::brokerAll();
        } else {
            return array();
        }
    }
    
    /**
     * Get the object for the ID associated with a particular row
     *
     * @param integer $intID The Object ID to search for
     *
     * @return Object_Input
     */
    public static function brokerByID($intID)
    {
        if (Object_User::isSystem()) {
            return parent::brokerByID($intID);
        } else {
            return false;
        }
    }
    
    /**
     * Get all objects by a particular search field
     *
     * @param string  $column  The column to search
     * @param string  $value   The value to look for.
     * @param boolean $inverse Look for anything but this value
     * 
     * @return array The array of objects matching this search
     */
    public static function brokerByColumnSearch($column = null, $value = null, $inverse = false)
    {
        if (Object_User::isSystem()) {
            return parent::brokerByColumnSearch($column, $value, $inverse);
        } else {
            return false;
        }
    }
    
    /**
     * Ensure that all database items are backed up before processing.
     *
     * This is our usual construct method for all extended classes.
     *
     * @return Object_Input
     */
    public function __construct()
    {
        if (! Object_User::isSystem()) {
            throw new Exception('It is not permitted to directly create input actions');
        } else {
            parent::__construct();
        }
    }
}

/**
 * This class defines some default and demo data for the use in demos.
 * 
 * @category Object_Input
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */
class Object_Input_Demo extends Object_Input
{
    protected $arrDemoData = array(
        array('strSender' => '+447000000001', 'strInterface' => 'gammu', 'textMessage' => 'I Joe Bloggs'),
        array('strSender' => '+447000000001', 'strInterface' => 'gammu', 'textMessage' => 'A 1'),
        array('strSender' => 'joe@example.com', 'strInterface' => 'xmpp', 'textMessage' => 'D 1'),
        array('strSender' => 'JoeBloggs', 'strInterface' => 'irc', 'textMessage' => '')
    );
}
