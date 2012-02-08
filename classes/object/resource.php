<?php

class object_resource extends object_generic
{
    // Generic Object Requirements
    protected $arrDBItems = array(
    	'strResourceName' => array('type' => 'varchar', 'length' => 255),
        'decCostToUse' => array('type' => 'decimal', 'length' => '7,3')
    );
    protected $strDBTable = "resource";
    protected $strDBKeyCol = "intResourceID";
    // Local Object Requirements
    protected $intResourceID = null;
    protected $strResourceName = null;
    protected $decCostToUse = null;
}