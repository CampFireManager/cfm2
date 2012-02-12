<?php

class object_user extends base_genericobject
{
    // Generic Object Requirements
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
        $objCache = base_cache::getHandler();
        $this_class = self::startNew();
        if (
            isset($objCache->arrCache[get_class($this_class)]['current'])
            && $objCache->arrCache[get_class($this_class)]['current'] != null
            && $objCache->arrCache[get_class($this_class)]['current'] != false
            ) {
            return $objCache->arrCache[get_class($this_class)]['current'];
        }
        $user = object_userauth::brokerCurrent();
        if ($user !== false) {
            $intUserID = $user->get_key('intUserID');
        } else {
            return false;
        }
        try {
            $db = base_database::getConnection();
            $sql = "SELECT * FROM {$this_class->strDBTable} WHERE {$this_class->strDBKeyCol} = ? LIMIT 1";
            $query = $db->prepare($sql);
            $query->execute(array($intUserID));
            $result = $query->fetchObject(get_class($this_class));
            $objCache->arrCache[get_class($this_class)]['id'][$intUserID] = $result;
            $objCache->arrCache[get_class($this_class)]['current'] = $result;
            return $result;
        } catch(PDOException $e) {
            error_log("SQL Error: " . $e->getMessage());
            return false;
        }
    }
}
