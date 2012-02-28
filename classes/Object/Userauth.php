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

class Object_Userauth extends Base_GenericObject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
        'intUserID' => array('type' => 'int', 'length' => 11),
    	'enumAuthType' => array('type' => 'enum', 'options' => array('openid', 'basicauth', 'codeonly', 'onetime'), 'unique' => true),
        'strAuthValue' => array('type' => 'varchar', 'length' => '255', 'unique' => true)
    );
    protected $strDBTable = "userauth";
    protected $strDBKeyCol = "intUserAuthID";
    // Local Object Requirements
    protected $intUserAuthID = null;
    protected $intUserID = null;
    protected $enumAuthType = null;
    protected $strAuthValue = null;
    protected $strCleartext = null;

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
            $this->set_enumAuthType($value);
            break;
        case 'strAuthValue':
            if (is_array($value) && isset($value['username']) && isset($value['password'])) {
                $username = $value['username'];
                $password = $value['password'];
                $this->set_strAuthValue($password, $username);
            } elseif (is_array($value) && isset($value['password'])) {
                $password = $value['password'];
                $this->set_strAuthValue($password);
            } elseif (is_string($value)) {
                $this->set_strAuthValue($value);
            } else {
                return false;
            }
        default:
            parent::setKey($keyname, $value);
        }
    }
    
    /**
     * The function to set the enumAuthType from a list of pre-defined enums.
     *
     * @param string $set Value to set
     * 
     * @return void
     */
    private function set_enumAuthType($set = '')
    {
        if ($set != '' && $this->enumAuthType != $set) {
            $valid = false;
            foreach ($this->arrDBItems['enumAuthType']['options'] as $option) {
                if ($option == $set) {
                    $valid = true;
                }
            }
            if ($valid == true) {
                $this->enumAuthType = $set;
                $this->arrChanges['enumAuthType'] = true;
            }
        }
    }

    /**
     * The Authentication Credentials to commit to the database.
     *
     * @param string $password The password to set
     * @param string $username The optional username to set
     * 
     * @return void
     */
    function set_strAuthValue($password = '', $username = '')
    {
        if ($this->enumAuthType != null) {
            if ($this->enumAuthType == 'openid' || $this->enumAuthType == 'codeonly' || $this->enumAuthType == 'onetime') {
                $set = sha1(Base_Config::getConfigSecure('salt') . $password);
            } elseif ($this->enumAuthType == 'basicauth') {
                $set = $username . ':' . sha1(Base_Config::getConfigSecure('salt') . $password);
            }
            if ($set != '' && $this->strAuthValue != $set) {
                $this->strCleartext = $password;
                $this->strAuthValue = $set;
                $this->arrChanges['strAuthValue'] = true;
            }
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
        $this_class = new $this_class_name(false);
        $createIfNotExist = false;
        if (isset($objCache->arrCache[$this_class_name]['current'])
            && $objCache->arrCache[$this_class_name]['current'] != null
            && $objCache->arrCache[$this_class_name]['current'] != false
        ) {
            return $objCache->arrCache[$this_class_name]['current'];
        }
        Base_GeneralFunctions::sessionStart();
        $arrRequestData = Base_Request::getRequest();
        if (isset($_SESSION['intUserAuthID']) && $_SESSION['intUserAuthID'] != '') {
            try {
                $db = Base_Database::getConnection();
                $sql = "SELECT * FROM userauth WHERE intUserAuthID = ? LIMIT 1";
                $query = $db->prepare($sql);
                $query->execute(array($_SESSION['intUserAuthID']));
                $result = $query->fetchObject($this_class_name);
                $objCache->arrCache[$this_class_name]['id'][$intUserID] = $result;
                $objCache->arrCache[$this_class_name]['current'] = $result;
                return $result;
            } catch (PDOException $e) {
                error_log("Error in SQL: " . $e->getMessage());
                return false;
            }
        } elseif (isset($_SESSION['OPENID_AUTH']) AND $_SESSION['OPENID_AUTH'] != false) {
            $key = 'openid';
            $value = sha1(Base_Config::getConfigSecure('salt') . $arrRequestData['OPENID_AUTH']);
            $createIfNotExist = true;
        } elseif (isset($arrRequestData['username']) && $arrRequestData['username'] != null && isset($arrRequestData['password']) && $arrRequestData['password'] != null) {
            $key = 'basicauth';
            $value = $arrRequestData['username'] . ':' . sha1(Base_Config::getConfigSecure('salt') . $arrRequestData['password']);
        } elseif (isset($arrRequestData['code']) && $arrRequestData['code'] != null) {
            $key = 'codeonly';
            $value = '%:' . sha1(Base_Config::getConfigSecure('salt') . $arrRequestData['code']);
        } elseif (isset($arrRequestData['onetime']) && $arrRequestData['onetime'] != null) {
            $key = 'onetime';
            $value = 'onetime:' . sha1(Base_Config::getConfigSecure('salt') . $arrRequestData['onetime']);
        } elseif (isset($arrRequestData['requestUrlParameters']['login']) 
                && isset($arrRequestData['requestUrlParameters']['username']) 
                && isset($arrRequestData['requestUrlParameters']['password'])
                ) {
            $key = 'basicauth';
            $value = $arrRequestData['requestUrlParameters']['username'] . ':' . sha1(Base_Config::getConfigSecure('salt') . $arrRequestData['requestUrlParameters']['password']);
        } elseif (isset($arrRequestData['requestUrlParameters']['register']) 
                && isset($arrRequestData['requestUrlParameters']['username']) 
                && isset($arrRequestData['requestUrlParameters']['password'])
                ) {
            $key = 'basicauth';
            $value = $arrRequestData['requestUrlParameters']['username'] . ':' . sha1(Base_Config::getConfigSecure('salt') . $arrRequestData['requestUrlParameters']['password']);
            $createIfNotExist = true;
        }
        if (isset($key)) {
            try {
                $db = Base_Database::getConnection();
                $sql = "SELECT * FROM userauth WHERE enumAuthType = ? and strAuthValue = ? LIMIT 1";
                $query = $db->prepare($sql);
                $query->execute(array($key, $value));
                $result = $query->fetchObject($this_class_name);
                if ($result != false) {
                    $objCache->arrCache[$this_class_name]['id'][$intUserID] = $result;
                    $objCache->arrCache[$this_class_name]['current'] = $result;
                    if ($key == 'onetime') {
                        $sql = "DELETE FROM userauth WHERE $key = ? LIMIT 1";
                        $query = $db->prepare($sql);
                        $query->execute(array($value));
                    }
                } else {
                    if ($createIfNotExist === true) {
                        new Object_User();
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
     * @param boolean $isReal   Perform Creation Actions (default false)
     * @param string  $codeonly The code associated to this type of authentication
     * @param boolean $onetime  Return a one-time unique code to use
     *
     * @return object
     */
    function __construct($isReal = false, $codeonly = false, $onetime = false)
    {
        parent::__construct($isReal);
        if (! $isReal) {
            return $this;
        }
        Base_GeneralFunctions::sessionStart();
        $arrRequestData = Base_Request::getRequest();
        if (isset($_SESSION['intUserAuthID'])) {
            unset($_SESSION['intUserAuthID']);
        }
        if (isset($_SESSION['OPENID_AUTH'])) {
            $this_class->set_enumAuthType('openid');
            $this_class->set_strAuthValue($arrRequestData['OPENID_AUTH']);
        } elseif (
            isset($arrRequestData['requestUrlParameters']['username']) 
            && $arrRequestData['requestUrlParameters']['username'] != null 
            && isset($arrRequestData['requestUrlParameters']['password']) 
            && $arrRequestData['requestUrlParameters']['password'] != null
        ) {
            if (count(Object_Userauth::brokerByColumnSearch('strAuthValue', $arrRequestData['requestUrlParameters']['username'] . ':%')) > 0) {
                throw new Exception("This username already exists, please select another");
            }
            $this_class->set_enumAuthType('basicauth');
            $this_class->set_strAuthValue(Base_Config::getConfigSecure('salt') . $arrRequestData['requestUrlParameters']['password'], $arrRequestData['requestUrlParameters']['username']);
        } elseif ($codeonly != false) {
            $this_class->set_enumAuthType('codeonly');
            $authString = '';
            while ($authString == '') {
                $authString = Base_GeneralFunctions::genRandStr(5, 9);
                if (count(Object_Userauth::brokerByColumnSearch('strAuthValue', '%:' . sha1(Base_Config::getConfigSecure('salt') . $authString))) > 0) {
                    $authString == '';
                }
            }
            $this_class->set_strAuthValue($authString, $codeonly);
        } elseif ($onetime == true) {
            $this_class->set_enumAuthType('onetime');
            $authString = '';
            while ($authString == '') {
                $authString = Base_GeneralFunctions::genRandStr(8, 12);
                if (count(Object_Userauth::brokerByColumnSearch('strAuthValue', '%:' . sha1(Base_Config::getConfigSecure('salt') . $authString))) > 0) {
                    $authString == '';
                }
            }
            $this_class->set_strAuthValue($authString, 'onetime');
        } else {
            return false;
        }
        $this_class->create();
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
        return $self;
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
        array('intUserAuthID' => 1, 'intUserID' => 1, 'enumAuthType' => 'onetime', 'strAuthValue' => '4b2ddfb08ff0bd38d94320be391fcdac0f43baad'), // SHA1('salt' . 'abcd2346ACDE');
        array('intUserAuthID' => 2, 'intUserID' => 2, 'enumAuthType' => 'openid', 'strAuthValue' => 'a6fb734121aa9c075476e5e8aa745cd2934157fb'), // SHA1('salt' . 'http://www.openid.net');
        array('intUserAuthID' => 3, 'intUserID' => 2, 'enumAuthType' => 'basicauth', 'strAuthValue' => 'cfmadmin:59b3e8d637cf97edbe2384cf59cb7453dfe30789'), // SHA1('salt' . 'password');
        array('intUserAuthID' => 4, 'intUserID' => 3, 'enumAuthType' => 'codeonly', 'strAuthValue' => '+447777777777:6e32856b3ee3e41d267708a88081c40f1ac06b59'), // SHA1('salt' . 'codeonly');
        array('intUserAuthID' => 5, 'intUserID' => 3, 'enumAuthType' => 'codeonly', 'strAuthValue' => 'user@gmail.com:1e8a2a0da098b481b7c274d51597751142e42614'), // SHA1('salt' . 'email');
        array('intUserAuthID' => 6, 'intUserID' => 4, 'enumAuthType' => 'openid', 'strAuthValue' => '990d538923af9542484873551d65a01733abf252') // SHA1('salt' . 'http://www.google.com/accounts/o8/id');
    );    
}
