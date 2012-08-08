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
 * This class defines the default value for a slot, and whether this value would
 * hardlock that slot (no-one can put a talk into that slot), softlock (anyone 
 * can propos a talk for that slot, but it won't be dynamically sorted into that
 * slot), or not locked at all.
 * 
 * @category Object_DefaultSlotType
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Object_DefaultSlotType extends Abstract_GenericObject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
        'strDefaultSlotType' => array('type' => 'varchar', 'length' => 255, 'required' => 'admin', 'render_in_sub_views' => true),
        'lockSlot' => array('type' => 'enum', 'options' => array('hardlock', 'softlock', 'none'), 'required' => 'admin'),
        'lastChange' => array('type' => 'datetime')
    );
    protected $arrTranslations = array(
        'label_strDefaultSlotType' => array('en' => 'Type of slot'),
        'label_lockSlot' => array('en' => 'Slot locking mechanism')
    );
    protected $strDBTable = "defaultslottype";
    protected $strDBKeyCol = "intDefaultSlotTypeID";
    protected $reqAdminToMod = true;
    // Local Object Requirements
    protected $intDefaultSlotTypeID = null;
    protected $strDefaultSlotType = null;
    protected $lockSlot = null;
    protected $lastChange = null;
    
    /**
     * Append a single value containing the key and the string representation
     * of this row.
     *
     * @param array $return The classes' data
     * 
     * @return array
     */
    protected function getCurrent($return)
    {
        $return = parent::getCurrent($return);
        $return['current']['arrDefaultSlotType']['key'] = $this->lockSlot;
        $return['current']['arrDefaultSlotType']['value'] = $this->lockSlot;
        return $return;
    }
}

/**
 * This class defines some default and demo data for the use in demos.
 * 
 * @category Object_DefaultSlotType
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */
class Object_DefaultSlotType_Demo extends Object_DefaultSlotType
{
    protected $arrDemoData = array(
        array('intDefaultSlotTypeID' => 1, 'strDefaultSlotType' => 'Keynote', 'lockSlot' => 'hardlock'),
        array('intDefaultSlotTypeID' => 2, 'strDefaultSlotType' => 'Lunch', 'lockSlot' => 'softlock'),
        array('intDefaultSlotTypeID' => 3, 'strDefaultSlotType' => 'Closing talk', 'lockSlot' => 'hardlock'),
        array('intDefaultSlotTypeID' => 4, 'strDefaultSlotType' => 'Afternoon Tea', 'lockSlot' => 'none')
    );
}