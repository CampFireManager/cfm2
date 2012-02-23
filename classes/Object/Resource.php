<?php

class Object_Resource extends Base_GenericObject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
    	'strResourceName' => array('type' => 'varchar', 'length' => 255),
        'decCostToUse' => array('type' => 'decimal', 'length' => '7,3')
    );
    protected $strDBTable = "resource";
    protected $strDBKeyCol = "intResourceID";
    protected $mustBeAdminToModify = true;
    protected $arrDemoData = array(
        array('intResourceID' => 1, 'strResourceName' => 'Projector', 'decCostToUse' => 0.000),
        array('intResourceID' => 2, 'strResourceName' => 'PA', 'decCostToUse' => 0.500),
        array('intResourceID' => 3, 'strResourceName' => 'Flat Screen TV', 'decCostToUse' => 1.000)
    );
    // Local Object Requirements
    protected $intResourceID = null;
    protected $strResourceName = null;
    protected $decCostToUse = null;
}