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
    protected $_arrDBItems = array(
        'strTalkTitle' => array('type' => 'varchar', 'length' => 255),
        'strTalkSummary' => array('type' => 'text'),
        'intUserID' => array('type' => 'int', 'length' => 11),
        'intRoomID' => array('type' => 'int', 'length' => 11),
        'intSlotID' => array('type' => 'int', 'length' => 11),
        'intTrackID' => array('type' => 'int', 'length' => 11),
        'intLength' => array('type' => 'tinyint', 'length' => 1),
        'jsonLinks' => array('type' => 'text'),
        'isRoomLocked' => array('type' => 'tinyint', 'length' => 1),
        'isSlotLocked' => array('type' => 'tinyint', 'length' => 1),
        'jsonResources' => array('type' => 'text'),
        'jsonOtherPresenters' => array('type' => 'text'),
        'lastChange' => array('type' => 'datetime')
    );
    protected $_strDBTable = "talk";
    protected $_strDBKeyCol = "intTalkID";
    protected $_reqCreatorToMod = true;
    // Local Object Requirements
    protected $intTalkID = null;
    protected $strTalkTitle = null;
    protected $strTalkSummary = null;
    protected $intUserID = null;
    protected $intRoomID = null;
    protected $intSlotID = null;
    protected $intTrackID = null;
    protected $intLength = null;
    protected $jsonLinks = null;
    protected $isRoomLocked = false;
    protected $isSlotLocked = false;
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
        $_self = parent::getSelf();
        if ($this->isFull() == true) {
            if ($this->intUserID != null && $this->intUserID > 0) {
                $objUser = Object_User::brokerByID($this->intUserID);
                if (is_object($objUser)) {
                    $_self['arrUser'] = $objUser->getSelf();
                    if ($_self['arrUser']['lastChange'] > $_self['lastChange']) {
                        $_self['lastChange'] = $_self['arrUser']['lastChange'];
                    }
                }
            }
            if ($this->intRoomID != null && $this->intRoomID > 0) {
                $objRoom = Object_Room::brokerByID($this->intRoomID);
                if (is_object($objRoom)) {
                    $objRoom->setFull(true);
                    $_self['arrRoom'] = $objRoom->getSelf();
                    if ($_self['arrRoom']['lastChange'] > $_self['lastChange']) {
                        $_self['lastChange'] = $_self['arrRoom']['lastChange'];
                    }
                }
            }
            if ($this->intSlotID != null && $this->intSlotID > 0) {
                $objSlot = Object_Slot::brokerByID($this->intSlotID);
                if (is_object($objSlot)) {
                    $objSlot->setFull(true);
                    $_self['arrSlot_start'] = $objSlot->getSelf();
                    if ($_self['arrSlot_start']['lastChange'] > $_self['lastChange']) {
                        $_self['lastChange'] = $_self['arrSlot_start']['lastChange'];
                    }
                    $_self['arrSlot_stop'] = $objSlot->getSelf();
                }
                if ($this->intLength > 1) {
                    $objSlot = Object_Slot::brokerByID($this->intSlotID + ($this->intLength - 1));
                    if (is_object($objSlot)) {
                        $objSlot->setFull(true);
                        $_self['arrSlot_stop'] = $objSlot->getSelf();
                        if ($_self['arrSlot_stop']['lastChange'] > $_self['lastChange']) {
                            $_self['lastChange'] = $_self['arrSlot_stop']['lastChange'];
                        }
                    }
                }
            }
            if ($this->intTrackID != null && $this->intTrackID > 0) {
                $objTrack = Object_Track::brokerByID($this->intTrackID);
                if (is_object($objTrack)) {
                    $_self['arrTrack'] = $objTrack->getSelf();
                    if ($_self['arrTrack']['lastChange'] > $_self['lastChange']) {
                        $_self['lastChange'] = $_self['arrTrack']['lastChange'];
                    }
                }
            }
            $_self['arrLinks'] = (array) json_decode($this->jsonLinks);
            $resources = (array) json_decode($this->jsonResources);
            foreach ($resources as $resource) {
                $objResource = Object_Resource::brokerByID($resource);
                if (is_object($objResource)) {
                    $arrResource = $objResource->getSelf();
                    $_self['arrResources'][] = $arrResource;
                    if ($arrResource['lastChange'] > $_self['lastChange']) {
                        $_self['lastChange'] = $arrResource['lastChange'];
                    }
                }
            }
            $presenters = (array) json_decode($this->jsonOtherPresenters);
            foreach ($presenters as $presenter) {
                $objPresenter = Object_User::brokerByID($presenter);
                if (is_object($objPresenter)) {
                    $arrPresenter = $objPresenter->getSelf();
                    $_self['arrPresenters'][] = $arrPresenter;
                    if ($arrPresenter['lastChange'] > $_self['lastChange']) {
                        $_self['lastChange'] = $arrPresenter['lastChange'];
                    }
                }
            }
            $_self['intAttendees'] = Object_Attendee::countByColumnSearch('intTalkID', $this->intTalkID);
            if (Object_Attendee::lastChangeByColumnSearch('intTalkID', $this->intTalkID) > $_self['lastChange']) {
                $_self['lastChange'] = Object_Attendee::lastChangeByColumnSearch('intTalkID', $this->intTalkID);
            }
            $_self['strSlotID'] = 'slot_' . $this->intSlotID;
            $_self['strRoomID'] = 'room_' . $this->intRoomID;
        }
        return $_self;
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
    protected $_arrDemoData = array(
        array('intTalkID' => 1, 'strTalkTitle' => 'Keynote', 'strTalkSummary' => 'A welcome to Barcamps', 'intUserID' => 1, 'intRoomID' => 1, 'intSlotID' => 1, 'intTrackID' => null, 'intLength' => 1, 'jsonLinks' => '{"slides":"http:\/\/slideshare.net","twitter":"http:\/\/twitter.com\/"}', 'isRoomLocked' => 1, 'isSlotLocked' => 1, 'jsonResources' => '[1]', 'jsonOtherPresenters' => '[]'),
        array('intTalkID' => 2, 'strTalkTitle' => 'An introduction to CampFireManager2', 'strTalkSummary' => 'A walk through of how it works, where to get it from and why you should use it at your next conference', 'intUserID' => 2, 'intRoomID' => 1, 'intSlotID' => 2, 'intTrackID' => 1, 'intLength' => 1, 'jsonLinks' => '{"code":"http:\/\/www.github.com\/JonTheNiceGuy\/cfm2"}', 'isRoomLocked' => 0, 'isSlotLocked' => 0, 'jsonResources' => '[]', 'jsonOtherPresenters' => '[]'),
        array('intTalkID' => 3, 'strTalkTitle' => 'An introduction to BarCamp', 'strTalkSummary' => "So, this is your first BarCamp? Glad you're here! This talk explains what BarCamps are, why they are so cool and why you should do a talk!", 'intUserID' => 3, 'intRoomID' => 2, 'intSlotID' => 2, 'intTrackID' => 2, 'intLength' => 1, 'jsonLinks' => '[]', 'isRoomLocked' => 0, 'isSlotLocked' => 0, 'jsonResources' => '[3]', 'jsonOtherPresenters' => '[1]')
    );
}
