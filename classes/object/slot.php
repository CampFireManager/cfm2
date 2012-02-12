<?php

class object_slot extends base_genericobject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
    	'dateStart' => array('type' => 'date'),
        'timeStart' => array('type' => 'time'),
        'dateEnd' => array('type' => 'date'),
        'timeEnd' => array('type' => 'time'),
        'intDefaultSlotTypeID' => array('type' => 'int', 'length' => 11),
        'isAvailable' => array('type' => 'tinyint', 'length' => 1)
    );
    protected $strDBTable = "slot";
    protected $strDBKeyCol = "intSlotID";
    protected $mustBeAdminToModify = true;
    // Local Object Requirements
    protected $intSlotID = null;
    protected $dateStart = null;
    protected $timeStart = null;
    protected $dateEnd = null;
    protected $timeEnd = null;
    protected $defaultSlotTypeID = null;
    protected $isAvailable = true;
}