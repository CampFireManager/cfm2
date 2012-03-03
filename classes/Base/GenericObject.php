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
 * This class provides all the object specific functions used throughout the site.
 * It is used as the basis for every object.
 *
 * @category Base_GenericObject
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Base_GenericObject
{
    /**
     * This is an array of database items. It is an array of arrays, where the
     * outer array has keys which make up the database table, and each key has
     * an array of database attributes, including the type, the length and 
     * whether the key is unique or not.
     * 
     * This array is used to validate whether updates apply to the database or
     * not, and to initialize the database.
     * 
     * @var array
     */
    protected $arrDBItems = array();
    /**
     * This defines the name of the database table. It is used in subsequent
     * queries.
     * 
     * @var string
     */
    protected $strDBTable = "";
    /**
     * This is name of the primary key column - it will be an integer, of length
     * 11 that auto increments.
     * 
     * @var string
     */
    protected $strDBKeyCol = "";
    /**
     * This array is used to compact update requests, by ensuring that only the
     * changes are sent to the database. Each key is processed, and running the
     * setKey() function will create a key in this table, with the value set to
     * true.
     * 
     * @var array
     */
    protected $arrChanges = array();
    /**
     * If this is set to true, when running the getSelf() function, it will
     * drill down into the linked tables to derive as much information as
     * possible, and return it all as an array.
     * 
     * @var boolean
     */
    protected $booleanFull = false;
    /**
     * Whenever this object is instantiated, it copies all of its values into
     * the $old array. This means, we can compare against the old column if
     * needed.
     * 
     * @var array
     */
    protected $old = array();
    /**
     * This variable, if set to true, will require the user making the chage to
     * be considered to be an administator to configure them. This relates to
     * key concerns around concrete values - such as the available rooms, slots
     * etc.
     * 
     * @var boolean
     */
    protected $mustBeAdminToModify = false;
    /**
     * This variable, if set to true, will require the user making the chage to
     * be either an admin to make the change, or the user who created the object.
     * 
     * @var boolean
     */
    protected $mustBeCreatorToModify = false;
    /**
     * This variable contains demonstration data for the extensions to each of
     * the Object_ classes, allowing you to create a full demonstration site
     * with relative ease. This array goes hand-in-hand with the $arrDBItems
     * array for initial set-ups.
     * 
     * @var array|null
     */
    protected $arrDemoData = null;
    
    /**
     * Get the object for the ID associated with a particular row
     *
     * @param integer $intID The Object ID to search for
     *
     * @return object UserObject for intUserID
     */
    static function brokerByID($intID = 0)
    {
        $objCache = Base_Cache::getHandler();
        $this_class_name = get_called_class();
        $this_class = new $this_class_name(false);
        if (0 + $intID > 0) {
            if (isset($objCache->arrCache[$this_class_name]['id'][$intID])) {
                return $objCache->arrCache[$this_class_name]['id'][$intID];
            }
            try {
                $db = Base_Database::getConnection();
                $sql = "SELECT * FROM {$this_class->strDBTable} WHERE {$this_class->strDBKeyCol} = ? LIMIT 1";
                $query = $db->prepare($sql);
                $query->execute(array($intID));
                $result = $query->fetchObject($this_class_name);
                if ($result == false) {
                    return false;
                } else {
                    $objCache->arrCache[$this_class_name]['id'][$intID] = $result;
                    return $result;
                }
            } catch(Exception $e) {
                error_log("Error brokering by ID: " . $e->getMessage());
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Get all objects by a particular search field
     *
     * @param string $column The column to search
     * @param string $value  The value to look for.
     * 
     * @return array The array of objects matching this search
     */
    static function brokerByColumnSearch($column = null, $value = null)
    {
        if ($column == null) {
            return false;
        }
        $objCache = Base_Cache::getHandler();
        $this_class_name = get_called_class();
        $this_class = new $this_class_name(false);
        $process = false;
        foreach ($this_class->arrDBItems as $db_item => $dummy) {
            if ($db_item == $column) {
                $process = true;
            }
        }
        if ($process == false) {
            return false;
        }
        $arrResult = array();
        try {
            $db = Base_Database::getConnection();
            $sql = "SELECT * FROM {$this_class->strDBTable} WHERE {$column} = ? ORDER BY {$this_class->strDBKeyCol} ASC";
            $query = $db->prepare($sql);
            $query->execute(array($value));
            $result = $query->fetchObject($this_class_name);
            if ($result == false) {
                return array();
            }
            while ($result != false) {
                $arrResult[] = $result;
                $objCache->arrCache[$this_class_name]['id'][$result->getKey($this_class->strDBKeyCol)] = $result;
                $result = $query->fetchObject($this_class_name);
            }
            return $arrResult;
        } catch(PDOException $e) {
            error_log('Error running SQL Query: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get a tally of the number of objects by a particular search field
     *
     * @param string $column The column to search
     * @param string $value  The value to look for.
     * 
     * @return integer The number of rows matching the search criteria
     */
    static function countByColumnSearch($column = null, $value = null)
    {
        if ($column == null) {
            return false;
        }
        $objCache = Base_Cache::getHandler();
        $this_class_name = get_called_class();
        $this_class = new $this_class_name(false);
        $process = false;
        foreach ($this_class->arrDBItems as $db_item => $dummy) {
            if ($db_item == $column) {
                $process = true;
            }
        }
        if ($process == false) {
            return false;
        }
        try {
            $db = Base_Database::getConnection();
            $sql = "SELECT count({$this_class->strDBKeyCol}) FROM {$this_class->strDBTable} WHERE {$column} = ?";
            $query = $db->prepare($sql);
            $query->execute(array($value));
            $result = $query->fetchColumn();
            return $result;
        } catch(PDOException $e) {
            error_log('Error running SQL Query: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all objects by a particular search field
     *
     * @return array The array of objects matching this search
     */
    static function brokerAll()
    {
        $objCache = Base_Cache::getHandler();
        $this_class_name = get_called_class();
        $this_class = new $this_class_name(false);
        $arrResult = array();
        try {
            $db = Base_Database::getConnection();
            $sql = "SELECT * FROM {$this_class->strDBTable} ORDER BY {$this_class->strDBKeyCol} ASC";
            $query = $db->prepare($sql);
            $query->execute();
            $result = $query->fetchObject($this_class_name);
            while ($result != false) {
                $arrResult[] = $result;
                $objCache->arrCache[$this_class_name]['id'][$result->getKey($this_class->strDBKeyCol)] = $result;
                $result = $query->fetchObject($this_class_name);
            }
            return $arrResult;
        } catch(PDOException $e) {
            error_log('Error running SQL Query: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get a tally of the number of rows in a table
     *
     * @return integer The number of rows in the table
     */
    static function countAll()
    {
        $objCache = Base_Cache::getHandler();
        $this_class_name = get_called_class();
        $this_class = new $this_class_name(false);
        try {
            $db = Base_Database::getConnection();
            $sql = "SELECT count({$this_class->strDBKeyCol}) FROM {$this_class->strDBTable}";
            $query = $db->prepare($sql);
            $query->execute();
            $result = $query->fetchColumn();
            return $result;
        } catch(PDOException $e) {
            error_log('Error running SQL Query: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * This function returns the value of the primary key
     *
     * @return integer
     */
    public function getPrimaryKeyValue()
    {
        $primaryKey = $this_class->strDBKeyCol;
        return $this->$primaryKey;
    }
    
    /**
     * Set booleanFull to this value - expands the existing object to include it's
     * component parts if true.
     *
     * @param boolean $full Set the booleanFull value to this
     *
     * @return void
     */
    function setFull($full)
    {
        $this->booleanFull = Base_GeneralFunctions::asBoolean($full);
    }

    /**
     * Get the value of $this->booleanFull
     *
     * @return boolean
     */
    function getFull()
    {
        return (boolean) $this->booleanFull;
    }

    /**
     * This function ensures that the keyname to be set is one of the recognised database values
     * then sets it, and updates the "arrChanges" array to show it's ready to be committed.
     *
     * @param string $keyname The key to modify
     * @param any    $value   The value to store
     * 
     * @return void
     */
    function setKey($keyname = '', $value = '')
    {
        if (array_key_exists($keyname, $this->arrDBItems) or $keyname == $this->strDBKeyCol) {
            if ($value != '' && $this->$keyname != $value) {
                $this->$keyname = $value;
                $this->arrChanges[$keyname] = true;
            }
        }
    }
    
    /**
     * Return the value of the Object's key, provided it's in the list of approved values
     *
     * @param string $keyname The key to search for
     * 
     * @return any The value from the object
     */
    function getKey($keyname = '')
    {
        if (array_key_exists($keyname, $this->arrDBItems) or $keyname == $this->strDBKeyCol) {
            return $this->$keyname;
        }
    }
    
    /**
     * Ensure that all database items are backed up before processing
     *
     * @param boolean $isCreationAction Used to determine whether to 
     * post-process the PDO object, to pre-process a creation action or, as in
     * this case, ignored.
     * 
     * @return object This class.
     */
    function __construct($isCreationAction = false)
    {
        if (isset($this->arrDBItems) and is_array($this->arrDBItems) and count($this->arrDBItems) > 0) {
            foreach ($this->arrDBItems as $item=>$dummy) {
                $this->old[$item] = $this->$item;
            }
        }
        return $this;
    }

    /**
     * Commit any changes to the database
     *
     * @return void
     */
    function write()
    {
        if ($this->mustBeAdminToModify
            && ((Object_User::brokerCurrent() != false 
            && Object_User::brokerCurrent()->getKey('isAdmin') == false) 
            || Object_User::brokerCurrent() == false)
        ) {
            return false;
        }
        if ($this->mustBeCreatorToModify
            && isset($this->arrDBItems['intUserID'])
            && ((Object_User::brokerCurrent() != false
            && Object_User::brokerCurrent()->getKey('intUserID') != $this->intUserID)
            || (Object_User::brokerCurrent() != false
            && Object_User::brokerCurrent()->getKey('isWorker') == false)
            || (Object_User::brokerCurrent() != false
            && Object_User::brokerCurrent()->getKey('isAdmin') == false)
            || Object_User::brokerCurrent() == false)
        ) {
            return false;
        }
        if (count($this->arrChanges) > 0) {
            $sql = '';
            $where = '';
            if (isset($this->strDBKeyCol) and $this->strDBKeyCol != '') {
                $strDBKeyCol = $this->strDBKeyCol;
                $values[$strDBKeyCol] = $this->$strDBKeyCol;
                $where = "{$this->strDBKeyCol} = :{$this->strDBKeyCol}";
            } elseif (isset($this->arrDBKeyCol) and is_array($this->arrDBKeyCol) and count($this->arrDBKeyCol) > 0) {
                foreach ($this->arrDBKeyCol as $keycol=>$dummy) {
                    if ($where != '') {
                        $where .= ' AND ';
                    }
                    $values["old$keycol"] = $this->old[$keycol];
                    $where .= "$keycol = :old$keycol";
                }
            }
            foreach ($this->arrChanges as $change_key=>$change_value) {
                if ($change_value == true and isset($this->arrDBItems[$change_key])) {
                    if ($sql != '') {
                        $sql .= ", ";
                    }
                    $sql .= "$change_key = :$change_key";
                    $values[$change_key] = $this->$change_key;
                }
            }
            $full_sql = "UPDATE {$this->strDBTable} SET $sql WHERE $where";
            try {
                $db = Base_Database::getConnection(true);
                $query = $db->prepare($full_sql);
                $query->execute($values);
                $this->sql = $full_sql;
                $this->sql_value = $values;
                Base_Hook::triggerHook('updateRecord', $this);
                return true;
            } catch(Exception $e) {
                error_log("Error writing: " . $e->getMessage());
                return false;
            }
        }
    }

    /**
     * Create the object
     *
     * @return boolean status of the create operation
     */
    function create()
    {
        if ($this->mustBeAdminToModify
            && ((Object_User::brokerCurrent() != false 
            && Object_User::brokerCurrent()->getKey('isAdmin') == false) 
            || Object_User::brokerCurrent() == false)
        ) {
            return false;
        }
        $this->arrChanges = array();
        $keys = '';
        $key_place = '';
        foreach ($this->arrDBItems as $field_name => $dummy) {
            if ($keys != '') {
                $keys .= ', ';
                $key_place .= ', ';
            }
            $keys .= $field_name;
            $key_place .= ":$field_name";
            $values[$field_name] = $this->$field_name;
        }
        $full_sql = "INSERT INTO {$this->strDBTable} ($keys) VALUES ($key_place)";
        try {
            $db = Base_Database::getConnection(true);
            $query = $db->prepare($full_sql);
            $query->execute($values);
            if ($this->strDBKeyCol != '') {
                $key = $this->strDBKeyCol;
                $this->$key = $db->lastInsertId();
            }
            $this->sql = $full_sql;
            $this->sql_value = $values;
            Base_Hook::triggerHook('createRecord', $this);
            return true;
        } catch (PDOException $e) {
            error_log("Error creating: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a row from the relevant table
     * 
     * @return boolean Whether there was an error deleting the row
     */
    function delete()
    {
        if ($this->mustBeAdminToModify
            && ((Object_User::brokerCurrent() != false 
            && Object_User::brokerCurrent()->getKey('isAdmin') == false) 
            || Object_User::brokerCurrent() == false)
        ) {
            return false;
        }
        if ($this->mustBeCreatorToModify
            && isset($this->arrDBItems['intUserID'])
            && ((Object_User::brokerCurrent() != false
            && Object_User::brokerCurrent()->getKey('intUserID') != $this->intUserID)
            || (Object_User::brokerCurrent() != false
            && Object_User::brokerCurrent()->getKey('isAdmin') == false)
            || Object_User::brokerCurrent() == false)
        ) {
            return false;
        }
        $sql = "DELETE FROM {$this->strDBTable} WHERE {$this->strDBKeyCol} = ?";
        try {
            $db = Base_Database::getConnection(true);
            $query = $db->prepare($sql);
            $key = $this->strDBKeyCol;
            $query->execute(array($this->$key));
            $this->sql = $sql;
            $this->sql_value = $this->$key;
            Base_Hook::triggerHook('deleteRecord', $this);
            return true;
        } catch (PDOException $e) {
            error_log("Error deleting: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * This is used to first initialize the tables of the database.
     * 
     * @return boolean State of table creation
     */
    function initialize()
    {
        $unique_key = '';
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->strDBTable}` (";
        if ($this->strDBKeyCol != '') {
            $sql .= "`{$this->strDBKeyCol}` int(11) NOT NULL AUTO_INCREMENT";
        }
        foreach ($this->arrDBItems as $field_name => $settings) {
            if (isset($settings['null'])) {
                if ($settings['null']) {
                    $isNull = "NULL";
                } else {
                    $isNull = "NOT NULL";
                }
            } else {
                $isNull = "DEFAULT NULL";
            }
            if ($settings['type'] == 'text') {
                $sql .= ", `{$field_name}` text $isNull";
            } elseif ($settings['type'] == 'enum') {
                $options = '';
                foreach ($settings['options'] as $option) {
                    if ($options != '') {
                        $options .= ',';
                    }
                    $options .= "'$option'";
                }
                $sql .= ", `{$field_name}` enum({$options}) $isNull";
            } elseif (isset($settings['length'])) {
                $sql .= ", `{$field_name}` {$settings['type']}({$settings['length']})  $isNull";
            } else {
                $sql .= ", `{$field_name}` {$settings['type']} $isNull";
            }
            if (isset($settings['unique'])) {
                if ($unique_key != '') {
                    $unique_key .= ',';
                }
                $unique_key .= "`{$field_name}`";
            }
        }
        if ($this->strDBKeyCol != '') {
            $sql .= ", PRIMARY KEY (`{$this->strDBKeyCol}`)";
        }
        if ($unique_key != '') {
            $sql .= ", UNIQUE KEY `unique_key` ({$unique_key})";
        }
        $sql .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        try {
            $db = Base_Database::getConnection(true);
            $db->exec($sql);
            return true;
        } catch (PDOException $e) {
            error_log("Error initializing table: " . $e->getMessage() . '; Tried ' . $sql);
            return false;
        }
    }

    /**
     * This is used to first initialize the tables of the database. It then adds a set of demo data.
     * 
     * @return boolean State of table creation
     */
    function initializeDemo()
    {
        $sql = "DROP TABLE IF EXISTS `{$this->strDBTable}`";
        try {
            $db = Base_Database::getConnection(true);
            $db->exec($sql);
        } catch (PDOException $e) {
            error_log("Error dropping table: " . $e->getMessage() . '; Tried ' . $sql);
        }
        $this->initialize();
        if ($this->arrDemoData == null || !is_array($this->arrDemoData) || count($this->arrDemoData) == 0) {
            return false;
        }
        $className = get_called_class();
        foreach ($this->arrDemoData as $entry) {
            $object = new $className(false);
            $object->mustBeAdminToModify = false;
            $object->mustBeCreatorToModify = false;
            foreach ($entry as $key => $value) {
                $object->setKey($key, $value);
            }
            $object->create();
        }
    }

    
    /**
     * Return an array of the collected or created data.
     *
     * @return array A mixed array of these items
     */
    function getSelf()
    {
        if ($this->strDBKeyCol != '') {
            $key = $this->strDBKeyCol;
            $return[$key] = $this->$key;
        }
        foreach ($this->arrDBItems as $key => $dummy) {
            $return[$key] = $this->$key;
        }
        if ($this->mustBeAdminToModify
            && ((Object_User::brokerCurrent() != false 
            && Object_User::brokerCurrent()->getKey('isAdmin') == false) 
            || Object_User::brokerCurrent() == false)
        ) {
            $return['isEditable'] = array();
        } elseif ($this->mustBeCreatorToModify
            && isset($this->arrDBItems['intUserID'])
            && ((Object_User::brokerCurrent() != false
            && Object_User::brokerCurrent()->getKey('intUserID') != $this->intUserID)
            || (Object_User::brokerCurrent() != false
            && Object_User::brokerCurrent()->getKey('isWorker') == false)
            || (Object_User::brokerCurrent() != false
            && Object_User::brokerCurrent()->getKey('isAdmin') == false)
            || Object_User::brokerCurrent() == false)
        ) {
            $return['isEditable'] = array();
        } else {
            foreach ($this->arrDBItems as $key=>$value) {
                $return['isEditable'][$key] = $value['type'];
            }
        }

        return $return;
    }
}