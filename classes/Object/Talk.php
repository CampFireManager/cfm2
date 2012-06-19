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
        'intAllocatedSlotID' => array('type' => 'int', 'length' => 11),
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
    protected $intAllocatedSlotID = null;
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
            if ($this->intUserID != null && $this->intUserID > 0) {
                $objUser = Object_User::brokerByID($this->intUserID);
                if (is_object($objUser)) {
                    $self['arrUser'] = $objUser->getSelf();
                    if ($self['arrUser']['lastChange'] > $self['lastChange']) {
                        $self['lastChange'] = $self['arrUser']['lastChange'];
                    }
                }
            }
            if ($this->intRoomID != null && $this->intRoomID > 0) {
                $objRoom = Object_Room::brokerByID($this->intRoomID);
                if (is_object($objRoom)) {
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
            $self['intAttendees'] = Object_Attendee::countByColumnSearch('intTalkID', $this->intTalkID);
            if (Object_Attendee::lastChangeByColumnSearch('intTalkID', $this->intTalkID) > $self['lastChange']) {
                $self['lastChange'] = Object_Attendee::lastChangeByColumnSearch('intTalkID', $this->intTalkID);
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
        $this->setKey('intAllocatedSlotID', null);
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
    public function setKey($key, $value, $debug = false)
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
