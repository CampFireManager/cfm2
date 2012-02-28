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
 * This class defines the facilities available in a room, which may be requested
 * when proposing a talk.
 * 
 * @category Object_Resource
 * @package  CampFireManager2_Objects
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

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
    // Local Object Requirements
    protected $intResourceID = null;
    protected $strResourceName = null;
    protected $decCostToUse = null;
}

/**
 * This class defines some default and demo data for the use in demos.
 * 
 * @category Object_Resource
 * @package  CampFireManager2_Objects
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */
class Object_Resource_Demo extends Object_Resource
{
    protected $mustBeAdminToModify = false;
    protected $arrDemoData = array(
        array('intResourceID' => 1, 'strResourceName' => 'Projector', 'decCostToUse' => 0.000),
        array('intResourceID' => 2, 'strResourceName' => 'PA', 'decCostToUse' => 0.500),
        array('intResourceID' => 3, 'strResourceName' => 'Flat Screen TV', 'decCostToUse' => 1.000)
    );
}