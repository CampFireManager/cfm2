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
 * @category Object_Userauth
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Object_Userauth extends Abstract_GenericObject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
        'intUserID' => array('type' => 'int', 'length' => 11),
    	'enumAuthType' => array('type' => 'enum', 'options' => array('openid', 'basicauth', 'codeonly', 'onetime'), 'unique' => true),
        'strAuthValue' => array('type' => 'varchar', 'length' => '255', 'unique' => true),
        'tmpCleartext' => array('type' => 'varchar', 'length' => '255'),
        'lastChange' => array('type' => 'datetime')
    );
    protected $strDBTable = "userauth";
    protected $strDBKeyCol = "intUserAuthID";
    protected $onlyCreatorMayModify = true;
    // Local Object Requirements
    protected $intUserAuthID = null;
    protected $intUserID = null;
    protected $enumAuthType = null;
    protected $strAuthValue = null;
    protected $tmpCleartext = null;
    protected $lastChange = null;

    /**
     * This overloaded function will process the normal setKey function, unless
     * it matches a pre-defined set of overideable functions.
     * 
     * @param string       $keyname The key to set
     * @param string|array $value   The value(s) to set against the key
     * 
     * @return void
     */
    function setKey($keyname = '', $value = '')
    {
        switch ($keyname) {
        case 'enumAuthType':
            if ($value != '' && $this->enumAuthType != $value) {
                $valid = false;
                foreach ($this->arrDBItems['enumAuthType']['options'] as $option) {
                    if ($option == $value) {
                        $valid = true;
                    }
                }
                if ($valid == true) {
                    $this->enumAuthType = $value;
                    $this->arrChanges['enumAuthType'] = true;
                }
            }
            break;
        case 'strAuthValue':
            if ($this->enumAuthType != null) {
                if ($this->enumAuthType == 'openid'
                    || $this->enumAuthType == 'onetime'
                ) {
                    if (is_array($value) && isset($value['password'])) {
                        $password = $value['password'];
                    } else {
                        $password = $value;
                    }
                    $set = sha1(Container_Config::getSecure('salt') . $password);
                } elseif (($this->enumAuthType == 'basicauth'
                    || $this->enumAuthType == 'codeonly')
                    && is_array($value) 
                    && isset($value['username']) 
                    && isset($value['password'])
                ) {
                    $password = $value['password'];
                    $set = $value['username'] . ':' . sha1(Container_Config::getSecure('salt') . $value['password']);
                }
                if ($set != '' && $this->strAuthValue != $set) {
                    $this->tmpCleartext = $password;
                    $this->strAuthValue = $set;                    
                    $this->arrChanges['tmpCleartext'] = true;
                    $this->arrChanges['strAuthValue'] = true;
                }
            }
            break;
        default:
            parent::setKey($keyname, $value);
        }
    }

    /**
     * A function to broker the current user object back to the engine.
     * 
     * @return object 
     */
    function brokerCurrent()
    {
        $objCache = Base_Cache::getHandler();
        $this_class_name = get_called_class();
        $createIfNotExist = false;
        if (isset($objCache->arrCache[$this_class_name]['current'])
            && $objCache->arrCache[$this_class_name]['current'] != null
            && $objCache->arrCache[$this_class_name]['current'] != false
        ) {
            return $objCache->arrCache[$this_class_name]['current'];
        }
        Base_GeneralFunctions::startSession();
        $arrRequestData = Base_Request::getRequest();
        if (isset($_SESSION['intUserAuthID']) && $_SESSION['intUserAuthID'] != '') {
            try {
                $objDatabase = Container_Database::getConnection();
                $sql = "SELECT * FROM userauth WHERE intUserAuthID = ? LIMIT 1";
                $query = $objDatabase->prepare($sql);
                $query->execute(array($_SESSION['intUserAuthID']));
                $result = $query->fetchObject($this_class_name);
                $objCache->arrCache[$this_class_name]['id'][$result->getKey('intUserID')] = $result;
                $objCache->arrCache[$this_class_name]['current'] = $result;
                return $result;
            } catch (PDOException $e) {
                error_log("Error in SQL: " . $e->getMessage());
                return false;
            }
        } elseif (isset($_SESSION['OPENID_AUTH']) AND $_SESSION['OPENID_AUTH'] != false) {
            $key = 'openid';
            $value = sha1(Container_Config::getSecure('salt') . $arrRequestData['OPENID_AUTH']);
            $createIfNotExist = true;
        } elseif (isset($arrRequestData['username']) && $arrRequestData['username'] != null && isset($arrRequestData['password']) && $arrRequestData['password'] != null) {
            $key = 'basicauth';
            $value = $arrRequestData['username'] . ':' . sha1(Container_Config::getSecure('salt') . $arrRequestData['password']);
        } elseif (isset($arrRequestData['requestUrlParameters']['code']) && $arrRequestData['requestUrlParameters']['code'] != null) {
            $key = 'codeonly';
            $value = '%:' . sha1(Container_Config::getSecure('salt') . $arrRequestData['requestUrlParameters']['code']);
        } elseif (isset($arrRequestData['requestUrlParameters']['username']) 
                && isset($arrRequestData['requestUrlParameters']['password'])
                ) {
            $key = 'basicauth';
            $value = $arrRequestData['requestUrlParameters']['username'] . ':' . sha1(Container_Config::getSecure('salt') . $arrRequestData['requestUrlParameters']['password']);
            if (isset($arrRequestData['requestUrlParameters']['register'])) {
                $createIfNotExist = true;
            }
        }
        if (isset($key)) {
            try {
                $objDatabase = Container_Database::getConnection();
                $sql = "SELECT * FROM userauth WHERE enumAuthType = ? and strAuthValue = ? LIMIT 1";
                $query = $objDatabase->prepare($sql);
                $query->execute(array($key, $value));
                $result = $query->fetchObject($this_class_name);
                if ($result != false) {
                    $objCache->arrCache[$this_class_name]['id'][$result->getKey('intUserID')] = $result;
                    $_SESSION['intUserAuthID'] = $result->getKey('intUserAuthID');
                    $objCache->arrCache[$this_class_name]['current'] = $result;
                    if ($key == 'onetime') {
                        $sql = "DELETE FROM userauth WHERE $key = ? LIMIT 1";
                        $query = $objDatabase->prepare($sql);
                        $query->execute(array($value));
                    }
                } else {
                    if ($createIfNotExist === true) {
                        try {
                            $return = new Object_User(true);
                            if ($return != false && isset($return->temp_intUserAuthID)) {
                                $_SESSION['intUserAuthID'] = $return->temp_intUserAuthID;
                            }
                        } catch (Exception $e) {
                            return $e->getMessage();
                        }
                    }
                }
                return $result;
            } catch (PDOException $e) {
                error_log("Error in SQL: " . $e->getMessage());
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Create a new User object
     * 
     * @param boolean $isCreationAction Perform Creation Actions (default 
     * false), or just handle the data supplied by the PDO request.
     * @param string  $codeonly         The code associated to this type of authentication
     * @param boolean $onetime          Return a one-time unique code to use
     *
     * @return object
     */
    function __construct($isCreationAction = false, $codeonly = false, $onetime = false)
    {
        parent::__construct($isCreationAction);
        if (! $isCreationAction) {
            return $this;
        }
        Base_GeneralFunctions::startSession();
        $arrRequestData = Base_Request::getRequest();
        if (isset($_SESSION['intUserAuthID'])) {
            unset($_SESSION['intUserAuthID']);
        }
        if (isset($_SESSION['OPENID_AUTH'])) {
            $this_class->setKey('enumAuthType', 'openid');
            $this_class->setKey('strAuthValue', $arrRequestData['OPENID_AUTH']);
        } elseif (
            isset($arrRequestData['requestUrlParameters']['username']) 
            && $arrRequestData['requestUrlParameters']['username'] != null 
            && isset($arrRequestData['requestUrlParameters']['password']) 
            && $arrRequestData['requestUrlParameters']['password'] != null
        ) {
            if (count(Object_Userauth::brokerByColumnSearch('strAuthValue', $arrRequestData['requestUrlParameters']['username'] . ':%')) > 0) {
                throw new Exception("This username already exists, please select another");
            }
            $this->setKey('enumAuthType', 'basicauth');
            $this->setKey('strAuthValue', array('password' => $arrRequestData['requestUrlParameters']['password'], 'username' => $arrRequestData['requestUrlParameters']['username']));
        } elseif ($codeonly != false) {
            $this->setKey('enumAuthType', 'codeonly');
            $authString = '';
            while ($authString == '') {
                $authString = Base_GeneralFunctions::genRandStr(5, 9);
                if (count(Object_Userauth::brokerByColumnSearch('strAuthValue', '%:' . sha1(Container_Config::getSecure('salt') . $authString))) > 0) {
                    $authString == '';
                }
            }
            $this->setKey('strAuthValue', array('password' => $authString, 'username' => $codeonly));
        } elseif ($onetime == true) {
            $this->setKey('enumAuthType', 'onetime');
            $authString = '';
            while ($authString == '') {
                $authString = Base_GeneralFunctions::genRandStr(8, 12);
                if (count(Object_Userauth::brokerByColumnSearch('strAuthValue', '%:' . sha1(Container_Config::getSecure('salt') . $authString))) > 0) {
                    $authString == '';
                }
            }
            $this->setKey('strAuthValue', array('password' => $authString, 'username' => 'onetime'));
        } else {
            return false;
        }
        $this->create();
        return $this;
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
        switch ($this->enumAuthType) {
        case 'basicauth':
            $string = explode(':', $this->strAuthValue);
            $self['strAuthValue'] = $string[0] . ':' . str_repeat('.', strlen($string[1]));
            break;
        case 'onetime':
            $string = explode(':', $this->strAuthValue);
            $self['strAuthValue'] = substr($string[1], 0, 2) . str_repeat('.', strlen($string[1]) - 4) . substr($string[1], -2);
            break;
        case 'codeonly':
            $string = explode(':', $this->strAuthValue);
            if (preg_match('/([^@]+)@([^@]+)', $string[0], $matches) > 0) {
                $self['strAuthValue'] = substr($matches[1], 0, 1) . str_repeat('.', strlen($matches[1]) - 2) . substr($matches[1], -1) . '@' . substr($matches[2], 0, 2) . str_repeat('.', strlen($matches[1]) - 4) . substr($matches[1], -2);
            } else {
                $self['strAuthValue'] = str_repeat('.', strlen($string[0]) - 6) . substr($string[0], -6);
            }
            break;
        }
        $self['strCleartext'] = $this->getCleartext();
        return $self;
    }
    
    /**
     * We may need to store the password for *ONE* itteration in cleartext.
     * Only the user needs to see this cleartext password and it's only to
     * be stored for 2 minutes or one viewing. Otherwise return an empty
     * string. The guarantee of clearing tmpCleartext values needs to be
     * picked up by a cronTick hook. This plugin needs to be written!
     *
     * @return string 
     */
    function getCleartext()
    {
        $user = Object_User::brokerCurrent();
        if ($this->tmpCleartext != ''
            && ($user != false 
            && $user->getKey('intUserID') == $this->intUserID)
            || strtotime($this->lastChange) < strtotime("2 minutes ago")
        ) {
            $tmpCleartext = $this->tmpCleartext;
            $this->setKey('tmpCleartext', '');
            $this->write();
            $this->tmpCleartext = $tmpCleartext;
        } else {
            $this->tmpCleartext = '';
        }
        return $this->tmpCleartext;
    }
}

/**
 * This class defines some default and demo data for the use in demos.
 * 
 * @category Object_Userauth
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Object_Userauth_Demo extends Object_Userauth
{
    protected $arrDemoData = array(
        array('intUserAuthID' => 1, 'intUserID' => 1, 'enumAuthType' => 'onetime', 'strAuthValue' => 'abcd2346ACDE'),
        array('intUserAuthID' => 2, 'intUserID' => 2, 'enumAuthType' => 'openid', 'strAuthValue' => 'http://www.openid.net'),
        array('intUserAuthID' => 3, 'intUserID' => 2, 'enumAuthType' => 'basicauth', 'strAuthValue' => array('username' => 'cfmadmin', 'password' => 'password')),
        array('intUserAuthID' => 4, 'intUserID' => 3, 'enumAuthType' => 'codeonly', 'strAuthValue' => array('username' => '+447777777777', 'password' => 'codeonly')),
        array('intUserAuthID' => 5, 'intUserID' => 3, 'enumAuthType' => 'codeonly', 'strAuthValue' => array('username' => 'user@gmail.com', 'password' => 'email')),
        array('intUserAuthID' => 6, 'intUserID' => 4, 'enumAuthType' => 'openid', 'strAuthValue' => 'http://www.google.com/accounts/o8/id')
    );    
}
