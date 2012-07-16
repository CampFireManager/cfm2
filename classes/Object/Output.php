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

class Object_Output extends Abstract_GenericObject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
        'strReceiver' => array('type' => 'varchar', 'length' => 255),
        'strInterface' => array('type' => 'varchar', 'length' => 255),
        'textMessage' => array('type' => 'text'),
        'isActioned' => array('type' => 'tinyint', 'length' => 1),
        'lastChange' => array('type' => 'datetime')
    );
    protected $strDBTable = "output";
    protected $strDBKeyCol = "intOutputID";
    protected $reqAdminToMod = true;
    // Local Object Requirements
    protected $intInputID = null;
    protected $strReceiver = null;
    protected $strInterface = null;
    protected $textMessage = null;
    protected $isActioned = false;
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
     * @return object UserObject for intUserID
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
            return array();
        }
    }
    
    /**
     * Ensure that all database items are backed up before processing.
     *
     * This is our usual construct method for all extended classes.
     *
     * @return object This class.
     */
    public static function __construct()
    {
        if (! Object_User::isSystem()) {
            throw new Exception('It is not permitted to directly create output actions');
        } else {
            parent::__construct();
        }
    }
    
    /**
     * A function to wrapper responding to Object_Input messages
     *
     * @param Object $objInput   The Input data to process
     * @param String $strMessage The message to reply with
     * 
     * @return void
     */
    public static function replyToInput($objInput, $strMessage)
    {
        if (is_object($objInput) 
            && get_class($objInput) == 'Object_Input' 
            && $strMessage != ''
        ) {
            $objOutput = new Object_Output();
            $objOutput->setKey('strReceiver', $objInput->getKey('strSender'));
            $objOutput->setKey('strInterface', $objInput->getKey('strInterface'));
            $objOutput->setKey('textMessage', $strMessage);
            $objOutput->create();
        }
    }
}

/**
 * This class defines some default and demo data for the use in demos.
 * 
 * @category Object_Output
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */
class Object_Output_Demo extends Object_Input
{
    protected $arrDemoData = array(
        array('strReceiver' => '+447000000001', 'strInterface' => 'gammu', 'textMessage' => '')
    );
}
