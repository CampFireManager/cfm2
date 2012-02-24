<?php

class Object_DefaultSlotType extends Base_GenericObject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
        'strDefaultSlotType' => array('type' => 'varchar', 'length' => 255),
        'locksSlot' => array('type' => 'enum', 'options' => array('hardlock', 'softlock', 'none'))
    );
    protected $strDBTable = "defaultSlotType";
    protected $strDBKeyCol = "intDefaultSlotTypeID";
    protected $mustBeAdminToModify = true;
    // Local Object Requirements
    protected $intDefaultSlotTypeID = null;
    protected $strDefaultSlotType = null;
    protected $locksSlot = null;
}

class Object_DefaultSlotType_Demo extends Object_DefaultSlotType
{
    protected $mustBeAdminToModify = false;
    protected $arrDemoData = array(
        array('intDefaultSlotTypeID' => 1, 'strDefaultSlotType' => 'Keynote', 'locksSlot' => 'hardlock'),
        array('intDefaultSlotTypeID' => 2, 'strDefaultSlotType' => 'Lunch', 'locksSlot' => 'softlock'),
        array('intDefaultSlotTypeID' => 3, 'strDefaultSlotType' => 'Closing talk', 'locksSlot' => 'hardlock'),
        array('intDefaultSlotTypeID' => 4, 'strDefaultSlotType' => 'Afternoon Tea', 'locksSlot' => 'none')
    );
}