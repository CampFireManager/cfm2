<?php

class object_defaultSlotType extends base_genericobject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
        'strDefaultSlotType' => array('type' => 'varchar', 'length' => 255)
    );
    protected $strDBTable = "defaultSlotType";
    protected $strDBKeyCol = "intDefaultSlotTypeID";
    // Local Object Requirements
    protected $intDefaultSlotTypeID = null;
    protected $strDefaultSlotType = null;
}