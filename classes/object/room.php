<?php

class object_room extends object_generic
{
    // Generic Object Requirements
    protected $arrDBItems = array(
    	'strRoomName' => array('type' => 'varchar', 'length' => 255),
        'jsonResourceList' => array('type' => 'text')
    );
    protected $strDBTable = "room";
    protected $strDBKeyCol = "intRoomID";
    // Local Object Requirements
    protected $intRoomID = null;
    protected $strRoomName = null;
    protected $jsonResourceList = null;
}