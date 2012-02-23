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
    protected $arrDemoData = array(
        array('intTalkID' => 1, 'strTalkTitle' => 'Keynote', 'strTalkSummary' => 'A welcome to Barcamps', 'intUserID' => 1, 'intRoomID' => 1, 'intSlotID' => 1, 'intTrackID' => null, 'intLength' => 1, 'jsonLinks' => '{"slides":"http:\/\/slideshare.net","twitter":"http:\/\/twitter.com\/"}', 'isRoomLocked' => 1, 'isSlotLocked' => 1, 'jsonResources' => '[1]', 'jsonOtherPresenters' => '[]'),
        array('intTalkID' => 2, 'strTalkTitle' => 'An introduction to CampFireManager2', 'strTalkSummary' => 'A walk through of how it works, where to get it from and why you should use it at your next conference', 'intUserID' => 2, 'intRoomID' => 1, 'intSlotID' => 2, 'intTrackID' => 1, 'intLength' => 1, 'jsonLinks' => '{"code":"http:\/\/www.github.com\/JonTheNiceGuy\/cfm2"}', 'isRoomLocked' => 0, 'isSlotLocked' => 0, 'jsonResources' => '[]', 'jsonOtherPresenters' => '[]'),
        array('intTalkID' => 3, 'strTalkTitle' => 'An introduction to BarCamp', 'strTalkSummary' => "So, this is your first BarCamp? Glad you're here! This talk explains what BarCamps are, why they are so cool and why you should do a talk!", 'intUserID' => 3, 'intRoomID' => 2, 'intSlotID' => 2, 'intTrackID' => 2, 'intLength' => 1, 'jsonLinks' => '[]', 'isRoomLocked' => 0, 'isSlotLocked' => 0, 'jsonResources' => '[3]', 'jsonOtherPresenters' => '[1]')
    );
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
}