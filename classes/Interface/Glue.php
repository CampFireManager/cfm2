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
 * This Interface defines the required functions in Glue based class.
 *
 * @category Interface_Glue
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

interface Interface_Glue
{
    /**
     * This function instantiates the object using the supplied configuration
     * details.
     * 
     * @param array  $arrConfigValues A list of configuration keys to retrieve
     * 
     * @return Interface_Glue
     */
    public function __construct($arrConfigValues = array());
    /**
     * This function calls the service, and retrieves a list of private messages
     * 
     * @param timestamp $since_timestamp The timestamp of the last successful
     * retrieval of a message
     * @param integer   $since_id        The last ID of a successfully received
     * message.
     * 
     * @return array
     */
    public function read_private($since_timestamp = null, $since_id = null);
    /**
     * This function calls the service, and retrieves a list of public messages
     * 
     * @param timestamp $since_timestamp The timestamp of the last successful
     * retrieval of a message
     * @param integer   $since_id        The last ID of a successfully received
     * message.
     * 
     * @return array
     */
    public function read_public($since_timestamp = null, $since_id = null);
    /**
     * This function calls the service, sending a message.
     * 
     * @param string $message     The message to send
     * @param string $destination The destination (if required) to send it to.
     * 
     * @return boolean
     */
    public function send($message, $destination = null);
}