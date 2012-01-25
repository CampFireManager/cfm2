<?php

class object_room extends object_generic
{
    // Generic Object Requirements
    protected $arrDBItems = array(
    	'strRoomName'=>true
    );
    protected $strDBTable = "room";
    protected $strDBKeyCol = "roomID";
    // Local Object Requirements
    protected $strRoomName = null;
}