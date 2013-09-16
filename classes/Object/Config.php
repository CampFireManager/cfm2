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
 * @category Object_Config
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */

class Object_Config extends Abstract_GenericObject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
        'key' => array('type' => 'varchar', 'length' => 255, 'required' => 'admin'),
        'value' => array('type' => 'text', 'required' => 'admin'),
        'lastChange' => array('type' => 'datetime')
    );
    protected $arrTranslations = array(
        'label_key' => array('en' => 'Variable name'),
        'label_value' => array('en' => 'Value')
    );
    protected $strDBTable = "config";
    protected $reqAdminToMod = true;
    // Local Object Requirements
    protected $key = null;
    protected $value = null;
    protected $isLocal = false;
    protected $lastChange = null;
    
    /**
     * This function overloads the parent write function - ensuring the value
     * is not locally stored first before performing the write action.
     * 
     * @return void
     */
    public function write()
    {
        if (! $this->isLocal) {
            parent::write();
        }
    }
    
    /**
     * This function overloads the parent create function - ensuring the value
     * is not locally stored first before performing the create action.
     * 
     * @return void
     */
    public function create()
    {
        if (! $this->isLocal) {
            parent::create();
        }
    }

    /**
     * This constructor permits the insertion of data into the object, for
     * locally stored (i.e. configuration rather than database) values.
     *
     * @param array  $values     The optional values to insert into the object
     * @param string $lastChange The optional date this value was created. For
     * config file based values, this will be the date the config file was last
     * amended.
     * 
     * @return Object_Config
     */
    public function __construct($values = null, $lastChange = null)
    {
        if ($values != null && is_array($values) && count($values) > 0) {
            $this->isLocal = true;
            foreach ($values as $key => $value) {
                $this->setKey($key, $value);
            }
            $this->setKey('lastChange', $lastChange);
        } else {
            parent::__construct();
        }
    }
    
}

/**
 * This class defines some default and demo data for the use in demos.
 * 
 * @category Object_Config
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */
class Object_Config_Demo extends Object_Config
{
    protected $arrDemoData = array(
        array('key' => 'Site_Name', 'value' => 'A Demo Site'),
        array('key' => 'Public_Url', 'value' => 'http://jontheniceguy.pagekite.me/cfm2/')
    );

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
