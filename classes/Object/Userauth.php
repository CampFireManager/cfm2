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
            foreach ($arrDBItems['enumAuthType']['options'] as $option) {
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
                $set = $request['username'] . ':' . sha1(Base_Config::getConfigSecure('salt') . $request['password']);
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
        $this_class = self::startNew(false);
        if (isset($objCache->arrCache[get_class($this_class)]['current'])
            && $objCache->arrCache[get_class($this_class)]['current'] != null
            && $objCache->arrCache[get_class($this_class)]['current'] != false
        ) {
            return $objCache->arrCache[get_class($this_class)]['current'];
        }
        Base_Session::start();
        $request = Base_Request::getRequest();
        if (isset($_SESSION['intUserAuthID']) && $_SESSION['intUserAuthID'] != '') {
            try {
                $db = Base_Database::getConnection();
                $sql = "SELECT * FROM userauth WHERE intUserAuthID = ? LIMIT 1";
                $query = $db->prepare($sql);
                $query->execute(array($_SESSION['intUserAuthID']));
                $result = $query->fetchObject(get_class($this_class));
                $objCache->arrCache[get_class($this_class)]['id'][$intUserID] = $result;
                $objCache->arrCache[get_class($this_class)]['current'] = $result;
                return $result;
            } catch (PDOException $e) {
                error_log("Error in SQL: " . $e->getMessage());
                return false;
            }
        } elseif (isset($_SESSION['OPENID_AUTH']) AND $_SESSION['OPENID_AUTH'] != false) {
            $key = 'openid';
            $value = sha1(Base_Config::getConfigSecure('salt') . $request['OPENID_AUTH']);
        } elseif (isset($request['username']) && $request['username'] != null && isset($request['password']) && $request['password'] != null) {
            $key = 'basicauth';
            $value = $request['username'] . ':' . sha1(Base_Config::getConfigSecure('salt') . $request['password']);
        } elseif (isset($request['code']) && $request['code'] != null) {
            $key = 'codeonly';
            $value = '%:' . sha1(Base_Config::getConfigSecure('salt') . $request['code']);
        } elseif (isset($request['onetime']) && $request['onetime'] != null) {
            $key = 'onetime';
            $value = 'onetime:' . sha1(Base_Config::getConfigSecure('salt') . $request['onetime']);
        }
        if (isset($key)) {
            try {
                $db = Base_Database::getConnection();
                $sql = "SELECT * FROM userauth WHERE enumAuthType = ? and strAuthValue = ? LIMIT 1";
                $query = $db->prepare($sql);
                $query->execute(array($key, $value));
                $result = $query->fetchObject(get_class($this_class));
                if ($result != false) {
                    $objCache->arrCache[get_class($this_class)]['id'][$intUserID] = $result;
                    $objCache->arrCache[get_class($this_class)]['current'] = $result;
                    if ($key == 'onetime') {
                        $sql = "DELETE FROM userauth WHERE $key = ? LIMIT 1";
                        $query = $db->prepare($sql);
                        $query->execute(array($value));
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
     * @param boolean $isReal   Perform Creation Actions (default true)
     * @param string  $codeonly The code associated to this type of authentication
     * @param boolean $onetime  Return a one-time unique code to use
     *
     * @return object
     */
    function startNew($isReal = true, $codeonly = false, $onetime = false)
    {
        $this_class = new self();
        if (! $isReal) {
            return $this_class;
        }
        Base_Session::start();
        $request = Base_Request::getRequest();
        if (isset($_SESSION['intUserAuthID'])) {
            unset($_SESSION['intUserAuthID']);
        }
        if (isset($_SESSION['OPENID_AUTH'])) {
            $this_class->set_enumAuthType('openid');
            $this_class->set_strAuthValue(sha1(Base_Config::getConfigSecure('salt') . $request['OPENID_AUTH']));
        } elseif (
            isset($request['requestUrlParameters']['username']) 
            && $request['requestUrlParameters']['username'] != null 
            && isset($request['requestUrlParameters']['password']) 
            && $request['requestUrlParameters']['password'] != null
        ) {
            $this_class->set_enumAuthType('basicauth');
            $this_class->set_strAuthValue($request['requestUrlParameters']['username'] . ':' . sha1(Base_Config::getConfigSecure('salt') . $request['requestUrlParameters']['password']));
        } elseif ($codeonly != false) {
            $this_class->set_enumAuthType('codeonly');
            $authString = '';
            while($authString == '') {
              $authString = Base_GeneralFunctions::genRandStr(5, 9);
              if (count(Object_Userauth::brokerByColumnSearch('strAuthValue', '%:' . sha1(Base_Config::getConfigSecure('salt') . $authString))) > 0) {
                  $authString == '';
              }
            }
            $this_class->set_strAuthValue($codeonly . ':' . sha1(Base_Config::getConfigSecure('salt') . $authString));
        } elseif ($onetime == true) {
            $this_class->set_enumAuthType('onetime');
            $authString = '';
            while($authString == '') {
              $authString = Base_GeneralFunctions::genRandStr(8, 12);
              if (count(Object_Userauth::brokerByColumnSearch('strAuthValue', '%:' . sha1(Base_Config::getConfigSecure('salt') . $authString))) > 0) {
                  $authString == '';
              }
            }
            $this_class->set_strAuthValue('onetime' . ':' . sha1(Base_Config::getConfigSecure('salt') . $authString));
        } else {
            return false;
        }
        $this_class->create();
        return $this_class;
    }
    
    function getSelf()
    {
        $self = parent::getSelf();
        switch ($this->enumAuthType) {
        case 'basicauth':
            $string = explode(':', $this->strAuthValue);
            $self['strAuthValue'] = $string[0];
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
    }
}
