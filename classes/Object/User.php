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
 * This class defines the object for PDO to use when retrives data about a talk.
 * 
 * @category Object_User
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Object_User extends Base_GenericObject
{
    protected $arrDBItems = array(
    	'strUserName' => array('type' => 'varchar', 'length' => 255),
        'isWorker' => array('type' => 'tinyint', 'length' => 1),
        'isAdmin' => array('type' => 'tinyint', 'length' => 1),
        'hasAttended' => array('type' => 'tinyint', 'length' => 1),
        'isHere' => array('type' => 'tinyint', 'length' => 1)
    );
    protected $strDBTable = "user";
    protected $strDBKeyCol = "intUserID";
    // Local Object Requirements
    protected $intUserID = null;
    protected $strUserName = null;
    protected $isWorker = false;
    protected $isAdmin = false;
    protected $hasAttended = false;
    protected $isHere = false;

    /**
     * Get the object for the current user.
     *
     * @return object UserObject for intUserID
     */
    function brokerCurrent()
    {
        $objCache = Base_Cache::getHandler();
        $this_class_name = get_called_class();
        $this_class = new $this_class_name();
        if (true === isset($objCache->arrCache[$this_class_name]['current'])
            && $objCache->arrCache[$this_class_name]['current'] != null
            && $objCache->arrCache[$this_class_name]['current'] != false
        ) {
            return $objCache->arrCache[$this_class_name]['current'];
        }
        $user = Object_Userauth::brokerCurrent();
        if ($user !== false) {
            $intUserID = $user->getKey('intUserID');
        } else {
            return false;
        }
        try {
            $db = Base_Database::getConnection();
            $sql = "SELECT * FROM {$this_class->strDBTable} WHERE {$this_class->strDBKeyCol} = ? LIMIT 1";
            $query = $db->prepare($sql);
            $query->execute(array($intUserID));
            $result = $query->fetchObject($this_class_name);
            $objCache->arrCache[$this_class_name]['id'][$intUserID] = $result;
            $objCache->arrCache[$this_class_name]['current'] = $result;
            return $result;
        } catch(PDOException $e) {
            error_log("SQL Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create a new User object
     * 
     * @param boolean $isReal Perform Creation Actions (default false)
     *
     * @return object This object
     */
    function __construct($isReal = false)
    {
        parent::__construct($isReal);
        if (! $isReal) {
            return $this;
        }
        try {
            $objUserAuth = new Object_Userauth();
            if (false !== $objUserAuth) {
                $this->setKey('strUserName', Base_GeneralFunctions::getValue(Base_Request::getRequest(), 'strUsername', 'An Anonymous User'));
                $this->create();
                $objUserAuth->setKey('intUserID', $this->getKey('intUserID'));
                $objUserAuth->write();
            }
            return $this;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    /**
     * This function clears values related to a logged in user.
     * 
     * @return void
     */
    function logout()
    {
        Base_GeneralFunctions::sessionStart();
        $arrRequestData = Base_Request::getRequest();
        if (isset($_SESSION['intUserAuthID']) && $_SESSION['intUserAuthID'] != '') {
            unset($_SESSION['intUserAuthID']);
        }
        if (isset($_SESSION['OPENID_AUTH']) && $_SESSION['OPENID_AUTH'] != '') {
            unset($_SESSION['OPENID_AUTH']);
        }
        if (isset($arrRequestData['username'])) {
            Base_Response::sendHttpResponse(401);
        }
    }
    
    /**
     * This overloaded function returns the data from the PDO object and adds
     * supplimental data based on linked tables
     * 
     * @return array
     */
    function getSelf()
    {
        $self = parent::getSelf();
        if ($this->getFull() == true) {
            $arrUserAuth = Object_Userauth::brokerByColumnSearch('intUserID', $this->intUserID);
            foreach ($arrUserAuth as $key => $value) {
                $self['arrUserAuth'][$key] = $value->getSelf();
            }
        }
        return $self;
    }
}

/**
 * This class defines some default and demo data for the use in demos.
 * 
 * @category Object_User
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */
class Object_User_Demo extends Object_User
{
    protected $arrDemoData = array(
        array('intUserID' => 1, 'strUserName' => 'Mr Keynote', 'isWorker' => 0, 'isAdmin' => 0, 'hasAttended' => 1, 'isHere' => 1),
        array('intUserID' => 2, 'strUserName' => 'Mr CFM Admin', 'isWorker' => 1, 'isAdmin' => 1, 'hasAttended' => 1, 'isHere' => 1),
        array('intUserID' => 3, 'strUserName' => 'Ms SoftSkills', 'isWorker' => 1, 'isAdmin' => 0, 'hasAttended' => 1, 'isHere' => 1),
        array('intUserID' => 4, 'strUserName' => 'Ms Attendee', 'isWorker' => 0, 'isAdmin' => 0, 'hasAttended' => 0, 'isHere' => 0)
    );
}