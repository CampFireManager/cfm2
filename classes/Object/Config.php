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
 * This class obtains manipulates all the configuration data for the service. It
 * handles local configuration (per-server), global configuration (per-site) and
 * secure configuration (api keys, password salts etc.)
 *
 * @category Object_Config
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Object_Config extends Abstract_GenericObject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
    	'key' => array('type' => 'varchar', 'length' => 255),
        'value' => array('type' => 'text'),
        'lastChange' => array('type' => 'datetime')
    );
    protected $strDBTable = "config";
    protected $mustBeAdminToModify = true;
    // Local Object Requirements
    protected $key = null;
    protected $value = null;
    protected $isLocal = false;
    protected $lastChange = null;
    
    public function write()
    {
        if (! $this->isLocal) {
            parent::write();
        }
    }
    
    public function create()
    {
        if (! $this->isLocal) {
            parent::create();
        }
    }
    
    public function __construct($values = null)
    {
        parent::__construct();
        if ($values != null && is_array($values) && count($values) > 0) {
            $this->isLocal = true;
            foreach ($values as $key => $value) {
                $this->setKey($key, $value);
            }
        }
    }
}

/**
 * This class defines some default and demo data for the use in demos.
 * 
 * @category Object_Attendee
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */
class Object_Config_Demo extends Object_Attendee
{
    protected $arrDemoData = array(
        array('key' => 'Site Name', 'value' => 'A Demo Site'),
    );
}
