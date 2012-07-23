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
        'intUserID' => array('type' => 'int', 'length' => 11, 'optional' => 'worker'),
        'enumAuthType' => array('type' => 'enum', 'options' => array('openid', 'basicauth', 'codeonly', 'onetime'), 'unique' => true, 'required' => 'user'),
        'strAuthValue' => array('type' => 'varchar', 'length' => '255', 'unique' => true),
        'tmpCleartext' => array('type' => 'varchar', 'length' => '255'),
        'lastChange' => array('type' => 'datetime')
    );
    protected $strDBTable = "userauth";
    protected $strDBKeyCol = "intUserAuthID";
    protected $reqCreatorToMod = true;
    // Local Object Requirements
    protected $intUserAuthID = null;
    protected $intUserID = null;
    protected $enumAuthType = null;
    protected $strAuthValue = null;
    protected $tmpCleartext = null;
    protected $lastChange = null;

    /**
     * This overloaded function performs the special magic needed for Userauth
     * values.
     * 
     * @return array
     */
    public static function listKeys()
    {
        $return = parent::listKeys();
        $return['enumAuthType']['options'] = array('basicauth' => 'basicauth');
        $return['username']['required'] = 'user';
        $return['password']['required'] = 'user';
        $return['password']['input_type'] = 'password';
        return $return;
    }
    
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
                    $set = sha1(Container_Config::getSecureByID('salt', 'Not Yet Set!!!')->getKey('value') . $password);
                } elseif ($this->enumAuthType == 'basicauth'
                    && is_array($value) 
                    && isset($value['username']) 
                    && isset($value['password'])
                ) {
                    $password = $value['password'];
                    $set = $value['username'] . ':' . sha1(Container_Config::getSecureByID('salt', 'Not Yet Set!!!')->getKey('value') . $value['password']);
                } elseif ($this->enumAuthType == 'codeonly'
                    && is_array($value) 
                    && isset($value['username']) 
                    && isset($value['password'])                        
                ) {
                    $password = $value['password'];
                    $set = $value['username'] . ':' . $value['password'];
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
        $thisClassName = get_called_class();
        $createIfNotExist = false;
        if (isset($objCache->arrCache[$thisClassName]['current'])
            && $objCache->arrCache[$thisClassName]['current'] != null
            && $objCache->arrCache[$thisClassName]['current'] != false
        ) {
            return $objCache->arrCache[$thisClassName]['current'];
        }
        $objRequest = Container_Request::getRequest();
        $arrSession = $objRequest->get_arrSession();
        if (isset($arrSession['intUserAuthID'])) {
            try {
                $objDatabase = Container_Database::getConnection();
                $sql = "SELECT * FROM userauth WHERE intUserAuthID = ? LIMIT 1";
                $query = $objDatabase->prepare($sql);
                $query->execute(array($arrSession['intUserAuthID']));
                $result = $query->fetchObject($thisClassName);
                if ($result != false) {
                    $objCache->arrCache[$thisClassName]['id'][$result->getKey('intUserID')] = $result;
                    $objCache->arrCache[$thisClassName]['current'] = $result;
                } else {
                    unset($_SESSION['intUserAuthID']);
                }
                return $result;
            } catch (Exception $e) {
                throw $e;
            }
        } elseif (isset($arrSession['OPENID_AUTH'])) {
            $key = 'openid';
            $value = sha1(Container_Config::getSecureByID('salt', 'Not Yet Set!!!')->getKey('value') . $arrSession['OPENID_AUTH']['url']);
            $createIfNotExist = true;
        } elseif ($objRequest->get_strUsername() != null && $objRequest->get_strPassword() != null) {
            $key = 'basicauth';
            $value = $objRequest->get_strUsername() . ':' . sha1(Container_Config::getSecureByID('salt', 'Not Yet Set!!!')->getKey('value') . $objRequest->get_strPassword());
        } elseif (Base_GeneralFunctions::getValue($objRequest->get_arrRqstParameters(), 'code', false, true) 
            && Base_GeneralFunctions::getValue($objRequest->get_arrRqstParameters(), 'code') != '%'
        ) {
            $key = 'codeonly';
            $value = '%:' . Base_GeneralFunctions::getValue($objRequest->get_arrRqstParameters(), 'code');
        } elseif (Base_GeneralFunctions::getValue($objRequest->get_arrRqstParameters(), 'username', false, true)
                && Base_GeneralFunctions::getValue($objRequest->get_arrRqstParameters(), 'password', false, true)
                ) {
            $key = 'basicauth';
            $value = Base_GeneralFunctions::getValue($objRequest->get_arrRqstParameters(), 'username') . ':' . sha1(Container_Config::getSecureByID('salt', 'Not Yet Set!!!')->getKey('value') . Base_GeneralFunctions::getValue($objRequest->get_arrRqstParameters(), 'password'));
            if (Base_GeneralFunctions::getValue($objRequest->get_arrRqstParameters(), 'register', false, true)) {
                $createIfNotExist = true;
            }
        }
        if (isset($key)) {
            try {
                $objDatabase = Container_Database::getConnection();
                $sql = "SELECT * FROM userauth WHERE enumAuthType = ? and strAuthValue like ? LIMIT 1";
                $query = $objDatabase->prepare($sql);
                $query->execute(array($key, $value));
                $result = $query->fetchObject($thisClassName);
                if ($result != false) {
                    $objCache->arrCache[$thisClassName]['id'][$result->getKey('intUserID')] = $result;
                    $_SESSION['intUserAuthID'] = $result->getKey('intUserAuthID');
                    $objCache->arrCache[$thisClassName]['current'] = $result;
                    if ($key == 'onetime') {
                        $sql = "DELETE FROM userauth WHERE $key = ? LIMIT 1";
                        $query = $objDatabase->prepare($sql);
                        $query->execute(array($value));
                    }
                } else {
                    if ($createIfNotExist === true) {
                        $result = new Object_User(true);
                        if ($result != false && isset($result->objUserAuthTemp)) {
                            Base_GeneralFunctions::startSession();
                            $_SESSION['intUserAuthID'] = $result->objUserAuthTemp->getKey('intUserAuthID');
                        }
                        return $result->objUserAuthTemp;
                    } else {
                        throw new Exception_AuthenticationFailed('User does not exist or password is incorrect.');
                    }
                }
                return $result;
            } catch (Exception $e) {
                throw $e;
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
        parent::__construct();
        if (! $isCreationAction) {
            return $this;
        }
        $objRequest = Container_Request::getRequest();
        $arrSession = $objRequest->get_arrSession();
        $this->reqCreatorToMod = false;
        if (Base_GeneralFunctions::getValue($objRequest->get_arrSession(), 'intUserAuthID', false, true)) {
            unset($_SESSION['intUserAuthID']);
        }
        if (isset($arrSession['OPENID_AUTH']['url'])) {
            $this->setKey('enumAuthType', 'openid');
            $this->setKey('strAuthValue', $arrSession['OPENID_AUTH']['url']);
        } elseif (
            Base_GeneralFunctions::getValue($objRequest->get_arrRqstParameters(), 'username', false, true) != false
            && Base_GeneralFunctions::getValue($objRequest->get_arrRqstParameters(), 'password', false, true) != false
        ) {
            if (count(Object_Userauth::brokerByColumnSearch('strAuthValue', Base_GeneralFunctions::getValue($objRequest->get_arrRqstParameters(), 'username') . ':%')) > 0) {
                throw new Exception_AuthenticationFailed("This username already exists, please select another");
            }
            $this->setKey('enumAuthType', 'basicauth');
            $this->setKey('strAuthValue', array('password' => Base_GeneralFunctions::getValue($objRequest->get_arrRqstParameters(), 'password'), 'username' => Base_GeneralFunctions::getValue($objRequest->get_arrRqstParameters(), 'username')));
        } elseif ($codeonly != false) {
            $this->setKey('enumAuthType', 'codeonly');
            $authString = '';
            while ($authString == '') {
                $authString = Base_GeneralFunctions::genRandStr(5, 9);
                if (count(Object_Userauth::brokerByColumnSearch('strAuthValue', '%:' . sha1(Container_Config::getSecureByID('salt', 'Not Yet Set!!!')->getKey('value') . $authString))) > 0) {
                    $authString == '';
                }
            }
            $this->setKey('strAuthValue', array('password' => $authString, 'username' => $codeonly));
        } elseif ($onetime == true) {
            $this->setKey('enumAuthType', 'onetime');
            $authString = '';
            while ($authString == '') {
                $authString = Base_GeneralFunctions::genRandStr(8, 12);
                if (count(Object_Userauth::brokerByColumnSearch('strAuthValue', '%:' . $authString)) > 0) {
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
            $self['strAuthValue'] = substr($this->strAuthValue, 0, 1) . str_repeat('.', strlen($this->strAuthValue) - 2) . substr($this->strAuthValue, -1);
            break;
        case 'codeonly':
            $string = explode(':', $this->strAuthValue);
            if (preg_match('/([^@]+)@([^@]+)/', $string[0], $matches) > 0) {
                $self['strAuthValue'] = substr($matches[1], 0, 1)
                    . str_repeat('.', strlen($matches[1]) - 2)
                    . substr($matches[1], -1)
                    . '@' 
                    . substr($matches[2], 0, 2) 
                    . str_repeat('.', strlen($matches[2]) - 4) 
                    . substr($matches[2], -2);
            } else {
                $self['strAuthValue'] = str_repeat('.', strlen($string[0]) - 6) . substr($string[0], -6);
            }
            break;
        }
        $self['strCleartext'] = $this->getCleartext();
        $thisUser = Object_User::brokerCurrent();
        if ($this->enumAuthType == 'codeonly' 
            && $thisUser != false 
            && $this->intUserID == $thisUser->getKey('intUserID')
        ) {
            $string = explode(':', $this->strAuthValue);
            $self['strCleartext'] = $string[1];
        }
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
        } elseif ($this->enumAuthType != 'codeonly'
            && $this->tmpCleartext == ''
            && ($user != false 
            && $user->getKey('intUserID') == $this->intUserID)
            || strtotime($this->lastChange) < strtotime("2 minutes ago")
        ) {
            $string = explode(':', $this->strAuthValue);
            $this->tmpCleartext = $string[1];
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