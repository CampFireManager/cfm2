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
 * This class provides all the collection specific functions used throughout the
 * site. It is used as the basis for every object.
 *
 * @category Base_GenericCollection
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Base_GenericCollection
{
    protected $arrData = array();
    
    /**
     * An internal function to make this a singleton. This should only be used when being used to find objects of itself.
     *
     * @return object This class by itself.
     */
    public static function getHandler()
    {
        $this_class_name = get_called_class();
        return new $this_class_name(false);
    }

    /**
     * A standard constructor method, which may be extended for specific 
     * collections.
     * 
     * @param boolean $isReal Used to determine whether to process the response 
     * further. Not used in this class but may be used in derived classes. Here 
     * for safety sake.
     * 
     * @return object This class.
     */
    function __construct($isReal = false)
    {
        return $this;
    }
    
    /**
     * A function to return all the timetable data. This will probably be superceeded by something.
     *
     * @return array
     */
    public function getSelf()
    {
        return $this->arrData;
    }

    /**
     * This function does nothing - it is here to emulate the behaviour of the
     * GenericObject.
     *
     * @param boolean $dummy A dummy value
     * 
     * @return boolean 
     */
    public function setFull($dummy = false)
    {
        return $dummy;
    }
}