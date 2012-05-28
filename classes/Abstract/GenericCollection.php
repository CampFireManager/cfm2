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
 * @category Abstract_GenericCollection
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

abstract class Abstract_GenericCollection implements Interface_Object
{
    protected $arrData = array();
    
    /**
     * An internal function to make this a singleton. This should only be used 
     * when being used to find objects of itself.
     *
     * @return object This class by itself.
     */
    public function getHandler()
    {
        $thisClassName = get_called_class();
        return new $thisClassName(false);
    }

    /**
     * A standard constructor method, which may be extended for specific 
     * collections.
     * 
     * @return object This class.
     */
    protected function __construct()
    {
        if (func_num_args() > 0) {
            throw new BadFunctionCallException("Too many arguments. This constructor does not accept parameters.");
        }
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
    public static function setFull($dummy = false)
    {
        return $dummy;
    }

    /**
     * This is a dummy function in case the function is called by accident
     *
     * @return boolean
     */
    public static function delete()
    {
        return false;
    }

    /**
     * This is a dummy function in case the function is called by accident
     *
     * @return boolean
     */
    public static function create()
    {
        return false;
    }

    /**
     * This is a dummy function in case the function is called by accident
     *
     * @return boolean
     */
    public static function write()
    {
        return false;
    }

    /**
     * Get a key from the collated data
     * 
     * @param string $key The value from the collated data
     *
     * @return mixed
     */
    public static function getKey($key = '')
    {
        if (isset($this->arrData[$key])) {
            return $this->arrData[$key];
        } else {
            return false;
        }
    }

    /**
     * This is a dummy function in case the function is called by accident
     *
     * @param mixed $key   Dummy value
     * @param mixed $value Dummy value
     * 
     * @return boolean
     */
    public static function setKey($key = '', $value = '')       
    {
        return false;
    }
    
    /**
     * This is a dummy function in case the function is called by accident
     *
     * @return boolean
     */
    public static function isFull()
    {
        return false;
    }
    
    /**
     * This is a dummy function in case the function is called by accident
     *
     * @param mixed $column Dummy value
     * @param mixed $value  Dummy value
     * 
     * @return boolean
     */
    public static function countByColumnSearch($column = '', $value = '')
    {
        return false;
    }

    /**
     * This is a dummy function in case the function is called by accident
     *
     * @param mixed $column Dummy value
     * @param mixed $value  Dummy value
     * 
     * @return boolean
     */
    public static function brokerByColumnSearch($column = '', $value = '')
    {
        return false;
    }
    /**
     * Return a specific aspect of the class
     *
     * @param string $intID The collection ID to retrieve. Leave blank for all of 
     * those collection objects.
     * 
     * @return array
     */
    public static function brokerByID($intID = null)
    {
        $thisClassName = get_called_class();
        $thisClass = new $thisClassName($intID);
        if (is_object($thisClass)) {
            return $thisClass;
        } else {
            return false;
        }
    }

    /**
     * This is a dummy function in case the function is called by accident
     *
     * @return integer There will only ever be one collection at a time
     */
    public static function countAll()
    {
        return 1;
    }
    
    /**
     * Return everything to do with this collection
     *
     * @return array
     */
    public static function brokerAll()
    {
        return array(self::brokerByID());
    }
    
    /**
     * This dummy function returns the array key for the collection - which will
     * be 0 in all cases, as the array keys start at zero and we only have one
     * object in any case.
     *
     * @return integer
     */
    public static function getPrimaryKeyValue()
    {
        return 0;
    }

}