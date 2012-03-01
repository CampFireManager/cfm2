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

class Object_DefaultSlotType extends Base_GenericObject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
        'strDefaultSlotType' => array('type' => 'varchar', 'length' => 255),
        'locksSlot' => array('type' => 'enum', 'options' => array('hardlock', 'softlock', 'none'))
    );
    protected $strDBTable = "defaultSlotType";
    protected $strDBKeyCol = "intDefaultSlotTypeID";
    protected $mustBeAdminToModify = true;
    // Local Object Requirements
    protected $intDefaultSlotTypeID = null;
    protected $strDefaultSlotType = null;
    protected $locksSlot = null;
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
        array('intDefaultSlotTypeID' => 1, 'strDefaultSlotType' => 'Keynote', 'locksSlot' => 'hardlock'),
        array('intDefaultSlotTypeID' => 2, 'strDefaultSlotType' => 'Lunch', 'locksSlot' => 'softlock'),
        array('intDefaultSlotTypeID' => 3, 'strDefaultSlotType' => 'Closing talk', 'locksSlot' => 'hardlock'),
        array('intDefaultSlotTypeID' => 4, 'strDefaultSlotType' => 'Afternoon Tea', 'locksSlot' => 'none')
    );
}