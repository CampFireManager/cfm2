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
 * This class defines the object for PDO to use when retrives data about a talk.
 * 
 * @category Object_Talk
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Object_Talk extends Abstract_GenericObject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
        'strTalkTitle' => array('type' => 'varchar', 'length' => 255),
        'strTalkSummary' => array('type' => 'text'),
        'hasPGContent' =>array('type' => 'tinyint', 'length' => 1),
        'intUserID' => array('type' => 'int', 'length' => 11),
        'intRequestedRoomID' => array('type' => 'int', 'length' => 11),
        'intRequestedSlotID' => array('type' => 'int', 'length' => 11),
        'intRoomID' => array('type' => 'int', 'length' => 11),
        'intSlotID' => array('type' => 'int', 'length' => 11),
        'intTrackID' => array('type' => 'int', 'length' => 11),
        'intLength' => array('type' => 'tinyint', 'length' => 1),
        'jsonLinks' => array('type' => 'text'),
        'isRoomLocked' => array('type' => 'tinyint', 'length' => 1),
        'isSlotLocked' => array('type' => 'tinyint', 'length' => 1),
        'isLocked' => array('type' => 'tinyint', 'length' => 1),
        'jsonResources' => array('type' => 'text'),
        'jsonOtherPresenters' => array('type' => 'text'),
        'lastChange' => array('type' => 'datetime')
    );
    protected $strDBTable = "talk";
    protected $strDBKeyCol = "intTalkID";
    protected $reqCreatorToMod = true;
    // Local Object Requirements
    protected $intTalkID = null;
    protected $strTalkTitle = null;
    protected $hasPGContent = null;
    protected $strTalkSummary = null;
    protected $intUserID = null;
    protected $intRequestedRoomID = null;
    protected $intRequestedSlotID = null;
    protected $intRoomID = null;
    protected $intSlotID = null;
    protected $intTrackID = null;
    protected $intLength = null;
    protected $jsonLinks = null;
    protected $isRoomLocked = 0;
    protected $isSlotLocked = 0;
    protected $isLocked = 0;
    protected $jsonResources = null;
    protected $jsonOtherPresenters = null;
    protected $lastChange = null;

    /**
     * This overloaded function returns the data from the PDO object and adds
     * supplimental data based on linked tables
     * 
     * @return array
     */
    function getSelf()
    {
        $self = parent::getSelf();
        if ($this->isFull() == true) {
            $self['intAttendees'] = Object_Attendee::countByColumnSearch('intTalkID', $this->intTalkID);
            if (Object_Attendee::lastChangeByColumnSearch('intTalkID', $this->intTalkID) > $self['lastChange']) {
                $self['lastChange'] = Object_Attendee::lastChangeByColumnSearch('intTalkID', $this->intTalkID);
            }
            if ($this->intUserID != null && $this->intUserID > 0) {
                $objUser = Object_User::brokerByID($this->intUserID);
                if (is_object($objUser)) {
                    $self['arrUser'] = $objUser->getSelf();
                    $self['arrPresenters'][] = $objUser->getSelf();
                    if ($self['arrUser']['lastChange'] > $self['lastChange']) {
                        $self['lastChange'] = $self['arrUser']['lastChange'];
                    }
                }
            }
            if ($this->intRoomID != null && $this->intRoomID > 0) {
                $objRoom = Object_Room::brokerByID($this->intRoomID);
                if (is_object($objRoom)) {
                    $self['hasExcessAttendees'] = false;
                    if ($self['intAttendees'] > $objRoom->getKey('intCapacity')) {
                        $self['hasExcessAttendees'] = true;
                    }
                    $objRoom->setFull(true);
                    $self['arrRoom'] = $objRoom->getSelf();
                    if ($self['arrRoom']['lastChange'] > $self['lastChange']) {
                        $self['lastChange'] = $self['arrRoom']['lastChange'];
                    }
                }
            }
            if ($this->intSlotID != null && $this->intSlotID > 0) {
                $objSlot = Object_Slot::brokerByID($this->intSlotID);
                if (is_object($objSlot)) {
                    $objSlot->setFull(true);
                    $self['arrSlot_start'] = $objSlot->getSelf();
                    if ($self['arrSlot_start']['lastChange'] > $self['lastChange']) {
                        $self['lastChange'] = $self['arrSlot_start']['lastChange'];
                    }
                    $self['arrSlot_stop'] = $objSlot->getSelf();
                }
                if ($this->intLength > 1) {
                    $objSlot = Object_Slot::brokerByID($this->intSlotID + ($this->intLength - 1));
                    if (is_object($objSlot)) {
                        $objSlot->setFull(true);
                        $self['arrSlot_stop'] = $objSlot->getSelf();
                        if ($self['arrSlot_stop']['lastChange'] > $self['lastChange']) {
                            $self['lastChange'] = $self['arrSlot_stop']['lastChange'];
                        }
                    }
                }
            }
            if ($this->intTrackID != null && $this->intTrackID > 0) {
                $objTrack = Object_Track::brokerByID($this->intTrackID);
                if (is_object($objTrack)) {
                    $self['arrTrack'] = $objTrack->getSelf();
                    if ($self['arrTrack']['lastChange'] > $self['lastChange']) {
                        $self['lastChange'] = $self['arrTrack']['lastChange'];
                    }
                }
            }
            $self['arrLinks'] = json_decode($this->jsonLinks, true);
            $resources = json_decode($this->jsonResources, true);
            foreach ($resources as $resource) {
                $objResource = Object_Resource::brokerByID($resource);
                if (is_object($objResource)) {
                    $arrResource = $objResource->getSelf();
                    $self['arrResources'][] = $arrResource;
                    if ($arrResource['lastChange'] > $self['lastChange']) {
                        $self['lastChange'] = $arrResource['lastChange'];
                    }
                }
            }
            $presenters = json_decode($this->jsonOtherPresenters, true);
            foreach ($presenters as $presenter) {
                $objPresenter = Object_User::brokerByID($presenter);
                if (is_object($objPresenter)) {
                    $arrPresenter = $objPresenter->getSelf();
                    $self['arrPresenters'][] = $arrPresenter;
                    if ($arrPresenter['lastChange'] > $self['lastChange']) {
                        $self['lastChange'] = $arrPresenter['lastChange'];
                    }
                }
            }
            $self['strSlotID'] = 'slot_' . $this->intSlotID;
            $self['strRoomID'] = 'room_' . $this->intRoomID;
        }
        return $self;
    }
    
    /**
     * This function sets the Room and Slot IDs to special "-1" indicators 
     * (unset), it also sets the "Allocated Slot ID" to null, and unlocks the
     * talk.
     * 
     * @return void
     */
    public function unschedule()
    {
        $this->setKey('intRoomID', -1);
        $this->setKey('intSlotID', -1);
        $this->setKey('isLocked', 0);
        $this->setKey('isRoomLocked', 0);
        $this->setKey('isSlotLocked', 0);
        $this->write();
    }
    
    /**
     * This function sets the lock attributes for the talk, the room and the
     * slot. It then triggers the "Fixed Talk" hook.
     * 
     * @return void
     */
    public function fixTalk()
    {
        $this->setKey('isLocked', 1);
        $this->setKey('isRoomLocked', 1);
        $this->setKey('isSlotLocked', 1);
        $this->write();
        $hook = new Base_Hook();
        $hook->triggerHook('fixTalk', $this);
    }
    
    /**
     * This overloaded function ensures that the user is an admin before setting
     * certain key flags. It also automatically triggers the setting of keys when
     * requesting room and slots if you are not an admin.
     *
     * @param string $key   The column name to set the value for
     * @param mixed  $value The value to set
     * 
     * @return mixed 
     */
    public function setKey($key, $value)
    {
        switch ($key) {
        case 'isLocked':
        case 'isRoomLocked':
        case 'isSlotLocked':
        case 'intAssignedSlotID':
            if (! Object_User::isAdmin()) {
                return false;
            }
            break;
        case 'intRoomID':
            if (! Object_User::isAdmin()) {
                $this->setKey('intRequestedRoomID', $value);
            }
            break;
        case 'intSlotID':
            if (! Object_User::isAdmin()) {
                $this->setKey('intRequestedSlotID', $value);
            }
            break;
        }
        return parent::setKey($key, $value);
    }

    /**
     * This function returns an empty grid, with spaces locked where those rooms
     * are locked down (for example, where all static talks are located) or 
     * where a slot is locked down (for example, lunch time, or keynote)
     *
     * @param array $arrSlots            The range of slots
     * @param array $arrRooms            The range of rooms
     * @param array $arrDefaultSlotTypes The range of default slot types
     * 
     * @return array 
     */
    protected static function getGrid($arrSlots, $arrRooms, $arrDefaultSlotTypes)
    {
        // Then layout the grid
        $arrGrid = array();
        foreach ($arrSlots as $intSlotID => $objSlot) {
            foreach ($arrRooms as $objRoom) {
                if ($objSlot->getKey('intDefaultSlotTypeID') != null) {
                    $objSlot = new Object_Slot();
                    $arrGrid[$intSlotID]['isLocked'] = false;
                    if (isset($arrDefaultSlotTypes[$objSlot->getKey('intDefaultSlotTypeID')])) {
                        $arrGrid[$intSlotID]['isLocked'] = $arrDefaultSlotTypes[$objSlot->getKey('intDefaultSlotTypeID')]->getKey('lockSlot');
                    }
                }
                if ($objRoom->getKey('isLocked') != false) {
                    $arrGrid[$intSlotID][$objRoom->getKey('intRoomID')]['isLocked'] = true;
                } else {
                    $arrGrid[$intSlotID][$objRoom->getKey('intRoomID')] = array();
                }
            }
        }
    }
    
    /**
     * This function sorts the talks in order of attendees, giving fractional 
     * weight to the order the talks were created in. It only sorts talks which
     * are not locked and are not "unscheduled" (for example, elected to be 
     * removed from the grid, or where the limbo module is enabled).
     * 
     * @return void
     */
    public static function sortAndPlaceTalksByAttendees()
    {
        $arrSortItems = array();
        $arrNowNext = Object_Slot::getNowAndNext();
        $intNowSlot = $arrNowNext[0];
        $arrSlots = Object_Slot::brokerAll();
        $arrRooms = Object_Room::brokerAllByRoomSize();
        $arrRoomsByID = array();
        foreach ($arrRooms as $room => $objRoom) {
            $arrRoomsByID[$objRoom->getKey('intRoomID')] = $room;
        }

        $arrGrid = self::getGrid($arrSlots, $arrRooms, Object_DefaultSlotType::brokerAll());
        foreach (Object_Talk::brokerAll() as $objTalk) {
            // Clear the spot on the grid for locked talks
            if ($objTalk->getKey('isLocked') == 1) {
                for ($intSlotID = $objTalk->getKey('intSlotID'); $intSlotID < $objTalk->getKey('intSlotID') + $objTalk->getKey('intLength'); $intSlotID++) {
                    $arrGrid[$intSlotID][$arrRoomsByID[$objTalk->getKey('intRoomID')]]['isLocked'] = true;
                }
            } elseif ($objTalk->getKey('intRoomID') != '-1') {
                $objTalk->setFull(true);
                $arrTalk = $objTalk->getSelf();
                if ($objTalk->getKey('intRequestedSlotID') < $intNowSlot) {
                    // If the talk was requested for before now but wasn't given
                    // a chance to be presented, put it in the "reschedule" group.
                    $intUseSlot = 0;
                } else {
                    // Otherwise, try to use the slot it's asked for.
                    $intUseSlot = $objTalk->getKey('intRequestedSlotID');
                }
                $arrSortItems[$intUseSlot][$arrTalk['intAttendees']][$arrTalk['intTalkID']] = $objTalk;
            }
        }
        ksort($arrSortItems);
        foreach ($arrSortItems as $intSlotID => $arrSlotSpaces) {
            if ($intSlotID > 0) {
                krsort($arrSlotSpaces);
                $room = 0;
                foreach ($arrSlotSpaces as $arrTalk) {
                    ksort($arrTalk);
                    foreach ($arrTalk as $objTalk) {
                        if ($room++ >= count($arrRooms)) {
                            $arrSortItems[0][] = $objTalk;
                        } else {
                            // TODO: This doesn't cope with locked rooms later in the grid when this talk is longer than one slot
                            while (isset($arrGrid[$intSlotID][$room]['isLocked'])) {
                                if ($room++ >= count($arrRooms)) {
                                    $arrSortItems[0][] = $objTalk;
                                    continue 2;
                                }
                            }
                            $arrGrid[$intSlotID][$room] = $objTalk;
                            if ($objTalk->getKey('intLength') > 1) {
                                for ($intSlotID = $objTalk->getKey('intSlotID') + 1; $intSlotID < $objTalk->getKey('intSlotID') + $objTalk->getKey('intLength'); $intSlotID++) {
                                    $arrGrid[$intSlotID][$room]['isLocked'] = true;
                                }
                            }
                        }
                    }
                }
            }
        }
        if (isset($arrSortItems[0]) && Container_Config::brokerByID('Schedule talks in the next available slot when this slot is full')->getKey('value', 0) == 1) {
            foreach ($arrSortItems[0] as $talk) {
                $arrSlotSpaces = array();
                foreach ($arrSlotItems as $arrTalkData) {
                    $arrSlotSpaces[$arrTalkData['array']['intAttendees'] - ($arrTalkData['array']['intTalkID']/10000)] = $arrTalkData;
                }
                unset($arrSlotItems);
                krsort($arrSlotSpaces);
                $assigned = false;
                foreach ($arrGrid as $intSlotID => $arrSlots) {
                    if ($intSlotID > $intNowSlot && !isset($arrSlots['isLocked'])) {
                        foreach ($arrSlots as $room => $arrSlot) {
                            if (is_integer($room)) {
                                if (! is_object($arrSlot) && !isset($arrSlot['isLocked'])) {
                                    $arrGrid[$intSlotID][$room] = $talk['object'];
                                    $assigned = true;
                                }
                            }
                        }
                    }
                }
                if ($assigned == false) {
                    $talk['object']->unschedule();
                }
            }
        } else {
            if (isset($arrSortItems[0])) {
                foreach ($arrSortItems[0] as $talk) {
                    $talk['object']->unschedule();
                }
            }
        }
        foreach ($arrGrid as $intSlotID => $arrSlots) {
            foreach ($arrSlots as $room => $objTalk) {
                if ($room == 'isLocked' || is_array($objTalk)) {
                    continue;
                }
                $intRoomID = $arrRooms[$room - 1]->getKey('intRoomID');
                if (0 + $intRoomID > 0 && is_object($objTalk)) {
                    $objTalk->setKey('intRoomID', $intRoomID);
                    $objTalk->setKey('intSlotID', $intSlotID);
                    $objTalk->write();
                }
            }
        }
    }
    
    /**
     * This function unschedules talks based on the number of people attending
     * the talk (calculated), ensuring the minimum number of attendees have 
     * expressed they will be attending that talk.
     *
     * @param array   $talks           The talks to be checked
     * @param integer $intMinAttendees The minimum number of attendees
     * 
     * @return void
     */
    public static function unscheduleBasedOnAttendees($talks, $intMinAttendees = 0)
    {
        foreach ($talks as $talk) {
            if ($talk->getKey('isLocked') == 1) {
                continue;
            } else {
                $talk->setFull(true);
                $data = $talk->getSelf();
                if ($intMinAttendees > $data['intAttendees']) {
                    $talk->unschedule();
                }
            }
        }
    }
}

/**
 * This class defines some default and demo data for the use in demos.
 * 
 * @category Object_Talk
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Object_Talk_Demo extends Object_Talk
{
    protected $arrDemoData = array(
        array('intTalkID' => 1, 'strTalkTitle' => 'Keynote', 'strTalkSummary' => 'A welcome to Barcamps', 'intUserID' => 1, 'intRequestedRoomID' => 1, 'intRequestedSlotID' => 1, 'intRoomID' => 1, 'intSlotID' => 1, 'intTrackID' => null, 'intLength' => 1, 'jsonLinks' => '{"slides":"http:\/\/slideshare.net","twitter":"http:\/\/twitter.com\/"}', 'isLocked' => 1, 'jsonResources' => '[1]', 'jsonOtherPresenters' => '[]'),
        array('intTalkID' => 2, 'strTalkTitle' => 'An introduction to CampFireManager2', 'strTalkSummary' => 'A walk through of how it works, where to get it from and why you should use it at your next conference', 'intUserID' => 2, 'intRequestedRoomID' => 1, 'intRequestedSlotID' => 2, 'intRoomID' => 1, 'intSlotID' => 2, 'intTrackID' => 1, 'intLength' => 1, 'jsonLinks' => '{"code":"http:\/\/www.github.com\/JonTheNiceGuy\/cfm2"}', 'isLocked' => '0', 'jsonResources' => '[]', 'jsonOtherPresenters' => '[]'),
        array('intTalkID' => 3, 'strTalkTitle' => 'An introduction to BarCamp', 'strTalkSummary' => "So, this is your first BarCamp? Glad you're here! This talk explains what BarCamps are, why they are so cool and why you should do a talk!", 'intUserID' => 3, 'intRequestedRoomID' => 2, 'intRequestedSlotID' => 2, 'intRoomID' => 2, 'intSlotID' => 2, 'intTrackID' => 2, 'intLength' => 1, 'jsonLinks' => '[]', 'isLocked' => '0', 'jsonResources' => '[3]', 'jsonOtherPresenters' => '[1]')
    );
}
