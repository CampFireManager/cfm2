<?php

class Object_Talk extends Base_GenericObject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
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
    );
    protected $strDBTable = "talk";
    protected $strDBKeyCol = "intTalkID";
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
    
    function getSelf()
    {
        $self = parent::getSelf();
        if ($this->getFull() == true) {
            if ($this->intUserID != null && $this->intUserID > 0) {
                $objUser = Object_User::brokerByID($this->intUserID);
                if (is_object($objUser)) {
                    $self['arrUser'] = $objUser->getSelf();
                }
            }
            if ($this->intRoomID != null && $this->intRoomID > 0) {
                $objRoom = Object_Room::brokerByID($this->intRoomID);
                if (is_object($objRoom)) {
                    $objRoom->setFull(true);
                    $self['arrRoom'] = $objRoom->getSelf();
                }
            }
            if ($this->intSlotID != null && $this->intSlotID > 0) {
                $objSlot = Object_Slot::brokerByID($this->intSlotID);
                if (is_object($objSlot)) {
                    $objSlot->setFull(true);
                    $self['arrSlot'] = $objSlot->getSelf();
                }
            }
            if ($this->intTrackID != null && $this->intTrackID > 0) {
                $objTrack = Object_Track::brokerByID($this->intTrackID);
                if (is_object($objTrack)) {
                    $self['arrTrack'] = $objTrack->getSelf();
                }
            }
            $self['arrLinks'] = (array) json_decode($this->jsonLinks);
            $resources = (array) json_decode($this->jsonResources);
            foreach ($resources as $resource) {
                $objResource = Object_Resource::brokerByID($resource);
                if (is_object($objResource)) {
                    $self['arrResources'][] = $objResource->getSelf();
                }
            }
            $presenters = (array) json_decode($this->jsonOtherPresenters);
            foreach ($presenters as $presenter) {
                $objPresenter = Object_User::brokerByID($presenter);
                if (is_object($objPresenter)) {
                    $self['arrPresenters'][] = $objPresenter->getSelf();
                }
            }
        }
        return $self;
    }

}

class Object_Talk_Demo extends Object_Talk
{
    protected $arrDemoData = array(
        array('intTalkID' => 1, 'strTalkTitle' => 'Keynote', 'strTalkSummary' => 'A welcome to Barcamps', 'intUserID' => 1, 'intRoomID' => 1, 'intSlotID' => 1, 'intTrackID' => null, 'intLength' => 1, 'jsonLinks' => '{"slides":"http:\/\/slideshare.net","twitter":"http:\/\/twitter.com\/"}', 'isRoomLocked' => 1, 'isSlotLocked' => 1, 'jsonResources' => '[1]', 'jsonOtherPresenters' => '[]'),
        array('intTalkID' => 2, 'strTalkTitle' => 'An introduction to CampFireManager2', 'strTalkSummary' => 'A walk through of how it works, where to get it from and why you should use it at your next conference', 'intUserID' => 2, 'intRoomID' => 1, 'intSlotID' => 2, 'intTrackID' => 1, 'intLength' => 1, 'jsonLinks' => '{"code":"http:\/\/www.github.com\/JonTheNiceGuy\/cfm2"}', 'isRoomLocked' => 0, 'isSlotLocked' => 0, 'jsonResources' => '[]', 'jsonOtherPresenters' => '[]'),
        array('intTalkID' => 3, 'strTalkTitle' => 'An introduction to BarCamp', 'strTalkSummary' => "So, this is your first BarCamp? Glad you're here! This talk explains what BarCamps are, why they are so cool and why you should do a talk!", 'intUserID' => 3, 'intRoomID' => 2, 'intSlotID' => 2, 'intTrackID' => 2, 'intLength' => 1, 'jsonLinks' => '[]', 'isRoomLocked' => 0, 'isSlotLocked' => 0, 'jsonResources' => '[3]', 'jsonOtherPresenters' => '[1]')
    );
}