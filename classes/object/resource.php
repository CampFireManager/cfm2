<?php

class object_resource extends base_genericobject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
    	'strResourceName' => array('type' => 'varchar', 'length' => 255),
        'decCostToUse' => array('type' => 'decimal', 'length' => '7,3')
    );
    protected $strDBTable = "resource";
    protected $strDBKeyCol = "intResourceID";
    protected $mustBeAdminToModify = true;
    // Local Object Requirements
    protected $intResourceID = null;
    protected $strResourceName = null;
    protected $decCostToUse = null;
}