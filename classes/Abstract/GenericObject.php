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
 * @category Abstract_GenericObject
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

abstract class Abstract_GenericObject implements Interface_Object
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
    protected $_arrDBItems = array();
    /**
     * This defines the name of the database table. It is used in subsequent
     * queries.
     * 
     * @var string
     */
    protected $_strDBTable = "";
    /**
     * This is name of the primary key column - it will be an integer, of length
     * 11 that auto increments.
     * 
     * @var string
     */
    protected $_strDBKeyCol = "";
    /**
     * This array is used to compact update requests, by ensuring that only the
     * changes are sent to the database. Each key is processed, and running the
     * setKey() function will create a key in this table, with the value set to
     * true.
     * 
     * @var array
     */
    protected $_arrChanges = array();
    /**
     * If this is set to true, when running the getSelf() function, it will
     * drill down into the linked tables to derive as much information as
     * possible, and return it all as an array.
     * 
     * @var boolean
     */
    protected $_booleanFull = false;
    /**
     * Whenever this object is instantiated, it copies all of its values into
     * the $_old array. This means, we can compare against the old column if
     * needed.
     * 
     * @var array
     */
    protected $_old = array();
    /**
     * This variable, if set to true, will require the user making the chage to
     * be considered to be an administator to configure them. This relates to
     * key concerns around concrete values - such as the available rooms, slots
     * etc.
     * 
     * @var boolean
     */
    protected $_reqAdminToMod = false;
    /**
     * This variable, if set to true, will require the user making the chage to
     * be either an admin to make the change, or the user who created the object.
     * 
     * @var boolean
     */
    protected $_reqCreatorToMod = false;
    /**
     * This variable contains demonstration data for the extensions to each of
     * the Object_ classes, allowing you to create a full demonstration site
     * with relative ease. This array goes hand-in-hand with the $_arrDBItems
     * array for initial set-ups.
     * 
     * @var array|null
     */
    protected $_arrDemoData = null;
    /**
     * Error message hand back. To hand back thrown error messages from new
     * instances of objects where we don't want code to be stopped.
     *
     * @var string|boolean
     */
    protected $_errorMessageReturn = false;

    /**
     * Ensure that all database items are backed up before processing.
     *
     * This is our usual construct method for all extended classes.
     *
     * @return object This class.
     */
    function __construct()
    {
        if (func_num_args() > 0) {
            throw new BadFunctionCallException("This function does not accept parameters.");
        }
        if (isset($this->_arrDBItems) and is_array($this->_arrDBItems) and count($this->_arrDBItems) > 0) {
            foreach ($this->_arrDBItems as $item=>$dummy) {
                $this->_old[$item] = $this->$item;
            }
        }
        return $this;
    }

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
        $thisClassName = get_called_class();
        $thisClass = new $thisClassName(false);
        if ((integer) $intID > 0) {
            if (isset($objCache->arrCache[$thisClassName]['id'][(string) $intID])) {
                return $objCache->arrCache[$thisClassName]['id'][(string) $intID];
            }
            try {
                $objDatabase = Container_Database::getConnection();
                $sql = Container_Database::getSqlString(
                    array(
                        'sql' => "SELECT * FROM {$thisClass->_strDBTable} WHERE {$thisClass->_strDBKeyCol} = ? LIMIT 1"
                    )
                );
                $query = $objDatabase->prepare($sql);
                $query->execute(array($intID));
                $result = $query->fetchObject($thisClassName);
                if ($result == false) {
                    return false;
                } else {
                    $objCache->arrCache[$thisClassName]['id'][$intID] = $result;
                    return $result;
                }
            } catch(Exception $e) {
                throw $e;
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
        $thisClassName = get_called_class();
        $thisClass = new $thisClassName(false);
        $process = false;
        foreach ($thisClass->_arrDBItems as $dbItem => $dummy) {
            if ($dbItem == $column) {
                $process = true;
            }
        }
        if ($process == false) {
            return false;
        }
        $arrResult = array();
        try {
            $objDatabase = Container_Database::getConnection();
            if ($value == '%') {
                $sql = Container_Database::getSqlString(
                    array(
                        'sql' => "SELECT * FROM {$thisClass->_strDBTable} WHERE {$column} IS NOT NULL ORDER BY {$thisClass->_strDBKeyCol}"
                    )
                );
                $query = $objDatabase->prepare($sql);
                $query->execute();
            } else {
                $sql = Container_Database::getSqlString(
                    array(
                        'sql' => "SELECT * FROM {$thisClass->_strDBTable} WHERE {$column} = ? ORDER BY {$thisClass->_strDBKeyCol}"
                    )
                );
                $query = $objDatabase->prepare($sql);
                $query->execute(array($value));
            }
            $result = $query->fetchObject($thisClassName);
            if ($result == false) {
                return array();
            }
            while ($result != false) {
                $arrResult[] = $result;
                $objCache->arrCache[$thisClassName]['id'][$result->getKey($thisClass->_strDBKeyCol)] = $result;
                $result = $query->fetchObject($thisClassName);
            }
            return $arrResult;
        } catch(Exception $e) {
            throw $e;
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
        $thisClassName = get_called_class();
        $thisClass = new $thisClassName(false);
        $process = false;
        foreach ($thisClass->_arrDBItems as $dbItem => $dummy) {
            if ($dbItem == $column) {
                $process = true;
            }
        }
        if ($process == false) {
            return false;
        }
        try {
            $objDatabase = Container_Database::getConnection();
            $sql = Container_Database::getSqlString(
                array(
                    'sql' => "SELECT count({$thisClass->_strDBKeyCol}) FROM {$thisClass->_strDBTable} WHERE {$column} = ?"
                )
            );
            $query = $objDatabase->prepare($sql);
            $query->execute(array($value));
            $result = $query->fetchColumn();
            return $result;
        } catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * Get the most recent lastChange date based on the column to search
     *
     * @param string $column The column to search
     * @param string $value  The value to look for.
     * 
     * @return datetime The most recent datetime string matching the search criteria
     */
    static function lastChangeByColumnSearch($column = null, $value = null)
    {
        if ($column == null) {
            return false;
        }
        $objCache = Base_Cache::getHandler();
        $thisClassName = get_called_class();
        $thisClass = new $thisClassName(false);
        $process = false;
        if (!isset($thisClass->_arrDBItems['lastChange'])) {
            return false;
        }
        foreach ($thisClass->_arrDBItems as $dbItem => $dummy) {
            if ($dbItem == $column) {
                $process = true;
            }
        }
        if ($process == false) {
            return false;
        }
        try {
            $objDatabase = Container_Database::getConnection();
            $sql = Container_Database::getSqlString(
                array(
                    'sql' => "SELECT max(lastChange) FROM {$thisClass->_strDBTable} WHERE {$column} = ?"
                )
            );
            $query = $objDatabase->prepare($sql);
            $query->execute(array($value));
            $result = $query->fetchColumn();
            return $result;
        } catch(Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Get all objects in this table
     * 
     * @return array The array of objects matching this search
     */
    static function brokerAll()
    {
        $objCache = Base_Cache::getHandler();
        $thisClassName = get_called_class();
        $thisClass = new $thisClassName();
        $arrResult = array();
        try {
            $objDatabase = Container_Database::getConnection();
            if ($thisClass->_strDBKeyCol != '') {
                $sql = Container_Database::getSqlString(
                    array(
                        'sql' => "SELECT * FROM {$thisClass->_strDBTable} ORDER BY {$thisClass->_strDBKeyCol}"
                    )
                );
            } else {
                $sql = Container_Database::getSqlString(
                    array(
                        'sql' => "SELECT * FROM {$thisClass->_strDBTable}"
                    )
                );
            }
            $query = $objDatabase->prepare($sql);
            $query->execute();
            $result = $query->fetchObject($thisClassName);
            while ($result != false) {
                $arrResult[] = $result;
                if (isset($thisClass->_strDBKeyCol) 
                    && $thisClass->_strDBKeyCol != '' 
                    && $thisClass->_strDBKeyCol != null
                ) {
                    $objCache->arrCache[$thisClassName]['id'][$result->getKey($thisClass->_strDBKeyCol)] = $result;
                } else {
                    $objCache->arrCache[$thisClassName]['id'][$result->getKey('key')] = $result;
                }
                $result = $query->fetchObject($thisClassName);
            }
            return $arrResult;
        } catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * Get the most recent "lastChange" datetime from this table
     * 
     * @return datetime The most recent lastChange date from this whole table
     */
    static function lastChangeAll()
    {
        $objCache = Base_Cache::getHandler();
        $thisClassName = get_called_class();
        $thisClass = new $thisClassName(false);
        $arrResult = array();
        try {
            $objDatabase = Container_Database::getConnection();
            $sql = Container_Database::getSqlString(
                array(
                    'sql' => "SELECT max(lastChange) FROM {$thisClass->_strDBTable}"
                )
            );
            $query = $objDatabase->prepare($sql);
            $query->execute();
            $result = $query->fetchColumn();
            return $arrResult;
        } catch(Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Get a tally of the number of rows in a table
     * 
     * @return integer The number of rows in the table
     */
    static function countAll()
    {
        $thisClassName = get_called_class();
        $thisClass = new $thisClassName(false);
        try {
            $objDatabase = Container_Database::getConnection();
            $sql = Container_Database::getSqlString(
                array(
                    'sql' => "SELECT count({$thisClass->_strDBKeyCol}) FROM {$thisClass->_strDBTable}"
                )
            );
            $query = $objDatabase->prepare($sql);
            $query->execute();
            $result = $query->fetchColumn();
            return $result;
        } catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * This function returns the value of the primary key
     *
     * @return integer
     */
    public function getPrimaryKeyValue()
    {
        $primaryKey = $this->_strDBKeyCol;
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
        $this->_booleanFull = Base_GeneralFunctions::asBoolean($full);
    }

    /**
     * Get the value of $this->booleanFull
     *
     * @return boolean
     */
    function isFull()
    {
        return (boolean) $this->_booleanFull;
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
        // Only the create and write functions should set the lastChange value.
        if ($keyname != 'lastChange') {
            if (array_key_exists($keyname, $this->_arrDBItems) 
                || $keyname == $this->_strDBKeyCol
            ) {
                if ($value != '' && $this->$keyname != $value) {
                    $this->$keyname = $value;
                    $this->_arrChanges[$keyname] = true;
                }
            }
            $protectedkeyname = '_' . $keyname;
            if (array_key_exists($protectedkeyname, $this->_arrDBItems) 
                || $protectedkeyname == $this->_strDBKeyCol
            ) {
                if ($value != '' && $this->$protectedkeyname != $value) {
                    $this->$protectedkeyname = $value;
                    $this->_arrChanges[$keyname] = true;
                }
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
        if ((array_key_exists($keyname, $this->_arrDBItems) || $keyname == $this->_strDBKeyCol) && (isset($this->$keyname) || $this->$keyname != null)) {
            return $this->$keyname;
        } else {
            return null;
        }
    }

    /**
     * Commit any changes to the database
     * 
     * @return void
     */
    function write()
    {
        try {
            if ($this->_reqAdminToMod
                && ((Object_User::brokerCurrent() != false 
                && Object_User::brokerCurrent()->getKey('isAdmin') == false) 
                || Object_User::brokerCurrent() == false)
            ) {
                return false;
            }
            if ($this->_reqCreatorToMod
                && isset($this->_arrDBItems['intUserID'])
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
            $this->lastChange = date('Y-m-d H:i:s');
            $this->_arrChanges['lastChange'] = true;
            if (count($this->_arrChanges) > 0) {
                $objDatabase = Container_Database::getConnection(true);
                $sql = '';
                $where = '';
                if (isset($this->_strDBKeyCol) and $this->_strDBKeyCol != '') {
                    $_strDBKeyCol = $this->_strDBKeyCol;
                    $values[$_strDBKeyCol] = $this->$_strDBKeyCol;
                    $where = Container_Database::getSqlString(
                        array(
                            'sql' => "{$this->_strDBKeyCol} = :{$this->_strDBKeyCol}"
                        )
                    );
                } elseif (isset($this->arrDBKeyCol) and is_array($this->arrDBKeyCol) and count($this->arrDBKeyCol) > 0) {
                    foreach ($this->arrDBKeyCol as $keycol => $dummy) {
                        if ($where != '') {
                            $where .= ' AND ';
                        }
                        $values["old$keycol"] = $this->_old[$keycol];
                        $where .= Container_Database::getSqlString(
                            array(
                                'sql' => "$keycol = :old$keycol"
                            )
                        );
                    }
                } else {
                    foreach ($this->_arrDBItems as $keycol => $dummy) {
                        if ($where != '') {
                            $where .= ' AND ';
                        }
                        $values["old$keycol"] = $this->_old[$keycol];
                        $where .= Container_Database::getSqlString(
                            array(
                                'sql' => "$keycol = :old$keycol"
                            )
                        );
                    }
                }
                foreach ($this->_arrChanges as $change_key => $change_value) {
                    if ($change_value == true and isset($this->_arrDBItems[$change_key])) {
                        if ($sql != '') {
                            $sql .= ", ";
                        }
                        $sql .= "$change_key = :$change_key";
                        $values[$change_key] = $this->$change_key;
                    }
                }
                $full_sql = Container_Database::getSqlString(
                    array(
                        'sql' => "UPDATE {$this->_strDBTable} SET $sql WHERE $where"
                    )
                );
                $query = $objDatabase->prepare($full_sql);
                $query->execute($values);
                $this->sql = $full_sql;
                $this->sql_value = $values;
                Container_Hook::Load()->triggerHook('updateRecord', $this);
                return true;
            }
        } catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * Create the object
     * 
     * @return boolean status of the create operation
     */
    function create()
    {
        if ($this->_reqAdminToMod
            && ((Object_User::brokerCurrent() != false 
            && Object_User::brokerCurrent()->getKey('isAdmin') == false) 
            || Object_User::brokerCurrent() == false)
        ) {
            return false;
        }
        $this->lastChange = date('Y-m-d H:i:s');
        $this->_arrChanges = array();
        $keys = '';
        $key_place = '';
        foreach ($this->_arrDBItems as $field_name => $dummy) {
            if ($keys != '') {
                $keys .= ', ';
                $key_place .= ', ';
            }
            $keys .= $field_name;
            $key_place .= ":$field_name";
            $values[$field_name] = $this->$field_name;
        }
        try {
            $objDatabase = Container_Database::getConnection(true);
            $full_sql = Container_Database::getSqlString(
                array(
                    'sql' => "INSERT INTO {$this->_strDBTable} ($keys) VALUES ($key_place)"
                )
            );
            $query = $objDatabase->prepare($full_sql);
            $query->execute($values);
            if ($this->_strDBKeyCol != '') {
                $key = $this->_strDBKeyCol;
                $this->$key = $objDatabase->lastInsertId();
            }
            $this->sql = $full_sql;
            $this->sql_value = $values;
            Container_Hook::Load()->triggerHook('createRecord', $this);
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete a row from the relevant table
     * 
     * @return boolean Whether there was an error deleting the row
     */
    function delete()
    {
        if ($this->_reqAdminToMod
            && ((Object_User::brokerCurrent() != false 
            && Object_User::brokerCurrent()->getKey('isAdmin') == false) 
            || Object_User::brokerCurrent() == false)
        ) {
            return false;
        }
        if ($this->_reqCreatorToMod
            && isset($this->_arrDBItems['intUserID'])
            && ((Object_User::brokerCurrent() != false
            && Object_User::brokerCurrent()->getKey('intUserID') != $this->intUserID)
            || (Object_User::brokerCurrent() != false
            && Object_User::brokerCurrent()->getKey('isAdmin') == false)
            || Object_User::brokerCurrent() == false)
        ) {
            return false;
        }
        try {
            $objDatabase = Container_Database::getConnection(true);
            $sql = Container_Database::getSqlString(
                array(
                    'sql' => "DELETE FROM {$this->_strDBTable} WHERE {$this->_strDBKeyCol} = ?"
                )
            );
            $query = $objDatabase->prepare($sql);
            $key = $this->_strDBKeyCol;
            $query->execute(array($this->$key));
            $this->sql = $sql;
            $this->sql_value = $this->$key;
            Container_Hook::Load()->triggerHook('deleteRecord', $this);
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * This is used to first initialize the tables of the database.
     * 
     * @return boolean State of table creation
     */
    function initialize()
    {
        try {
            $objDatabase = Container_Database::getConnection(true);
            $unique_key = '';
            $sql = Container_Database::getSqlString(
                array(
                    'sql' => "CREATE TABLE IF NOT EXISTS `{$this->_strDBTable}` ("
                )
            );
            $field_data = '';
            if ($this->_strDBKeyCol != '') {
                $field_data .= Container_Database::getSqlString(
                    array(
                        'sql' => "`{$this->_strDBKeyCol}` int(11) NOT NULL AUTO_INCREMENT",
                        'sqlite' => "`{$this->_strDBKeyCol}` int(11) PRIMARY KEY"
                    )
                );
            }
            foreach ($this->_arrDBItems as $field_name => $settings) {
                if ($field_data != '') {
                    $field_data .= ', ';
                }
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
                    $field_data .= Container_Database::getSqlString(
                        array(
                            'sql' => "`{$field_name}` text $isNull"
                        )
                    );
                } elseif ($settings['type'] == 'enum') {
                    $options = '';
                    foreach ($settings['options'] as $option) {
                        if ($options != '') {
                            $options .= ',';
                        }
                        $options .= "'$option'";
                    }
                    $field_data .= Container_Database::getSqlString(
                        array(
                            'sql' => "`{$field_name}` enum({$options}) $isNull"
                        )
                    );
                } elseif (isset($settings['length'])) {
                    $field_data .= Container_Database::getSqlString(
                        array(
                            'sql' => "`{$field_name}` {$settings['type']}({$settings['length']})  $isNull"
                        )
                    );
                } else {
                    $field_data .= Container_Database::getSqlString(
                        array(
                            'sql' => "`{$field_name}` {$settings['type']} $isNull"
                        )
                    );
                }
                if (isset($settings['unique'])) {
                    if ($unique_key != '') {
                        $unique_key .= ',';
                    }
                    $unique_key .= "`{$field_name}`";
                }
            }
            $sql .= $field_data;
            if ($this->_strDBKeyCol != '') {
                $sql .= Container_Database::getSqlString(
                    array(
                        'sql' => ", PRIMARY KEY (`{$this->_strDBKeyCol}`)",
                        'sqlite' => ''
                    )
                );
            }
            if ($unique_key != '') {
                $sql .= Container_Database::getSqlString(
                    array(
                        'sql' => ", UNIQUE KEY `unique_key` ({$unique_key})",
                        'sqlite' => ", UNIQUE ({$unique_key})"
                    )
                );
            }
            $sql .= Container_Database::getSqlString(
                array(
                    'mysql' => ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;",
                    'sql'   => ")"
                )
            );
            $objDatabase->exec($sql);
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * This is used to first initialize the tables of the database. It then adds a set of demo data.
     * 
     * @return boolean State of table creation
     */
    function initializeDemo()
    {
        try {
            $objDatabase = Container_Database::getConnection(true);
            $sql = Container_Database::getSqlString(
                array(
                    'sql' => "DROP TABLE IF EXISTS `{$this->_strDBTable}`"
                )
            );
            $objDatabase->exec($sql);

            $this->initialize();
            if ($this->_arrDemoData == null || !is_array($this->_arrDemoData) || count($this->_arrDemoData) == 0) {
                return false;
            }
            $className = get_called_class();
            foreach ($this->_arrDemoData as $entry) {
                $object = new $className();
                $object->_reqAdminToMod = false;
                $object->_reqCreatorToMod = false;
                foreach ($entry as $key => $value) {
                    $object->setKey($key, $value);
                }
                $object->create();
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    
    /**
     * Return an array of the collected or created data.
     * 
     * @return array A mixed array of these items
     */
    function getSelf()
    {
        if ($this->_strDBKeyCol != '') {
            $key = $this->_strDBKeyCol;
            $return[$key] = $this->$key;
        }
        foreach ($this->_arrDBItems as $key => $dummy) {
            $return[$key] = $this->$key;
        }
        if ($this->_booleanFull) {
            if ($this->_reqAdminToMod
                && ((Object_User::brokerCurrent() != false 
                && Object_User::brokerCurrent()->getKey('isAdmin') == false) 
                || Object_User::brokerCurrent() == false)
            ) {
                $return['isEditable'] = array();
            } elseif ($this->_reqCreatorToMod
                && isset($this->_arrDBItems['intUserID'])
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
                foreach ($this->_arrDBItems as $key=>$value) {
                    $return['isEditable'][$key] = $value['type'];
                }
            }
        }

        return $return;
    }
    
    /**
     * This function returns any errors we want to pass back to the client we 
     * collected during the creation of a new object
     * 
     * @return string|boolean
     */
    function getErrorMessage()
    {
        return $this->_errorMessageReturn;
    }
}