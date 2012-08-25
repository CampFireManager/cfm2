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
 * This class retrieves data about the daemons.
 * 
 * @category Object_Daemon
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */

class Object_Daemon extends Abstract_GenericObject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
        'strDaemon' => array('type' => 'varchar', 'length' => 255),
        'intInboundCounter' => array('type' => 'integer', 'length' => 11),
        'intOutboundCounter' => array('type' => 'integer', 'length' => 11),
        'intUniqueCounter' => array('type' => 'integer', 'length' => 11),
        'intScope' => array('type' => 'integer', 'length' => 3),
        'lastUsedSuccessfully' => array('type' => 'datetime'),
        'lastChange' => array('type' => 'datetime')
    );
    protected $strDBTable = "daemon";
    protected $strDBKeyCol = "intDaemonID";
    protected $reqAdminToMod = true;
    // Local Object Requirements
    protected $intDaemonID = null;
    protected $strDaemon = null;
    protected $intInboundCounter = null;
    protected $intOutboundCounter = null;
    protected $intUniqueCounter = false;
    protected $intScope = null;
    protected $lastUsedSuccessfully = null;
    protected $lastChange = null;
    
    /**
     * Ensure that all database items are backed up before processing.
     *
     * This is our usual construct method for all extended classes.
     *
     * @return Object_Output
     */
    public function __construct()
    {
        if (! Object_User::isSystem()) {
            throw new Exception('It is not permitted to directly create daemon actions');
        } else {
            parent::__construct();
        }
    }
}

/**
 * This class defines some default and demo data for the use in demos.
 * 
 * @category Object_Daemon
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */
class Object_Daemon_Demo extends Object_Daemon
{
    protected $arrDemoData = array(
        array('strDaemon' => 'Glue_TwitterAPI-1', 'intInboundCounter' => 0, 'intOutboundCounter' => 0, 'intUniqueCounter' => 0, 'intScope' => 350, 'lastUsedSuccessfully' => '1970-01-01 00:00:00')
    );
}
