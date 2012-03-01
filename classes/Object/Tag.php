<?php
/**
 * CampFire Manager is a scheduling tool predominently used at BarCamps to 
 * schedule talks based, mainly, on the number of people attending each talk
 * receives.
 *
 * PHP version 5
 *
 * @category Default
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */
/**
 * This class defines the object for PDO to use when retrives data about a tag.
 * 
 * @category Object_Tag
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Object_Tag extends Base_GenericObject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
    	'strTagName' => array('type' => 'varchar', 'length' => 255, 'unique' => true),
        'intTalkID' => array('type' => 'int', 'length' => 11, 'unique' => true),
        'intUserID' => array('type' => 'int', 'length' => 11, 'unique' => true)
    );
    protected $strDBTable = "tag";
    protected $strDBKeyCol = "intTagID";
    protected $mustBeAdminToModify = false;
    protected $mustBeCreatorToModify = false;
    // Local Object Requirements
    protected $intTagID = null;
    protected $strTagName = null;
    protected $intTalkID = null;
    protected $intUserID = null;
    
    /**
     * This function overloads the normal construction function to ensure that Tag modifications are set as per the config file.
     *
     * @param boolean $isReal Pass this variable on to the parent class
     * 
     * @return object
     */
    function __construct($isReal = false)
    {
        $this->mustBeAdminToModify = Base_GeneralFunctions::asBoolean(Base_Config::getConfig('OnlyAdminsCanTagTalks', 'false'));
        $this->mustBeCreatorToModify = Base_GeneralFunctions::asBoolean(Base_Config::getConfig('OnlyTagCreatorsCanEditTalkTags', 'false'));
        return parent::__construct($isReal);
    }
}

/**
 * This class defines some default and demo data for the use in demos.
 * 
 * @category Object_Tag
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Object_Tag_Demo extends Object_Tag
{
    protected $arrDemoData = array(
        array('intTagID' => 1, 'strTagName' => 'Developers ^ 3', 'intTalkID' => 1, 'intUserID' => 2),
        array('intTagID' => 2, 'strTagName' => 'Open Source', 'intTalkID' => 2, 'intUserID' => 2),
        array('intTagID' => 3, 'strTagName' => 'Events', 'intTalkID' => 2, 'intUserID' => 2),
        array('intTagID' => 4, 'strTagName' => 'Scheduling', 'intTalkID' => 2, 'intUserID' => 2),
        array('intTagID' => 5, 'strTagName' => 'Newbie', 'intTalkID' => 3, 'intUserID' => 2),
        array('intTagID' => 6, 'strTagName' => 'Explanation', 'intTalkID' => 3, 'intUserID' => 2)
    );
}