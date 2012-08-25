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
 * This class defines the object for PDO to use when retrives data about a slot.
 * 
 * @category Object_Slot
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */

class Object_Slot extends Abstract_GenericObject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
        'dateStart' => array('type' => 'date', 'optional' => 'admin', 'input_type' => 'date', 'render_in_sub_views' => true),
        'timeStart' => array('type' => 'time', 'required' => 'admin', 'input_type' => 'time', 'render_in_sub_views' => true),
        'dateEnd' => array('type' => 'date', 'optional' => 'admin', 'input_type' => 'date'),
        'timeEnd' => array('type' => 'time', 'required' => 'admin', 'input_type' => 'time', 'render_in_sub_views' => true),
        'intDefaultSlotTypeID' => array('type' => 'integer', 'length' => 11, 'optional' => 'admin', 'source' => 'DefaultSlotType'),
        'lastChange' => array('type' => 'datetime')
    );
    protected $arrTranslations = array(
        'label_dateStart' => array('en' => 'Date this Slot starts'),
        'label_timeStart' => array('en' => 'Time this Slot starts'),
        'label_dateEnd' => array('en' => 'Date this Slot ends'),
        'label_timeEnd' => array('en' => 'Time this Slot ends'),
        'label_intDefaultSlotTypeID' => array('en' => 'Slot is labelled for a particular purpose'),
        'label_new_intDefaultSlotTypeID' => array('en' => 'Default Slot Type')
    );
    protected $strDBTable = "slot";
    protected $strDBKeyCol = "intSlotID";
    protected $reqAdminToMod = true;
    // Local Object Requirements
    protected $intSlotID = null;
    protected $dateStart = null;
    protected $timeStart = null;
    protected $dateEnd = null;
    protected $timeEnd = null;
    protected $intDefaultSlotTypeID = null;
    protected $isAvailable = true;
    protected $lastChange = true;
    // Calculated Values
    protected $isNow = null;
    protected $isNext = null;
    protected $isStillToCome = null;

    /**
     * This overloaded function returns the data from the PDO object and adds
     * supplimental data based on linked tables
     * 
     * @return array
     */
    function getData()
    {
        $self = parent::getData();
        if ((string) $this->dateStart == '') {
            $self['dateStart'] = date('Y-m-d');
            $this->setKey('dateStart', date('Y-m-d'));
        }
        if ((string) $this->dateEnd == '') {
            if ($this->timeStart > $this->timeEnd) {
                $self['dateEnd'] = date('Y-m-d', strtotime("tomorrow"));
                $this->setKey('dateEnd', date('Y-m-d', strtotime("tomorrow")));
            } else {
                $self['dateEnd'] = date('Y-m-d');
                $this->setKey('dateEnd', date('Y-m-d'));
            }
        }
        $self['datetimeStart'] = $self['dateStart'] . 'T' . $self['timeStart'] . Container_Config::brokerByID('TZ', 'Z')->getKey('value');
        $self['epochStart'] = strtotime($self['datetimeStart']);
        $self['datetimeEnd'] = $self['dateEnd'] . 'T' . $self['timeEnd'] . Container_Config::brokerByID('TZ', 'Z')->getKey('value');
        $self['epochEnd'] = strtotime($self['datetimeEnd']);
        $self['datetimeDuration'] = $self['datetimeStart'] . '/' . $self['datetimeEnd'];
        $self['isNow'] = $this->isNow;
        $self['isNext'] = $this->isNext;
        $self['isStillToCome'] = $this->isStillToCome;

        if ($this->isFull() == true) {
            if ($this->intDefaultSlotTypeID != null && $this->intDefaultSlotTypeID > 0) {
                $objDefaultSlotType = Object_DefaultSlotType::brokerByID($this->intDefaultSlotTypeID);
                if (is_object($objDefaultSlotType)) {
                    $self['arrDefaultSlotType'] = $objDefaultSlotType->getSelf();
                    if ($self['arrDefaultSlotType']['epochLastChange'] > $self['epochLastChange']) {
                        $self['epochLastChange'] = $self['arrDefaultSlotType']['epochLastChange'];
                    }
                }
            }
        }
        Base_Response::setLastModifiedTime($self['epochLastChange']);
        $self['lastChange'] = date('Y-m-d H:i:s', $self['epochLastChange']);
        return $self;
    }

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
        if (date("Y-m-d") != $this->dateStart) {
            $return['current']['value'] = $this->dateStart . ' ' . $this->timeStart . ' - ' . $this->timeEnd;
        } else {
            $return['current']['value'] = $this->timeStart . ' - ' . $this->timeEnd;
        }
        return $return;
    }
    
    /**
     * Get the intSlotID's of the "Now slot" and "Next slot"
     * 
     * @param string $strNow The value to use when searching for the now/next values
     *
     * @return array
     */
    public static function getNowAndNext($strNow = null)
    {
        $arrSlots = self::brokerAll(false);
        if ($strNow == null) {
            $strNow = '+0 minutes';
        }
        $intNowSlot = "0";
        $intNextSlot = "0";
        foreach ($arrSlots as $objSlot) {
            $slot = $objSlot->getSelf();
            if ($intNextSlot == 0) {
                $intNextSlot = $slot['intSlotID'];
            }
            if (date('YmdHi', strtotime($slot['dateStart'] . ' ' . $slot['timeStart'])) <= date('YmdHi', strtotime($strNow))) {
                $intNowSlot = $slot['intSlotID'];
                $intNextSlot = "0";
            }
        }
        foreach ($arrSlots as $objSlot) {
            if ($objSlot->getKey('intSlotID') == $intNowSlot) {
                $objSlot->isNow = true;
            } elseif ($objSlot->getKey('intSlotID') == $intNextSlot) {
                $objSlot->isNext = true;
                $objSlot->isStillToCome = true;
            } elseif ($objSlot->getKey('intSlotID') > $intNextSlot) {
                $objSlot->isStillToCome = true;
            }
        }
        return array($intNowSlot, $intNextSlot);
    }
    
    /**
     * Overloaded brokerAll to add the now and next time
     *
     * @param boolean $getNowNext Add the Now/Next time
     * @param string  $strNow     Time to add
     * 
     * @return array
     */
    public static function brokerAll($getNowNext = true, $strNow = '+0 minutes')
    {
        if ($getNowNext == true) {
            self::getNowAndNext($strNow);
        }
        return parent::brokerAll();
    }
    
    /**
     * Overloaded function to get certain calculated values.
     *
     * @param string $key Key to search for
     * 
     * @return string
     */
    public function getKey($key = null)
    {
        if ($key == 'isNow') {
            return $this->isNow;
        } elseif ($key == 'isNext') {
            return $this->isNext;
        } elseif ($key == 'isStillToCome') {
            return $this->isStillToCome;
        } elseif ($key == 'strSlot') {
            return trim($this->dateStart . ' ' . $this->timeStart);
        } else {
            return parent::getKey($key);
        }
    }
}

/**
 * This class defines some default and demo data for the use in demos.
 * 
 * @category Object_Slot
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */

class Object_Slot_Demo extends Object_Slot
{
    protected $arrDemoData = array(
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