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
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */
/**
 * This Interface defines the required functions in any PDO object based class.
 *
 * @category Interface_Config
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */

interface Interface_Object
{
    /**
     * This function will search for any instance of this type of object, using
     * the table's primary key.
     * 
     * @param integer $intID The key to search for.
     * 
     * @return array
     */
    public static function brokerByID($intID);
    
    /**
     * This function searches for all values which match a value in a column
     * search. Nulls and '' are treated equally as a null value or an empty
     * string, % means "anything", and you can invert a search by setting
     * $inverse to true.
     * 
     * @param string  $column  The column name in the table to search for
     * @param string  $value   The value to look for in that column
     * @param boolean $inverse Reverse the search
     * 
     * @return array
     */
    public static function brokerByColumnSearch($column, $value, $inverse);
    
    /**
     * This function will return the number of rows which matches a particular 
     * column search.
     *
     * @param string  $column  The column name in the table to search for
     * @param string  $value   The value to look for in that column
     * @param boolean $inverse Reverse the search
     * 
     * @return integer
     */    
    public static function countByColumnSearch($column, $value, $inverse);
    /**
     * This function will return the most recent Last Modified value for a
     * particular column search.
     *
     * @param string  $column  The column name in the table to search for
     * @param string  $value   The value to look for in that column
     * @param boolean $inverse Reverse the search
     * 
     * @return datetime
     */
    public static function lastChangeByColumnSearch($column, $value, $inverse);
    /**
     * This function loads all values from this database table.
     * 
     * @return array
     */
    public static function brokerAll();
    /**
     * This function will return the last modified date of the most recently 
     * edited row in this table.
     * 
     * @return datetime
     */
    public static function lastChangeAll();
    /**
     * This function will count the number of rows in this table.
     * 
     * @return integer
     */
    public static function countAll();
    /**
     * This function will return the values which are required and which are
     * optional to create a new object of this type
     * 
     * @return array
     */
    public static function listKeys();
    /**
     * This function returns an array of all the calculated data for this object
     * 
     * @return array
     */
    public function getSelf();
}