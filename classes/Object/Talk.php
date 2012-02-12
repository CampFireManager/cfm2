<?php

class Object_Talk extends Base_Genericobject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
    	'strTalkTitle' => array('type' => 'varchar', 'length' => 255),
        'strTalkSummary' => array('type' => 'text'),
        'intUserID' => array('type' => 'int', 'length' => 11),
        'intRoomID' => array('type' => 'int', 'length' => 11),
        'intSlotID' => array('type' => 'int', 'length' => 11),
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
    protected $intLength = null;
    protected $jsonLinks = null;
    protected $isRoomLocked = false;
    protected $isSlotLocked = false;
    protected $jsonResources = null;
    protected $jsonOtherPresenters = null;
}