<?php

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

    function set_enumAuthType($set = '')
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

    function set_strAuthValue($password = '', $username = '')
    {
        if ($this->enumAuthType != null) {
            if ($this->enumAuthType == 'openid' || $this->enumAuthType == 'codeonly') {
                $set = sha1(Base_Config::getConfigSecure('salt') . $password);
            } elseif ($this->enumAuthType == 'basicauth') {
                $set = $username . ':' . sha1(Base_Config::getConfigSecure('salt') . $password);
            }
            if ($set != '' && $this->strAuthValue != $set) {
                $this->strAuthValue = $set;
                $this->arrChanges['strAuthValue'] = true;
            }
        }
    }

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
        Base_Session::start();
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
        Base_Session::start();
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
            $this_class->set_enumAuthType('basicauth');
            $this_class->set_strAuthValue(Base_Config::getConfigSecure('salt') . $arrRequestData['requestUrlParameters']['password'], $arrRequestData['requestUrlParameters']['username']);
        } elseif ($codeonly != false) {
            $this_class->set_enumAuthType('codeonly');
            $authString = '';
            while($authString == '') {
              $authString = Base_GeneralFunctions::genRandStr(5, 9);
              if (count(Object_Userauth::brokerByColumnSearch('strAuthValue', '%:' . sha1(Base_Config::getConfigSecure('salt') . $authString))) > 0) {
                  $authString == '';
              }
            }
            $this_class->set_strAuthValue($authString, $codeonly);
        } elseif ($onetime == true) {
            $this_class->set_enumAuthType('onetime');
            $authString = '';
            while($authString == '') {
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
