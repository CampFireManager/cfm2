<?php

class Object_Tag extends Base_GenericObject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
    	'strTagName' => array('type' => 'varchar', 'length' => 255),
        'intTalkID' => array('type' => 'int', 'length' => 11)
    );
    protected $strDBTable = "tag";
    protected $strDBKeyCol = "intTagID";
    protected $mustBeAdminToModify = true;
    // Local Object Requirements
    protected $intTagID = null;
    protected $strTagName = null;
    protected $intTalkID = null;
}