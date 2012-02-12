<?php

class Object_Room extends Base_Genericobject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
    	'strRoomName' => array('type' => 'varchar', 'length' => 255),
        'jsonResourceList' => array('type' => 'text')
    );
    protected $strDBTable = "room";
    protected $strDBKeyCol = "intRoomID";
    protected $mustBeAdminToModify = true;
    // Local Object Requirements
    protected $intRoomID = null;
    protected $strRoomName = null;
    protected $jsonResourceList = null;
}