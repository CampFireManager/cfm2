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
 * This class defines the object for PDO to use when retrives data about a slot.
 * 
 * @category Object_Slot
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Object_Slot extends Abstract_GenericObject
{
    // Generic Object Requirements
    protected $_arrDBItems = array(
        'dateStart' => array('type' => 'date'),
        'timeStart' => array('type' => 'time'),
        'dateEnd' => array('type' => 'date'),
        'timeEnd' => array('type' => 'time'),
        'intDefaultSlotTypeID' => array('type' => 'integer', 'length' => 11),
        'lastChange' => array('type' => 'datetime')
    );
    protected $_strDBTable = "slot";
    protected $_strDBKeyCol = "intSlotID";
    protected $_reqAdminToMod = true;
    // Local Object Requirements
    protected $intSlotID = null;
    protected $dateStart = null;
    protected $timeStart = null;
    protected $dateEnd = null;
    protected $timeEnd = null;
    protected $intDefaultSlotTypeID = null;
    protected $isAvailable = true;
    protected $lastChange = true;

    /**
     * This overloaded function returns the data from the PDO object and adds
     * supplimental data based on linked tables
     * 
     * @return array
     */
    function getSelf()
    {
        $_self = parent::getSelf();
        if ((string) $this->dateStart == '') {
            $_self['dateStart'] = date('Y-m-d');
            if ((string) $this->dateEnd == '') {
                if ($this->timeStart > $this->timeEnd) {
                    $_self['dateEnd'] = date('Y-m-d', strtotime("tomorrow"));
                } else {
                    $_self['dateEnd'] = date('Y-m-d');
                }
            }
        }
        $_self['datetimeStart'] = $_self['dateStart'] . 'T' . $_self['timeStart'] . Container_Config::brokerByID('TZ_Offset', 'Z')->getKey('value');
        $_self['datetimeEnd'] = $_self['dateEnd'] . 'T' . $_self['timeEnd'] . Container_Config::brokerByID('TZ_Offset', 'Z')->getKey('value');
        $_self['datetimeDuration'] = $_self['datetimeStart'] . '/' . $_self['datetimeEnd'];
        
        if ($this->isFull() == true) {
            if ($this->intDefaultSlotTypeID != null && $this->intDefaultSlotTypeID > 0) {
                $objDefaultSlotType = Object_DefaultSlotType::brokerByID($this->intDefaultSlotTypeID);
                if (is_object($objDefaultSlotType)) {
                    $_self['arrDefaultSlotType'] = $objDefaultSlotType->getSelf();
                    if ($_self['arrDefaultSlotType']['lastChange'] > $_self['lastChange']) {
                        $_self['lastChange'] = $_self['arrDefaultSlotType']['lastChange'];
                    }
                }
            }
        }
        return $_self;
    }

    /**
     * Get the intSlotID's of the "Now slot" and "Next slot"
     * 
     * @todo Edge case - outside of this slot, before next slot ... no "Now" or "Next"
     *
     * @return array
     */
    public static function getNowAndNext()
    {
        $arrSlots = self::brokerAll();
        $now = null;
        $next = null;
        foreach ($arrSlots as $objSlot) {
            $slot = $objSlot->getSelf();
            if (date('YmdHi', strtotime($slot['dateStart'] . ' ' . $slot['timeStart'])) <= date('YmdHi')
                && date('YmdHi', strtotime($slot['dateEnd'] . ' ' . $slot['timeEnd'])) >= date('YmdHi')
                || ($now == null && date('YmdHi') <= date('YmdHi', strtotime($slot['dateStart'] . ' ' . $slot['timeStart'])))
                || ($now != null && $next == null)
            ) {
                if ($now == null) {
                    $now = $slot['intSlotID'];
                } else {
                    $next = $slot['intSlotID'];
                }
            }
        }
        return array($now, $next);
    }
}

/**
 * This class defines some default and demo data for the use in demos.
 * 
 * @category Object_Slot
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Object_Slot_Demo extends Object_Slot
{
    protected $_arrDemoData = array(
        array('intSlotID' => 1, 'dateStart' => '', 'timeStart' => '09:00', 'dateEnd' => '', 'timeEnd' => '09:45', 'intDefaultSlotTypeID' => 1),
        array('intSlotID' => 2, 'dateStart' => '', 'timeStart' => '10:00', 'dateEnd' => '', 'timeEnd' => '10:45', 'intDefaultSlotTypeID' => null),
        array('intSlotID' => 3, 'dateStart' => '', 'timeStart' => '11:00', 'dateEnd' => '', 'timeEnd' => '11:45', 'intDefaultSlotTypeID' => null),
        array('intSlotID' => 4, 'dateStart' => '', 'timeStart' => '12:00', 'dateEnd' => '', 'timeEnd' => '12:45', 'intDefaultSlotTypeID' => 2),
        array('intSlotID' => 5, 'dateStart' => '', 'timeStart' => '13:00', 'dateEnd' => '', 'timeEnd' => '13:45', 'intDefaultSlotTypeID' => null),
        array('intSlotID' => 6, 'dateStart' => '', 'timeStart' => '14:00', 'dateEnd' => '', 'timeEnd' => '14:45', 'intDefaultSlotTypeID' => null),
        array('intSlotID' => 7, 'dateStart' => '', 'timeStart' => '15:00', 'dateEnd' => '', 'timeEnd' => '15:45', 'intDefaultSlotTypeID' => null),
        array('intSlotID' => 8, 'dateStart' => '', 'timeStart' => '16:00', 'dateEnd' => '', 'timeEnd' => '16:45', 'intDefaultSlotTypeID' => null),
        array('intSlotID' => 9, 'dateStart' => '', 'timeStart' => '17:00', 'dateEnd' => '', 'timeEnd' => '17:45', 'intDefaultSlotTypeID' => 3),
        array('intSlotID' => 10, 'dateStart' => '', 'timeStart' => '18:00', 'dateEnd' => '', 'timeEnd' => '18:45', 'intDefaultSlotTypeID' => null),
        array('intSlotID' => 11, 'dateStart' => '', 'timeStart' => '19:00', 'dateEnd' => '', 'timeEnd' => '19:45', 'intDefaultSlotTypeID' => null),
        array('intSlotID' => 12, 'dateStart' => '', 'timeStart' => '20:00', 'dateEnd' => '', 'timeEnd' => '20:45', 'intDefaultSlotTypeID' => null),
        array('intSlotID' => 13, 'dateStart' => '', 'timeStart' => '21:00', 'dateEnd' => '', 'timeEnd' => '21:45', 'intDefaultSlotTypeID' => null),
        array('intSlotID' => 14, 'dateStart' => '', 'timeStart' => '22:00', 'dateEnd' => '', 'timeEnd' => '22:45', 'intDefaultSlotTypeID' => null),
        array('intSlotID' => 15, 'dateStart' => '', 'timeStart' => '23:00', 'dateEnd' => '', 'timeEnd' => '23:45', 'intDefaultSlotTypeID' => null),
    );
}