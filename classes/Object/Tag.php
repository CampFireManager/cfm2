<?php
/**
 * CampFire Manager is a scheduling tool predominently used at BarCamps to 
 * schedule talks based, mainly, on the number of people attending each talk
 * receives.
 *
 * PHP version 5
 *
 * @category CampFireManager2
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */
/**
 * This class defines the object for PDO to use when retrives data about a tag.
 * 
 * @category Object_Tag
 * @package  CampFireManager2_Objects
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

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

/**
 * This class defines some default and demo data for the use in demos.
 * 
 * @category Object_Tag
 * @package  CampFireManager2_Objects
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

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