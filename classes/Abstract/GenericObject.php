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
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */
/**
 * This class provides all the object specific functions used throughout the site.
 * It is used as the basis for every object.
 *
 * @category Abstract_GenericObject
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
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
    protected $reqAdminToMod = false;
    /**
     * This variable, if set to true, will require the user making the chage to
     * be either an admin to make the change, or the user who created the object.
     * 
     * @var boolean
     */
    protected $reqCreatorToMod = false;
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
     * Error message hand back. To hand back thrown error messages from new
     * instances of objects where we don't want code to be stopped.
     *
     * @var string|boolean
     */
    protected $errorMessageReturn = false;
    /**
     * An array of translation strings. Default to "en" if no more specific
     * translation is available.
     * 
     * @var array|null
     */
    protected $arrTranslations = array();
    /**
     * This value, if set to true, allows us to customize what type of data is
     * returned, without creating a new table. See, for example, Users as
     * Presenters
     * 
     * @var boolean
     */
    protected $doNotInitialize = false;
    
    /**
     * Ensure that all database items are backed up before processing.
     *
     * This is our usual construct method for all extended classes.
     *
     * @return Abstract_GenericObject
     */
    function __construct()
    {
        if (func_num_args() > 0) {
            throw new BadFunctionCallException("This function does not accept parameters.");
        }
        if (isset($this->arrDBItems) and is_array($this->arrDBItems) and count($this->arrDBItems) > 0) {
            foreach ($this->arrDBItems as $item => $dummy) {
                $this->old[$item] = $this->$item;
                $dummy = null;
            }
        }
        return $this;
    }

    /**
     * Get the object for the ID associated with a particular row
     *
     * @param integer $intID The Object ID to search for
     *
     * @return Abstract_GenericObject
     */
    static function brokerByID($intID = 0)
    {
        $objCache = Base_Cache::getHandler();
        $thisClassName = get_called_class();
        $thisClass = new $thisClassName();
        if ((integer) $intID > 0) {
            if (isset($objCache->arrCache[$thisClassName]['id'][(string) $intID])) {
                return $objCache->arrCache[$thisClassName]['id'][(string) $intID];
            }
            try {
                $objDatabase = Container_Database::getConnection();
                $sql = Container_Database::getSqlString(
                    array(
                        'sql' => "SELECT * FROM {$thisClass->strDBTable} WHERE {$thisClass->strDBKeyCol} = ? LIMIT 1"
                    )
                );
                $query = $objDatabase->prepare($sql);
                $query->execute(array($intID));
                $result = $query->fetchObject($thisClassName);
                if ($result == false) {
                    return false;
                } else {
                    $objCache->arrCache[$thisClassName]['id'][$result->getKey($thisClass->strDBKeyCol)] = $result;
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
     * @param string  $column    The column to search
     * @param string  $value     The value to look for.
     * @param boolean $inverse   Look for anything but this value
     * @param boolean $json      Look for a JSON encoded string
     * @param integer $count     The number of records to return
     * @param string  $direction The SQL direction to process
     * 
     * @return array The array of objects matching this search
     */
    static function brokerByColumnSearch($column = null, $value = null, $inverse = false, $json = false, $count = null, $direction = 'ASC')
    {
        if ($column == null) {
            throw new OutOfBoundsException('No column name');
        }
        $objCache = Base_Cache::getHandler();
        $thisClassName = get_called_class();
        $system_state = Object_User::isSystem();
        Object_User::isSystem(true);
        $thisClass = new $thisClassName();
        Object_User::isSystem($system_state);
        $process = false;
        foreach ($thisClass->arrDBItems as $dbItem => $dummy) {
            $dummy = null;
            if ($dbItem == $column) {
                $process = true;
            }
        }
        if ($thisClass->strDBKeyCol == $column) {
            $process = true;
        }
        if ($process == false) {
            throw new OutOfBoundsException('Not a valid column name');
        }
        $arrResult = array();
        try {
            $objDatabase = Container_Database::getConnection();
            if ($thisClass->strDBKeyCol == null) {
                $thisClass->strDBKeyCol = $column;
            }
            if ($value == null || $value == '' || ($inverse == true && $value == '%')) {
                if ($count != null) {
                    $sql = Container_Database::getSqlString(
                        array(
                            'sql' => "SELECT * FROM {$thisClass->strDBTable} WHERE `{$column}` IS NULL OR `{$column}` = '' ORDER BY `{$thisClass->strDBKeyCol}` {$direction} LIMIT 0, {$count}"
                        )
                    );
                } else {
                    $sql = Container_Database::getSqlString(
                        array(
                            'sql' => "SELECT * FROM {$thisClass->strDBTable} WHERE `{$column}` IS NULL OR `{$column}` = '' ORDER BY `{$thisClass->strDBKeyCol}` {$direction}"
                        )
                    );
                }
                $query = $objDatabase->prepare($sql);
                $query->execute();
            } elseif ($value == '%') {
                if ($count != null) {
                    $sql = Container_Database::getSqlString(
                        array(
                            'sql' => "SELECT * FROM {$thisClass->strDBTable} WHERE `{$column}` IS NOT NULL ORDER BY `{$thisClass->strDBKeyCol}` {$direction} LIMIT 0, {$count}"
                        )
                    );
                } else {
                    $sql = Container_Database::getSqlString(
                        array(
                            'sql' => "SELECT * FROM {$thisClass->strDBTable} WHERE `{$column}` IS NOT NULL ORDER BY `{$thisClass->strDBKeyCol}` {$direction}"
                        )
                    );
                }
                $query = $objDatabase->prepare($sql);
                $query->execute();
            } elseif ($inverse == false) {
                if ($json == true) {
                    if ($count != null) {
                        $sql = Container_Database::getSqlString(
                            array(
                                'sql' => "SELECT * FROM {$thisClass->strDBTable} WHERE `{$column}` = ? OR `{$column}` = ? OR `{$column}` = ? OR `{$column}` = ? OR `{$column}` = ? ORDER BY `{$thisClass->strDBKeyCol}` {$direction} LIMIT 0, {$count}"
                            )
                        );
                    } else {
                        $sql = Container_Database::getSqlString(
                            array(
                                'sql' => "SELECT * FROM {$thisClass->strDBTable} WHERE `{$column}` = ? OR `{$column}` = ? OR `{$column}` = ? OR `{$column}` = ? OR `{$column}` = ? ORDER BY `{$thisClass->strDBKeyCol}` {$direction}"
                            )
                        );
                    }
                    $query = $objDatabase->prepare($sql);
                    $query->execute(array("[$value]", "%\"$value\"%", "[$value,%", "%,$value,%", "%,$value]"));
                } else {
                    if ($count != null) {
                        $sql = Container_Database::getSqlString(
                            array(
                                'sql' => "SELECT * FROM {$thisClass->strDBTable} WHERE `{$column}` = ? ORDER BY `{$thisClass->strDBKeyCol}` {$direction} LIMIT 0, {$count}"
                            )
                        );
                    } else {
                        $sql = Container_Database::getSqlString(
                            array(
                                'sql' => "SELECT * FROM {$thisClass->strDBTable} WHERE `{$column}` = ? ORDER BY `{$thisClass->strDBKeyCol}` {$direction}"
                            )
                        );
                    }
                    $query = $objDatabase->prepare($sql);
                    $query->execute(array($value));
                }
            } else {
                if ($count != null) {
                    $sql = Container_Database::getSqlString(
                        array(
                            'sql' => "SELECT * FROM {$thisClass->strDBTable} WHERE `{$column}` != ? ORDER BY `{$thisClass->strDBKeyCol}` {$direction} LIMIT 0, {$count}"
                        )
                    );
                } else {
                    $sql = Container_Database::getSqlString(
                        array(
                            'sql' => "SELECT * FROM {$thisClass->strDBTable} WHERE `{$column}` != ? ORDER BY `{$thisClass->strDBKeyCol}` {$direction}"
                        )
                    );                    
                }
                $query = $objDatabase->prepare($sql);
                $query->execute(array($value));
            }
            $arrResult = array();
            $result = $query->fetchObject($thisClassName);
            while ($result != false) {
                if (isset($thisClass->strDBKeyCol) 
                    && $thisClass->strDBKeyCol != '' 
                    && $thisClass->strDBKeyCol != null
                ) {
                    if (isset($objCache->arrCache[$thisClassName]['id'][$result->getKey($thisClass->strDBKeyCol)])) {
                        $arrResult[$result->getKey($thisClass->strDBKeyCol)] = $objCache->arrCache[$thisClassName]['id'][$result->getKey($thisClass->strDBKeyCol)];
                    } else {
                        $arrResult[$result->getKey($thisClass->strDBKeyCol)] = $result;
                        $objCache->arrCache[$thisClassName]['id'][$result->getKey($thisClass->strDBKeyCol)] = $result;
                    }
                } else {
                    $arrResult[] = $result;
                }
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
     * @param string  $column  The column to search
     * @param string  $value   The value to look for.
     * @param boolean $inverse Look for everything but the content you've specified
     * 
     * @return integer The number of rows matching the search criteria
     */
    static function countByColumnSearch($column = null, $value = null, $inverse = false)
    {
        if ($column == null) {
            throw new OutOfBoundsException('Not a valid column name');
        }
        $thisClassName = get_called_class();
        $thisClass = new $thisClassName();
        $process = false;
        foreach ($thisClass->arrDBItems as $dbItem => $dummy) {
            $dummy = null;
            if ($dbItem == $column) {
                $process = true;
            }
        }
        if ($process == false) {
            throw new OutOfBoundsException('Not a valid column name');
        }
        try {
            $objDatabase = Container_Database::getConnection();
            if ($value == null || $value == '' || ($inverse == true && $value == '%')) {
                $sql = Container_Database::getSqlString(
                    array(
                        'sql' => "SELECT count({$thisClass->strDBKeyCol}) FROM {$thisClass->strDBTable} WHERE {$column} IS NULL OR {$column} = ''"
                    )
                );
                $query = $objDatabase->prepare($sql);
                $query->execute();
            } elseif ($value == '%') {
                $sql = Container_Database::getSqlString(
                    array(
                        'sql' => "SELECT count({$thisClass->strDBKeyCol}) FROM {$thisClass->strDBTable} WHERE {$column} IS NOT NULL"
                    )
                );
                $query = $objDatabase->prepare($sql);
                $query->execute();
            } elseif ($inverse == false) {
                $sql = Container_Database::getSqlString(
                    array(
                        'sql' => "SELECT count({$thisClass->strDBKeyCol}) FROM {$thisClass->strDBTable} WHERE {$column} = ?"
                    )
                );
                $query = $objDatabase->prepare($sql);
                $query->execute(array($value));
            } else {
                $sql = Container_Database::getSqlString(
                    array(
                        'sql' => "SELECT count({$thisClass->strDBKeyCol}) FROM {$thisClass->strDBTable} WHERE {$column} != ?"
                    )
                );
                $query = $objDatabase->prepare($sql);
                $query->execute(array($value));
            }
            $result = $query->fetchColumn();
            return $result;
        } catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * Get the most recent lastChange date based on the column to search
     *
     * @param string  $column  The column to search
     * @param string  $value   The value to look for.
     * @param boolean $inverse Instruct the search to be reversed
     * 
     * @return datetime The most recent datetime string matching the search criteria
     */
    static function lastChangeByColumnSearch($column = null, $value = null, $inverse = false)
    {
        if ($column == null) {
            throw new OutOfBoundsException('Not a valid column name');
        }
        $thisClassName = get_called_class();
        $thisClass = new $thisClassName();
        $process = false;
        if (!isset($thisClass->arrDBItems['lastChange'])) {
            throw new OutOfBoundsException('Does not store Last Change data');
        }
        foreach ($thisClass->arrDBItems as $dbItem => $dummy) {
            $dummy = null;
            if ($dbItem == $column) {
                $process = true;
            }
        }
        if ($process == false) {
            throw new OutOfBoundsException('Not a valid column name');
        }
        try {
            $objDatabase = Container_Database::getConnection();
            if ($value == null || $value == '' || ($inverse == true && $value == '%')) {
                $sql = Container_Database::getSqlString(
                    array(
                        'sql' => "SELECT max(lastChange) FROM {$thisClass->strDBTable} WHERE {$column} IS NULL OR {$column} = ''"
                    )
                );
                $query = $objDatabase->prepare($sql);
                $query->execute();
            } elseif ($value == '%') {
                $sql = Container_Database::getSqlString(
                    array(
                        'sql' => "SELECT max(lastChange) FROM {$thisClass->strDBTable} WHERE {$column} IS NOT NULL"
                    )
                );
                $query = $objDatabase->prepare($sql);
                $query->execute();
            } elseif ($inverse == false) {
                $sql = Container_Database::getSqlString(
                    array(
                        'sql' => "SELECT max(lastChange) FROM {$thisClass->strDBTable} WHERE {$column} = ?"
                    )
                );
                $query = $objDatabase->prepare($sql);
                $query->execute(array($value));
            } else {
                $sql = Container_Database::getSqlString(
                    array(
                        'sql' => "SELECT max(lastChange) FROM {$thisClass->strDBTable} WHERE {$column} != ?"
                    )
                );
                $query = $objDatabase->prepare($sql);
                $query->execute(array($value));
            }
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
            if ($thisClass->strDBKeyCol != '') {
                $sql = Container_Database::getSqlString(
                    array(
                        'sql' => "SELECT * FROM {$thisClass->strDBTable} ORDER BY {$thisClass->strDBKeyCol}"
                    )
                );
            } else {
                $sql = Container_Database::getSqlString(
                    array(
                        'sql' => "SELECT * FROM {$thisClass->strDBTable}"
                    )
                );
            }
            $query = $objDatabase->prepare($sql);
            $query->execute();
            $result = $query->fetchObject($thisClassName);
            while ($result != false) {
                if (isset($thisClass->arrDBItems['key']) && $thisClass->strDBKeyCol == null) {
                    $thisClass->strDBKeyCol = 'key';
                }
                if (isset($thisClass->strDBKeyCol) 
                    && $thisClass->strDBKeyCol != '' 
                    && $thisClass->strDBKeyCol != null
                ) {
                    if (isset($objCache->arrCache[$thisClassName]['id'][$result->getKey($thisClass->strDBKeyCol)])) {
                        $arrResult[$result->getKey($thisClass->strDBKeyCol)] = $objCache->arrCache[$thisClassName]['id'][$result->getKey($thisClass->strDBKeyCol)];
                    } else {
                        $arrResult[$result->getKey($thisClass->strDBKeyCol)] = $result;
                        $objCache->arrCache[$thisClassName]['id'][$result->getKey($thisClass->strDBKeyCol)] = $result;
                    }
                } else {
                    $arrResult[] = $result;
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
        $thisClassName = get_called_class();
        $thisClass = new $thisClassName();
        if (!isset($thisClass->arrDBItems['lastChange'])) {
            throw new OutOfBoundsException('Does not store Last Change data');
        }
        try {
            $objDatabase = Container_Database::getConnection();
            $sql = Container_Database::getSqlString(
                array(
                    'sql' => "SELECT max(lastChange) FROM {$thisClass->strDBTable}"
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
     * Get a tally of the number of rows in a table
     * 
     * @return integer The number of rows in the table
     */
    static function countAll()
    {
        $thisClassName = get_called_class();
        $thisClass = new $thisClassName();
        try {
            $objDatabase = Container_Database::getConnection();
            $sql = Container_Database::getSqlString(
                array(
                    'sql' => "SELECT count({$thisClass->strDBKeyCol}) FROM {$thisClass->strDBTable}"
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
        $primaryKey = $this->strDBKeyCol;
        if ($primaryKey != '') {
            return $this->$primaryKey;
        } else {
            return null;
        }
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
    function isFull()
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
        if ($keyname == 'intUserID' && ! Object_User::isAdmin()) {
            $temp = Object_User::brokerCurrent();
            if ($temp == false) {
                return false;
            }
            $value = $temp->getKey('intUserID');
        }
        // Only the create and write functions should set the lastChange value.
        if ($keyname != 'lastChange') {
            if (array_key_exists($keyname, $this->arrDBItems) 
                || $keyname == $this->strDBKeyCol
            ) {
                if ($this->$keyname != $value) {
                    $this->$keyname = $value;
                    $this->arrChanges[$keyname] = true;
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
    function getKey($keyname = null)
    {
        if ((array_key_exists($keyname, $this->arrDBItems) 
            || $keyname == $this->strDBKeyCol) 
            && (isset($this->$keyname) 
            || $this->$keyname == null)
        ) {
            return $this->$keyname;
        } else {
            throw new BadFunctionCallException("Although the column '$keyname' exists, there is no corresponding protected value");
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
            if ($this->reqAdminToMod && ! Object_User::isAdmin()) {
                throw new BadMethodCallException("You need to be an admin to perform this operation");
            }
            if ($this->reqCreatorToMod && isset($this->arrDBItems['intUserID']) && ! Object_User::isCreator($this->intUserID)) {
                throw new BadMethodCallException("You need to be the object creator to perform this operation");
            }
            $this->lastChange = date('Y-m-d H:i:s');
            $this->arrChanges['lastChange'] = true;
            if (count($this->arrChanges) > 0) {
                $objDatabase = Container_Database::getConnection(true);
                $sql = '';
                $where = '';
                if (isset($this->strDBKeyCol) and $this->strDBKeyCol != '') {
                    $strDBKeyCol = $this->strDBKeyCol;
                    $values[$strDBKeyCol] = $this->$strDBKeyCol;
                    $where = Container_Database::getSqlString(
                        array(
                            'sql' => '`' . $this->strDBKeyCol . '`' . " = :{$this->strDBKeyCol}"
                        )
                    );
                } elseif (isset($this->arrDBKeyCol) and is_array($this->arrDBKeyCol) and count($this->arrDBKeyCol) > 0) {
                    foreach ($this->arrDBKeyCol as $keycol => $dummy) {
                        if ($where != '') {
                            $where .= ' AND ';
                        }
                        $values["old$keycol"] = $this->old[$keycol];
                        $where .= Container_Database::getSqlString(
                            array(
                                'sql' => '`' . $keycol . `'` . " = :old$keycol"
                            )
                        );
                    }
                } else {
                    foreach ($this->arrDBItems as $keycol => $dummy) {
                        if ($where != '') {
                            $where .= ' AND ';
                        }
                        $values["old$keycol"] = $this->old[$keycol];
                        $where .= Container_Database::getSqlString(
                            array(
                                'sql' => '`' . $keycol . '`' . " = :old$keycol"
                            )
                        );
                    }
                }
                foreach ($this->arrChanges as $change_key => $change_value) {
                    if ($change_value == true and isset($this->arrDBItems[$change_key])) {
                        if ($sql != '') {
                            $sql .= ", ";
                        }
                        $sql .= '`' . $change_key .'`' . " = :$change_key";
                        $values[$change_key] = $this->$change_key;
                    }
                }
                $full_sql = Container_Database::getSqlString(
                    array(
                        'sql' => "UPDATE {$this->strDBTable} SET $sql WHERE $where"
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
            if (isset($full_sql)) {
                error_log("SQL error: " . $e->getMessage() . " SQL: $full_sql (Values: " . print_r($values, true) . ")");
            } else {
                error_log("SQL error: " . $e->getMessage());
            }
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
        if ($this->reqAdminToMod && ! Object_User::isAdmin()) {
            throw new BadMethodCallException("You need to be an admin to perform this operation");
        }
        $this->lastChange = date('Y-m-d H:i:s');
        $this->arrChanges = array();
        $keys = '';
        $key_place = '';
        if (isset($this->strDBKeyCol) && $this->strDBKeyCol != '') {
            $keys = '`' . $this->strDBKeyCol . '`';
            $key_place = 'NULL';
        }
        foreach ($this->arrDBItems as $field_name => $dummy) {
            $dummy = null;
            if ($keys != '') {
                $keys .= ', ';
                $key_place .= ', ';
            }
            $keys .= '`' . $field_name . '`';
            $key_place .= ":$field_name";
            $values[$field_name] = $this->$field_name;
        }
        try {
            $objDatabase = Container_Database::getConnection(true);
            $full_sql = Container_Database::getSqlString(
                array(
                    'sql' => "INSERT INTO {$this->strDBTable} ($keys) VALUES ($key_place)"
                )
            );
            $query = $objDatabase->prepare($full_sql);
            $query->execute($values);
            if ($this->strDBKeyCol != '') {
                $key = $this->strDBKeyCol;
                $this->$key = $objDatabase->lastInsertId();
            }
            $this->sql = $full_sql;
            $this->sql_value = $values;
            Container_Hook::Load()->triggerHook('createRecord', $this);
            return true;
        } catch (Exception $e) {
            error_log("SQL error: " . $e->getMessage() . " SQL: $full_sql (Values: " . print_r($values, true) . ")");
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
        if ($this->reqAdminToMod && ! Object_User::isAdmin()) {
            throw new BadMethodCallException("You need to be an admin to perform this operation");
        }
        if ($this->reqCreatorToMod && isset($this->arrDBItems['intUserID']) && ! Object_User::isCreator($this->intUserID)) {
            throw new BadMethodCallException("You need to be the object creator to perform this operation");
        }
        try {
            $objDatabase = Container_Database::getConnection(true);
            $sql = Container_Database::getSqlString(
                array(
                    'sql' => "DELETE FROM {$this->strDBTable} WHERE {$this->strDBKeyCol} = ?"
                )
            );
            $query = $objDatabase->prepare($sql);
            $key = $this->strDBKeyCol;
            $query->execute(array($this->$key));
            $this->sql = $sql;
            $this->sql_value = $this->$key;
            Container_Hook::Load()->triggerHook('deleteRecord', $this);
            return true;
        } catch (Exception $e) {
            error_log("SQL error: " . $e->getMessage() . " SQL: $sql (Value: {$this->$key})");
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
        if ($this->doNotInitialize == true) {
            return false;
        }
        try {
            $objDatabase = Container_Database::getConnection(true);
            $unique_key = '';
            $sql = Container_Database::getSqlString(
                array(
                    'sql' => "CREATE TABLE IF NOT EXISTS `{$this->strDBTable}` ("
                )
            );
            $field_data = '';
            if ($this->strDBKeyCol != '') {
                $field_data .= Container_Database::getSqlString(
                    array(
                        'sql' => "`{$this->strDBKeyCol}` int(11) NOT NULL AUTO_INCREMENT",
                        'sqlite' => "`{$this->strDBKeyCol}` integer PRIMARY KEY"
                    )
                );
            }
            foreach ($this->arrDBItems as $field_name => $settings) {
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
                            'sql' => "`{$field_name}` enum({$options}) $isNull",
                            'sqlite' => "{$field_name} text $isNull" // SQLite doesn't do enums :(
                        )
                    );
                } elseif (isset($settings['length'])) {
                    $field_data .= Container_Database::getSqlString(
                        array(
                            'sql' => "`{$field_name}` {$settings['type']}({$settings['length']})  $isNull",
                            'sqlite' => "`{$field_name}` {$settings['type']} $isNull"
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
            if ($this->strDBKeyCol != '') {
                $sql .= Container_Database::getSqlString(
                    array(
                        'sql' => ", PRIMARY KEY (`{$this->strDBKeyCol}`)",
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
            if (isset($sql)) {
                error_log("SQL error: " . $e->getMessage() . " SQL: $sql");
            } else {
                error_log("Error performing SQL actions. " . $e->getMessage());
            }
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
        $sql = '';
        if ($this->doNotInitialize == true) {
            return false;
        }
        Object_User::isSystem(true);
        try {
            $objDatabase = Container_Database::getConnection(true);
            $sql = Container_Database::getSqlString(
                array(
                    'sql' => "DROP TABLE IF EXISTS `{$this->strDBTable}`"
                )
            );
            $objDatabase->exec($sql);
            $this->initialize();
            if ($this->arrDemoData == null || !is_array($this->arrDemoData) || count($this->arrDemoData) == 0) {
                return false;
            }
            $className = get_called_class();
            foreach ($this->arrDemoData as $entry) {
                $object = new $className();
                $object->reqAdminToMod = false;
                $object->reqCreatorToMod = false;
                foreach ($entry as $key => $value) {
                    $object->setKey($key, $value);
                }
                $object->create();
            }
        } catch (Exception $e) {
            error_log("SQL error: " . $e->getMessage() . " SQL: $sql");
            throw $e;
        }
    }

    /**
     * Append the translated labels to the returned data for this class.
     *
     * @param array $return The classes' data
     * 
     * @return array
     */
    protected function getLabels($return)
    {
        foreach ($return as $key => $data) {
            $data = null;
            if (isset($this->arrTranslations['label_' . $key])) {
                $return['labels'][$key] = Base_Response::translate($this->arrTranslations['label_' . $key]);
            }
            if (isset($this->arrDBItems[$key]['array'])) {
                $return['pointers'][$key]['data'] = $this->arrDBItems[$key]['array'];
                if (isset($this->arrDBItems[$key]['source'])) {
                    $return['pointers'][$key]['source'] = strtolower($this->arrDBItems[$key]['source']);
                }
            }
        }
        return $return;
    }

    /**
     * Append a single value containing the key and the string representation
     * of this row.
     *
     * @param array $return The classes' data
     * 
     * @return array
     */
    protected function getCurrent($return)
    {
        $return['current'] = array('key' => $this->getPrimaryKeyValue(), 'element' => $this->strDBTable, 'value' => '');
        foreach ($this->arrDBItems as $strDBItem => $arrDBItem) {
            if (isset($arrDBItem['render_in_sub_views'])) {
                if ($return['current']['value'] != '') {
                    $return['current']['value'] .= ', ';
                }
                $return['current']['value'] .= $this->getKey($strDBItem);
            }
        }
        if ($return['current']['value'] == '') {
            if (isset($arrDBItems['str' . strtoupper(substr($this->strDBTable, 0, 1)) . substr($this->strDBTable, 1)])) {
                $return['current']['value'] = $this->getKey('str' . strtoupper(substr($this->strDBTable, 0, 1)) . substr($this->strDBTable, 1));
            } else {
                $return['current']['value'] = $this->getPrimaryKeyValue();
            }
        }
        if ($return['current']['key'] == '-1') {
            $return['current']['value'] = Base_Response::translate(array('en' => 'Any'));
        }
        return $return;
    }
    
    /**
     * This function is used a lot throughout the code base. It was recently
     * re-written to support adding the data labels for the
     *
     * @return array
     */
    public function getSelf()
    {
        return $this->getCurrent($this->getLabels($this->getData()));
    }
    
    /**
     * Return an array of the collected or created data, including Labels for
     * text boxes and details of what can be edited in this case.
     * 
     * @return array A mixed array of these items
     */
    public function getData()
    {
        if ($this->strDBKeyCol != '') {
            $key = $this->strDBKeyCol;
            $return[$key] = $this->$key;
        }
        foreach ($this->arrDBItems as $key => $dummy) {
            $dummy = null;
            $return[$key] = $this->$key;
        }
        if ($this->booleanFull) {
            if ($this->reqAdminToMod && ! Object_User::isAdmin()) {
                $return['isEditable'] = array();
            } elseif ($this->reqCreatorToMod 
                && (isset($this->arrDBItems['intUserID'])
                || $this->strDBKeyCol == 'intUserID')
                && ! Object_User::isCreator($this->intUserID)
            ) {
                $return['isEditable'] = array();
            } else {
                $return['isEditable'] = $this->listKeys($this);
            }
        }
        if (isset($this->lastChange)) {
            $return['epochLastChange'] = strtotime($this->lastChange);
        } else {
            $return['epochLastChange'] = strtotime('now');
        }
        Base_Response::setLastModifiedTime($return['epochLastChange']);
        $return['lastChange'] = date('Y-m-d H:i:s', $return['epochLastChange']);
        return $return;
    }

    /**
     * This function will return the values which are required and which are
     * optional to create a new object of this type
     * 
     * @param Abstract_GenericObject $self An object of this type to retrieve
     * current values from.
     * 
     * @return array
     */
    public static function listKeys($self = null)
    {
        $thisClassName = get_called_class();
        if ($self == null) {
            $self = new $thisClassName();
        }
        $return = array();
        if ($self->reqAdminToMod == true && ! Object_User::isAdmin()) {
            return array();
        }
        foreach ($self->arrDBItems as $strDBItem => $arrDBItem) {
            if (isset($arrDBItem['required']) || isset($arrDBItem['optional'])) {
                if (isset($arrDBItem['required'])
                    && (($arrDBItem['required'] == 'user'
                    && Object_User::brokerCurrent() != false)
                    || ($arrDBItem['required'] == 'worker' 
                    && Object_User::isWorker())
                    || ($arrDBItem['required'] == 'admin' 
                    && Object_User::isAdmin()))
                ) {
                    if (Object_User::isAdmin()) {
                        $return[$strDBItem]['required'] = 'admin';
                    } elseif (Object_User::isWorker()) {
                        $return[$strDBItem]['required'] = 'worker';
                    } elseif (Object_User::brokerCurrent() != false) {
                        $return[$strDBItem]['required'] = 'user';
                    }
                } elseif (isset($arrDBItem['optional']) 
                    && (($arrDBItem['optional'] == 'user'
                    && Object_User::brokerCurrent() != false)
                    || ($arrDBItem['optional'] == 'worker' 
                    && Object_User::isWorker())
                    || ($arrDBItem['optional'] == 'admin' 
                    && Object_User::isAdmin()))
                ) {
                    if (Object_User::isAdmin()) {
                        $return[$strDBItem]['optional'] = 'admin';
                    } elseif (Object_User::isWorker()) {
                        $return[$strDBItem]['optional'] = 'worker';
                    } elseif (Object_User::brokerCurrent() != false) {
                        $return[$strDBItem]['optional'] = 'user';
                    }
                }
                if (isset($return[$strDBItem]['required']) 
                    || isset($return[$strDBItem]['optional'])
                ) {
                    if (isset($self->arrTranslations['label_' . $strDBItem])) {
                        $return[$strDBItem]['label'] = Base_Response::translate($self->arrTranslations['label_' . $strDBItem]);
                    }
                    if (isset($self->arrTranslations['label_new_' . $strDBItem])) {
                        $return[$strDBItem]['label'] = Base_Response::translate($self->arrTranslations['label_new_' . $strDBItem]);
                    }
                    if (isset($arrDBItem['type']) && $arrDBItem['type'] == 'tinyint' && isset($arrDBItem['length']) && $arrDBItem['length'] == 1) {
                        $return[$strDBItem]['list']["1"] = "Yes";
                        $return[$strDBItem]['list']["0"] = "No";
                    }
                    if (isset($arrDBItem['options'])) {
                        foreach ($arrDBItem['options'] as $option) {
                            $return[$strDBItem]['list'][$option] = $option;
                        }
                    }
                    if (isset($arrDBItem['source'])) {
                        if (isset($arrDBItem['value_for_any'])) {
                            $return[$strDBItem]['list'][(string) $arrDBItem['value_for_any']] = Base_Response::translate(array('en' => 'Any'));
                        }
                        $data_object = 'Object_' . $arrDBItem['source'];
                        $data_key = 'int' . $arrDBItem['source'] . 'ID';
                        $data_value = 'str' . $arrDBItem['source'];
                        $arrData = $data_object::brokerAll();
                        if (is_array($arrData) && count($arrData) > 0) {
                            foreach ($arrData as $objData) {
                                if (! isset($arrDBItem['must_have_as_true'])
                                    || $objData->getKey($arrDBItem['must_have_as_true']) == 'true'
                                    || $objData->getKey($arrDBItem['must_have_as_true']) == '1'
                                ) {
                                    $return[$strDBItem]['list'][$objData->getKey($data_key)] = $objData->getKey($data_value);
                                } elseif ($objData->getKey($data_key) == $self->getKey($strDBItem)) {
                                    $return[$strDBItem]['list'][$objData->getKey($data_key)] = $objData->getKey($data_value) . ' ('. Base_Response::translate(array('en' => 'current value')) . ')';                                    
                                }
                            }
                        }
                    }
                    if (isset($arrDBItem['default_value'])) {
                        if ($arrDBItem['default_value'] == 'intUserID') {
                            $thisUser = Object_User::brokerCurrent();
                            if ($thisUser != false) {
                                $return[$strDBItem]['default_value'] = $thisUser->getKey('intUserID');
                            }
                        } else {
                            $return[$strDBItem]['default_value'] = $arrDBItem['default_value'];
                        }
                    }
                    if (isset($arrDBItem['input_type'])) {
                        $return[$strDBItem]['input_type'] = $arrDBItem['input_type'];
                    }
                    if (isset($arrDBItem['array'])) {
                        $return[$strDBItem]['array'] = $arrDBItem['array'];
                    }
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
        return $this->errorMessageReturn;
    }
    
    public static function dataForNewPage()
    {
        $thisClassName = get_called_class();
        $system_state = Object_User::isSystem();
        Object_User::isSystem(true);
        $self = new $thisClassName();
        Object_User::isSystem($system_state);
        $objRequest = Container_Request::getRequest();
        $arrRequest = $objRequest->get_arrRqstParameters();
        $objUser = Object_User::brokerCurrent();
        $self->setKey('intUserID', $objUser->getKey('intUserID'));
        foreach ($self->arrDBItems as $strKey => $arrDBItem) {
            if (isset($arrRequest[$strKey])) {
                $self->$strKey = $arrRequest[$strKey];
            }
        }
        $self->setFull(true);
        return $self->getCurrent($self->getLabels($self->getData()));
    }
}