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

class Object_Tag_Demo extends Object_Tag
{
    protected $mustBeAdminToModify = false;
    protected $arrDemoData = array(
        array('intTagID' => 1, 'strTagName' => 'Developers ^ 3', 'intTalkID' => 1),
        array('intTagID' => 2, 'strTagName' => 'Open Source', 'intTalkID' => 2),
        array('intTagID' => 3, 'strTagName' => 'Events', 'intTalkID' => 2),
        array('intTagID' => 4, 'strTagName' => 'Scheduling', 'intTalkID' => 2),
        array('intTagID' => 5, 'strTagName' => 'Newbie', 'intTalkID' => 3),
        array('intTagID' => 6, 'strTagName' => 'Explanation', 'intTalkID' => 3)
    );
}