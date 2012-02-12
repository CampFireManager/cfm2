<?php

class Object_Userauth extends Base_Genericobject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
        'intUserID' => array('type' => 'int', 'length' => 11),
    	'enumAuthType' => array('type' => 'enum', 'options' => array('openid', 'basicauth', 'codeonly'), 'unique' => true),
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
                $set = sha1(base_config::getConfigGlobal('salt') . $password);
            } elseif ($this->enumAuthType == 'basicauth') {
                $set = $request['username'] . ':' . sha1(base_config::getConfigGlobal('salt') . $request['password']);
            }
            if ($set != '' && $this->strAuthValue != $set) {
                $this->strAuthValue = $set;
                $this->arrChanges['strAuthValue'] = true;
            }
        }
    }

    function brokerCurrent()
    {
        $objCache = base_cache::getHandler();
        $this_class = self::startNew(false);
        if (isset($objCache->arrCache[get_class($this_class)]['current'])
            && $objCache->arrCache[get_class($this_class)]['current'] != null
            && $objCache->arrCache[get_class($this_class)]['current'] != false
        ) {
            return $objCache->arrCache[get_class($this_class)]['current'];
        }
        base_session::start();
        $request = base_request::getRequest();
        if (isset($_SESSION['intUserAuthID']) && $_SESSION['intUserAuthID'] != '') {
            try {
                $db = base_database::getConnection();
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
            $value = sha1(base_config::getConfigGlobal('salt') . $request['OPENID_AUTH']);
        } elseif (isset($request['username']) && $request['username'] != null && isset($request['password']) && $request['password'] != null) {
            $key = 'basicauth';
            $value = $request['username'] . ':' . sha1(base_config::getConfigGlobal('salt') . $request['password']);
        }
        if (isset($key)) {
            try {
                $db = base_database::getConnection();
                $sql = "SELECT * FROM userauth WHERE $key = ? LIMIT 1";
                $query = $db->prepare($sql);
                $query->execute(array($value));
                $result = $query->fetchObject(get_class($this_class));
                $objCache->arrCache[get_class($this_class)]['id'][$intUserID] = $result;
                $objCache->arrCache[get_class($this_class)]['current'] = $result;
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
     * @param boolean $isReal Perform Creation Actions (default true)
     *
     * @return object
     */
    function startNew($isReal = true)
    {
        $this_class = new self();
        if (! $isReal) {
            return $this_class;
        }
        base_session::start();
        $request = base_request::getRequest();
        if (isset($_SESSION['intUserAuthID'])) {
            unset($_SESSION['intUserAuthID']);
        }
        if (isset($_SESSION['OPENID_AUTH'])) {
            $key = 'openid';
            $value = sha1(base_config::getConfigGlobal('salt') . $request['OPENID_AUTH']);
        } elseif (
            isset($request['requestUrlParameters']['username']) 
            && $request['requestUrlParameters']['username'] != null 
            && isset($request['requestUrlParameters']['password']) 
            && $request['requestUrlParameters']['password'] != null
        ) {
            $key = 'basicauth';
            $value = $request['requestUrlParameters']['username'] . ':' . sha1(base_config::getConfigSecure('salt') . $request['requestUrlParameters']['password']);
        }

    }
}
