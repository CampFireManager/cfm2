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
 * This class defines the object for PDO to use when retrives data about a Change.
 * 
 * @category Object_ChangeLog
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */

class Object_ChangeLog extends Abstract_GenericObject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
        'enumChangeType' => array('type' => 'enum', 'options' => array('Create', 'Update', 'Delete')),
        'objectType' => array('type' => 'varchar', 'length' => 255),
        'objectID' => array('type' => 'int', 'length' => 11),
        'intActorID' => array('type' => 'int', 'length' => 11),
        'jsonChanges' => array('type' => 'text'),
        'lastChange' => array('type' => 'datetime')
    );
    protected $arrTranslations = array(
    );
    protected $strDBTable = "changelog";
    protected $strDBKeyCol = "intChangeID";
    protected $reqAdminToMod = true;
    // Local Object Requirements
    protected $intChangeID = null;
    protected $enumChangeType = null;
    protected $objectType = null;
    protected $objectID = null;
    protected $intActorID = null;
    protected $jsonChanges = null;
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
     * @param string  $column    The column to search
     * @param string  $value     The value to look for.
     * @param boolean $inverse   Look for anything but this value
     * @param boolean $json      Look for a JSON encoded string
     * @param integer $count     The number of records to return
     * @param string  $direction The SQL direction to process
     * 
     * @return array The array of objects matching this search
     */
    public static function brokerByColumnSearch($column = null, $value = null, $inverse = false, $json = false, $count = null, $direction = 'ASC')
    {
        if (Object_User::isSystem()) {
            return parent::brokerByColumnSearch($column, $value, $inverse, $json, $count, $direction);
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
            throw new BadMethodCallException('It is not permitted to directly create input actions');
        } else {
            parent::__construct();
        }
    }
    
    /**
     * This overriden function does nothing - a ChangeLog can't be changed - 
     * only read, and even then only at the Database Level.
     * 
     * @return void
     */
    public function writeChangeLog() {
        // Do Nothing!
    }
    
}

/**
 * This class defines some default and demo data for the use in demos.
 * 
 * @category Object_ChangeLog
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */

class Object_ChangeLog_Demo extends Object_ChangeLog
{
    protected $arrDemoData = array();
}
