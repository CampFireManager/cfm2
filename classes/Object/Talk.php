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
        'strTalk'             => array('type' => 'varchar', 'length' => 255, 'required' => 'user', 'render_in_sub_views' => true),
        'strTalkSummary'      => array('type' => 'text', 'optional' => 'user'),
        'hasNsfwMaterial'     => array('type' => 'tinyint', 'length' => 1, 'required' => 'user', 'default_value' => "0"),
        'intUserID'           => array('type' => 'int', 'length' => 11, 'required' => 'worker', 'source' => 'User'),
        'intRequestedRoomID'  => array('type' => 'int', 'length' => 11),
        'intRequestedSlotID'  => array('type' => 'int', 'length' => 11),
        'intRoomID'           => array('type' => 'int', 'length' => 11, 'optional' => 'admin', 'source' => 'Room', 'value_for_any' => '-1'),
        'intSlotID'           => array('type' => 'int', 'length' => 11, 'required' => 'user', 'source' => 'Slot', 'must_have_as_true' => 'isStillToCome'),
        'intLength'           => array('type' => 'int', 'length' => 1, 'required' => 'user', 'default_value' => 1),
        'jsonLinks'           => array('type' => 'text', 'optional' => 'user', 'array' => 'arrLinks'),
        'isRoomLocked'        => array('type' => 'tinyint', 'length' => 1),
        'isSlotLocked'        => array('type' => 'tinyint', 'length' => 1),
        'isLocked'            => array('type' => 'tinyint', 'length' => 1, 'required' => 'admin'),
        'jsonOtherPresenters' => array('type' => 'text', 'optional' => 'user', 'source' => 'User', 'array' => 'arrPresenters'),
        'lastChange'          => array('type' => 'datetime')
    );
    protected $arrTranslations = array(
        'label_strTalk' => array('en' => 'Talk Title'),
        'label_strTalkSummary' => array('en' => 'Talk Summary'),
        'label_new_strTalkSummary' => array('en' => 'A short paragraph describing your talk'),
        'label_new_hasNsfwMaterial' => array('en' => 'Does this talk contain content which may be unsuitable for minors'),
        'label_hasNsfwMaterial' => array('en' => 'Contains content which may be unsuitable for minors'),
        'label_new_intUserID' => array('en' => 'Proposing User'),
        'label_new_intRoomID' => array('en' => 'Room Requested'),
        'label_new_intSlotID' => array('en' => 'Slot Requested'),
        'label_intSlotID' => array('en' => 'Slot Allocated'),
        'label_intLength' => array('en' => 'Talk Length in slots'),
        'label_jsonLinks' => array('en' => 'Associated Links'),
        'label_new_jsonLinks' => array('en' => 'Associated Links (in the format: Twitter:http://twitter.com/yourusername or Blog:http://my.blog.com)'),
        'label_new_isLocked' => array('en' => 'Lock this talk to this Room and Slot'),
        'label_isLocked' => array('en' => 'Talk fixed'),
        'label_jsonOtherPresenters' => array('en' => 'Other Presenters'),
        'label_new_jsonOtherPresenters' => array('en' => 'Other Presenters'),
        'label_arrAttendee' => array('en' => '(Potentially) Attending people')
    );
    protected $strDBTable          = "talk";
    protected $strDBKeyCol         = "intTalkID";
    protected $reqCreatorToMod     = true;
    // Local Object Requirements
    protected $intTalkID           = null;
    protected $strTalk             = null;
    protected $hasNsfwMaterial     = null;
    protected $strTalkSummary      = null;
    protected $intUserID           = null;
    protected $intRequestedRoomID  = null;
    protected $intRequestedSlotID  = null;
    protected $intRoomID           = null;
    protected $intSlotID           = null;
    protected $intLength           = null;
    protected $jsonLinks           = null;
    protected $isRoomLocked        = 0;
    protected $isSlotLocked        = 0;
    protected $isLocked            = 0;
    protected $jsonOtherPresenters = null;
    protected $lastChange          = null;

    /**
     * Append the translated labels to the returned data for this class.
     *
     * @param array $return The classes' data
     * 
     * @return array
     */
    protected function getLabels($return)
    {
        $return = parent::getLabels($return);
        if (isset($this->arrTranslations['label_arrAttendee'])) {
            $return['labels']['arrAttendee'] = Base_Response::translate($this->arrTranslations['label_arrAttendee']);
        }
        return $return;
    }

    /**
     * This overloaded function returns the data from the PDO object and adds
     * supplimental data based on linked tables
     * 
     * @return array
     */
    function getData()
    {
        $me = Object_User::brokerCurrent();
        $self = parent::getData();
        if ($this->isFull() == true) {
            if (is_object($me) && $this->intUserID == $me->getPrimaryKeyValue()) {
                $self['isPresenting'] = true;
            } else {
                $self['isPresenting'] = false;
            }
            $arrAttendee = Object_Attendee::brokerByColumnSearch('intTalkID', $this->intTalkID);
            $self['arrAttendee'] = array();
            $self['isAttending'] = false;
            foreach ($arrAttendee as $intAttendeeID => $objAttendee) {
                $self['arrAttendee'][$intAttendeeID] = $objAttendee->getSelf(true);
                if (is_object($me) && $objAttendee->getKey('intUserID') == $me->getPrimaryKeyValue()) {
                    $self['isAttending'] = $intAttendeeID;
                }
            }
            $self['intAttendees'] = count($self['arrAttendee']);
            if (strtotime(Object_Attendee::lastChangeByColumnSearch('intTalkID', $this->intTalkID)) > $self['epochLastChange']) {
                $self['epochLastChange'] = strtotime(Object_Attendee::lastChangeByColumnSearch('intTalkID', $this->intTalkID));
            }
            if ($this->intUserID != null && $this->intUserID > 0) {
                $objUser = Object_UserProposer::brokerByID($this->intUserID);
                if (is_object($objUser)) {
                    $self['arrUser'] = $objUser->getSelf();
                    $self['arrPresenters'][] = $objUser->getSelf();
                    if ($self['arrUser']['epochLastChange'] > $self['epochLastChange']) {
                        $self['epochLastChange'] = $self['arrUser']['epochLastChange'];
                    }
                }
            }
            
            if ($this->intRoomID != null && $this->intRoomID > 0) {
                $objRoom = Object_Room::brokerByID($this->intRoomID);
                if (is_object($objRoom)) {
                    $self['hasExcessAttendees'] = false;
                    if ($self['intAttendees'] > ($objRoom->getKey('intCapacity') * (Container_Config::brokerByID('Capacity Alert Percentage', '75')->getKey('value') / 100))) {
                        $self['hasExcessAttendees'] = true;
                    }
                    $objRoom->setFull(true);
                    $self['arrRoom'] = $objRoom->getSelf();
                    if ($self['arrRoom']['epochLastChange'] > $self['epochLastChange']) {
                        $self['epochLastChange'] = $self['arrRoom']['epochLastChange'];
                    }
                }
            }
            
            if ($this->intSlotID != null && $this->intSlotID > 0) {
                $objSlot = Object_Slot::brokerByID($this->intSlotID);
                if (is_object($objSlot)) {
                    $objSlot->setFull(true);
                    $self['arrSlot'] = $objSlot->getSelf();
                    if ($self['arrSlot']['epochLastChange'] > $self['epochLastChange']) {
                        $self['epochLastChange'] = $self['arrSlot']['epochLastChange'];
                    }
                }
                if ($this->intLength > 1) {
                    $objSlot = Object_Slot::brokerByID($this->intSlotID + ($this->intLength - 1));
                    if (is_object($objSlot)) {
                        $objSlot->setFull(true);
                        $self['arrSlot']['dateEnd'] = $arrSlot_stop['dateEnd'];
                        $self['arrSlot']['timeEnd'] = $arrSlot_stop['timeEnd'];
                        $self['arrSlot']['datetimeEnd'] = $self['dateEnd'] . 'T' . $self['timeEnd'] . Container_Config::brokerByID('TZ', 'Z')->getKey('value');
                        $self['arrSlot']['datetimeDuration'] = $self['datetimeStart'] . '/' . $self['datetimeEnd'];

                        if ($arrSlot_stop['epochLastChange'] > $self['epochLastChange']) {
                            $self['epochLastChange'] = $arrSlot['epochLastChange'];
                        }
                        if ($arrSlot_stop['lastChange'] > $self['lastChange']) {
                            $self['lastChange'] = $arrSlot_stop['lastChange'];
                        }
                    }
                }
            }
            $arrLinks = json_decode($this->jsonLinks, true);
            if (! is_array($arrLinks)) {
                $arrLinks = array();
            }
            foreach ($arrLinks as $key => $value) {
                if ($value != '' && $value != '[]') {
                    $self['arrLinks'][$key] = $value;
                }
            }
                        
            $presenters = json_decode($this->jsonOtherPresenters, true);
            if (!is_array($presenters)) {
                $presenters = array();
            }
            foreach ($presenters as $presenter) {
                $objPresenter = Object_UserPresenter::brokerByID($presenter);
                if (is_object($objPresenter)) {
                    if (is_object($me) && $presenter == $me->getPrimaryKeyValue()) {
                        $self['isPresenting'] = true;
                    }
                    $arrPresenter = $objPresenter->getSelf();
                    $self['arrPresenters'][] = $arrPresenter;
                    if ($arrPresenter['epochLastChange'] > $self['epochLastChange']) {
                        $self['epochLastChange'] = $arrPresenter['epochLastChange'];
                    }
                }
            }
            
            $self['strSlotID'] = 'slot_' . $this->intSlotID;
            $self['strRoomID'] = 'room_' . $this->intRoomID;
        }
        Base_Response::setLastModifiedTime($self['epochLastChange']);
        $self['lastChange'] = date('Y-m-d H:i:s', $self['epochLastChange']);
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
        if (Container_Config::brokerByID('Schedule Only In This Slot', '0')->getKey('value') != '0') {
            $this->setKey('intSlotID', -1);
        }
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
        return $arrGrid;
    }

    /**
     * This function locks all unfixed talks occurring before this slot.
     * 
     * @param String $strNow Optional time to lock talks before
     * 
     * @return void
     */
    public static function lockTalks($strNow = null)
    {
        $arrNowNext = Object_Slot::getNowAndNext($strNow);
        $intNowSlot = $arrNowNext[0];
        foreach (Object_Talk::brokerAll() as $objTalk) {
            if ($objTalk->getKey('intSlotID') != -1 
                && $objTalk->getKey('intSlotID') <= $intNowSlot
            ) {
                $objTalk->fixTalk();
            }
        }
    }
    
    /**
     * This function sorts the talks in order of attendees, giving fractional 
     * weight to the order the talks were created in. It only sorts talks which
     * are not locked and are not "unscheduled" (for example, elected to be 
     * removed from the grid, or where the limbo module is enabled).
     * 
     * @param string $strNow The time to action as Now/Next (Unit testing 
     * purposes only
     * 
     * @return void
     */
    public static function sortAndPlaceTalksByAttendees($strNow = null)
    {
        $arrSortItems = array();
        $arrNowNext = Object_Slot::getNowAndNext($strNow);
        $intNowSlot = $arrNowNext[0];
        $arrSlots = Object_Slot::brokerAll();
        $arrRooms = Object_Room::brokerAllByRoomSize();
        $arrRoomsByID = array();
        foreach ($arrRooms as $room => $objRoom) {
            $arrRoomsByID[$objRoom->getKey('intRoomID')] = $room;
        }
        $arrDefaults = Object_DefaultSlotType::brokerAll();

        $arrGrid = Object_Talk::getGrid($arrSlots, $arrRooms, $arrDefaults);
        $arrTalks = Object_Talk::brokerAll();
        foreach ($arrTalks as $objTalk) {
            // Clear the spot on the grid for locked talks
            if ($objTalk->getKey('isLocked') == 1) {
                for ($intSlotID = $objTalk->getKey('intSlotID'); $intSlotID < $objTalk->getKey('intSlotID') + $objTalk->getKey('intLength'); $intSlotID++) {
                    if ($objTalk->getKey('intRoomID') > 0) {
                        $arrGrid[$intSlotID][$arrRoomsByID[$objTalk->getKey('intRoomID')]]['isLocked'] = true;
                    }
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
                foreach ($arrSlotSpaces as $intAttendees => $arrTalk) {
                    ksort($arrTalk);
                    foreach ($arrTalk as $intTalkID => $objTalk) {
                        if ($room++ >= count($arrRooms)) {
                            $arrSortItems[0][$intAttendees][$intTalkID] = $objTalk;
                        } else {
                            // TODO: This doesn't cope with locked rooms later in the grid when this talk is longer than one slot
                            while (isset($arrGrid[$intSlotID][$room]['isLocked'])) {
                                if ($room++ >= count($arrRooms)) {
                                    $arrSortItems[0][$intAttendees][$intTalkID] = $objTalk;
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
        if (isset($arrSortItems[0]) 
            && count($arrSortItems[0]) > 0
            && Container_Config::brokerByID('Schedule Only In This Slot', '0')->getKey('value') == 0
        ) {
            foreach ($arrSortItems[0] as $intAttendees => $arrTalk) {
                $arrSlotSpaces = array();
                foreach ($arrTalk as $intTalkID => $objTalk) {
                    $floatPosition = ($intAttendees - ($intTalkID/10000))*10000;
                    $arrSlotSpaces[(float) $floatPosition] = $objTalk;
                }
            }
            unset($arrSortItems);
            krsort($arrSlotSpaces);
            foreach ($arrSlotSpaces as $floatPosition => $objTalk) {
                $fixed = false;
                foreach ($arrGrid as $intSlotID => $arrSlots) {
                    if ($intSlotID > $intNowSlot
                        && $intSlotID >= $objTalk->getKey('intRequestedSlotID')
                        && (!isset($arrSlots['isLocked']) || ($arrSlots['isLocked'] != 'hardlock' && $arrSlots['isLocked'] != 'softlock'))
                    ) {
                        foreach ($arrSlots as $intRoomID => $arrSlot) {
                            if (is_integer($intRoomID)) {
                                if ($fixed == false 
                                    && ! is_object($arrSlot) 
                                    && !isset($arrSlot['isLocked'])
                                ) {
                                    $arrGrid[$intSlotID][$intRoomID] = $objTalk;
                                    $fixed = true;
                                }
                            }
                        }
                    }
                }
                if ($fixed == false) {
                    $objTalk->unschedule();
                }
            }
        } else {
            if (isset($arrSortItems[0])) {
                foreach ($arrSortItems[0] as $mixedTalk) {
                    if (is_object($mixedTalk)) {
                        $mixedTalk->unschedule();
                    } elseif (is_array($mixedTalk)) {
                        foreach ($mixedTalk as $objTalk) {
                            $objTalk->unschedule();
                        }
                    } else {
                        throw new Exception("Not an object or an array");
                    }
                }
            }
        }
        foreach ($arrGrid as $intSlotID => $arrSlots) {
            if ($intSlotID > 0) {
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
        Object_Talk::unscheduleBasedOnAttendees(
            $arrTalks, 
            Container_Config::brokerByID('LimboMinimumVotes', '2')->getKey('value')
        );
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
                if (0 + $intMinAttendees > 0 + $data['intAttendees']) {
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
        array('intTalkID' => 1, 'strTalk' => 'Keynote', 'strTalkSummary' => 'A welcome to Barcamps', 'intUserID' => 1, 'intRequestedRoomID' => 1, 'intRequestedSlotID' => 1, 'intRoomID' => 1, 'intSlotID' => 1, 'intLength' => 1, 'jsonLinks' => '{"slides":"http:\/\/slideshare.net","twitter":"http:\/\/twitter.com\/"}', 'isLocked' => 1, 'jsonOtherPresenters' => '[]'),
        array('intTalkID' => 2, 'strTalk' => 'An introduction to CampFireManager2', 'strTalkSummary' => 'A walk through of how it works, where to get it from and why you should use it at your next conference', 'intUserID' => 2, 'intRequestedRoomID' => 2, 'intRequestedSlotID' => 2, 'intRoomID' => 2, 'intSlotID' => 2, 'intLength' => 1, 'jsonLinks' => '{"code":"http:\/\/www.github.com\/JonTheNiceGuy\/cfm2"}', 'isLocked' => '0', 'jsonOtherPresenters' => '[]'),
        array('intTalkID' => 3, 'strTalk' => 'An introduction to BarCamp', 'strTalkSummary' => "So, this is your first BarCamp? Glad you're here! This talk explains what BarCamps are, why they are so cool and why you should do a talk!", 'intUserID' => 3, 'intRequestedRoomID' => 3, 'intRequestedSlotID' => 2, 'intRoomID' => 3, 'intSlotID' => 2, 'intLength' => 1, 'jsonLinks' => '[]', 'isLocked' => '0', 'jsonOtherPresenters' => '[1]'),
        array('intTalkID' => 4, 'strTalk' => 'A talk in limbo', 'strTalkSummary' => 'This talk should be rendered as an unscheduled talk', 'intUserID' => 1, 'intRequestedRoomID' => 1, 'intRequestedSlotID' => 2, 'intRoomID' => -1, 'intSlotID' => -1, 'intLength' => 1),
    );
}
