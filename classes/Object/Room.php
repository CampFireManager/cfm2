<?php

class Object_Room extends Base_GenericObject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
    	'strRoomName' => array('type' => 'varchar', 'length' => 255),
        'jsonResourceList' => array('type' => 'text')
    );
    protected $strDBTable = "room";
    protected $strDBKeyCol = "intRoomID";
    protected $mustBeAdminToModify = true;
    protected $arrDemoData = array(
        array('intRoomID' => 1, 'strRoomName' => 'Room A', 'jsonResourceList' => '[1,2]'),
        array('intRoomID' => 2, 'strRoomName' => 'Room B', 'jsonResourceList' => '[2,3]'),
        array('intRoomID' => 3, 'strRoomName' => 'Room C', 'jsonResourceList' => '[3]')
    );
    // Local Object Requirements
    protected $intRoomID = null;
    protected $strRoomName = null;
    protected $jsonResourceList = null;
}

class Object_Room_Demo extends Object_Room
{
    protected $mustBeAdminToModify = false;
    protected $arrDemoData = array(
        array('intRoomID' => 1, 'strRoomName' => 'Room A', 'jsonResourceList' => '[1,2]'),
        array('intRoomID' => 2, 'strRoomName' => 'Room B', 'jsonResourceList' => '[2,3]'),
        array('intRoomID' => 3, 'strRoomName' => 'Room C', 'jsonResourceList' => '[3]')
    );
}