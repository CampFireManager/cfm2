<?php
class object_generic
{
    protected $arrDBItems = array();
    protected $strDBTable = "";
    protected $strDBKeyCol = "";
    protected $arrChanges = array();
    protected $booleanFull = false;
    protected $old = array();

    /**
     * Set booleanFull to this value - expands the existing object to include it's
     * component parts if true.
     *
     * @param boolean $full Set the booleanFull value to this
     *
     * @return void
     */
    function set_full($full)
    {
        $this->booleanFull = $this->asBoolean($full);
    }

    /**
     * Get the value of $this->booleanFull
     *
     * @return boolean
     */
    function get_full()
    {
        return $this->full;
    }

    /**
     * Ensure that all database items are backed up before processing
     *
     * @return void
     */
    function __construct()
    {
        if (isset($this->arrDBItems) and is_array($this->arrDBItems) and count($this->arrDBItems) > 0) {
            foreach ($this->arrDBItems as $item=>$dummy) {
                $this->old[$item] = $this->$item;
            }
        }
    }

    /**
     * Commit any changes to the database
     *
     * @return void
     */
    function write()
    {
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
                $db = Database::getConnection(true);
                $query = $db->prepare($full_sql);
                $query->execute($values);
                // This section of code, thanks to code example here:
                // http://www.lornajane.net/posts/2011/handling-sql-errors-in-pdo
                if ($query->errorCode() != 0) {
                    throw new Exception("SQL Error: " . print_r(array('sql'=>$full_sql, 'values'=>$values, 'error'=>$query->errorInfo()), true), 1);
                }
                hook_update::trigger();
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
    protected function create()
    {
        $this->arrChanges = array();
        $keys = '';
        $key_place = '';
        foreach ($this->arrDBItems as $field_name=>$dummy) {
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
            $db = Database::getConnection(true);
            $query = $db->prepare($full_sql);
            $query->execute($values);
            // This section of code, thanks to code example here:
            // http://www.lornajane.net/posts/2011/handling-sql-errors-in-pdo
            if ($query->errorCode() != 0) {
                throw new Exception("SQL Error: " . print_r(array('sql'=>$full_sql, 'values'=>$values, 'error'=>$query->errorInfo()), true), 1);
            }
            if ($this->strDBKeyCol != '') {
                $key = $this->strDBKeyCol;
                $this->$key = $db->lastInsertId();
            }
            hook_create::trigger();
            return true;
        } catch(Exception $e) {
            error_log("Error creating: " . $e->getMessage());
            return false;
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
        foreach ($this->arrDBItems as $key=>$dummy) {
            $return[$key] = $this->$key;
        }
        return $return;
    }

    /**
     * Return boolean true for 1 and boolean false for 0
     *
     * @param integer $check Value to check
     *
     * @return boolean Result
     */
    function asBoolean($check)
    {
        switch((string) $check) {
            case 'no':
            case '0':
            case 'false':
                return false;
            case '1':
            case 'yes':
            case 'true':
                return true;
            default:
                return false;
        }
    }

    /**
     * Return the value marked as being "preferred", or failing that, the first entry in the array, or the only entry.
     *
     * @param JSON $strJson A JSON encoded string, containing an array of data, or just a simple string.
     *
     * @return string The preferred value.
     */
    function preferredJson($strJson = '')
    {
        $arrJson = (array) json_decode($strJson);
        if (count($arrJson) > 1) {
            foreach ($arrJson as $key=>$value) {
                if ($key == 'preferred') {
                    return $value;
                }
            }
            // We didn't find a preferred value, so just return the first one as being "preferred"
            foreach ($arrJson as $value) {
                return $value;
            }
        } elseif (is_array($arrJson) and count($arrJson) == 1) {
            foreach ($arrJson as $value) {
                return $value;
            }
        } else {
            return $strJson;
        }
    }

    /**
     * Return the size of the JSON array
     *
     * @param JSON $strJson A JSON encoded array
     *
     * @return integer The size of the JSON array
     */
    function sizeJson($strJson = '')
    {
        $arrJson = (array) json_decode($strJson);
        if (count($arrJson == 0)) {
            $arrJson[] = $strJson;
        }
        return count($arrJson);
    }

    /**
     * Add a new string to an existing JSON array, or promote one value to being "preferred"
     *
     * @param JSON    $strJson     The existing JSON array.
     * @param string  $strNewValue The value to add, or prefer.
     * @param boolean $preferred   Optional. Set to true to make this value preferred.
     *
     * @return JSON The resulting JSON array.
     */
    function addJson($strJson = '', $strNewValue = '', $preferred = false)
    {
        $set = false;
        $arrJson = (array) json_decode($strJson);
        if (count($arrJson) == 0 and $strJson != '') {
            $arrJson[] = $strJson;
        } elseif ($strJson == '') {
            $arrJson = array();
        }
        $arrTemp = array();
        $intKey = 0;
        if ($preferred == true) {
            foreach ($arrJson as $value) {
                if ($value == $strNewValue) {
                    $arrTemp['preferred'] = $value;
                    $set = true;
                } else {
                    $arrTemp[$intKey++] = $value;
                }
            }
            if ($set == false) {
                $arrTemp['preferred'] = $strNewValue;
            }
        } else {
            foreach ($arrJson as $value) {
                if ($value == $strNewValue) {
                    $set = true;
                } else {
                    $arrTemp[$intKey++] = $value;
                }
            }
            if ($set == false) {
                $arrTemp[$intKey++] = $strNewValue;
            }
        }
        return json_encode($arrTemp);
    }

    /**
     * This function removes a value from the JSON array, preserving the "preferred" key, where appropriate.
     *
     * @param JSON   $strJson          The JSON array to operate on
     * @param string $strValueToRemove The value to remove from the array
     *
     * @return false|JSON The modified array, or false, if there is only one value.
     */
    function delJson($strJson = '', $strValueToRemove = '')
    {
        $arrJson = (array) json_decode($strJson);
        if (count($arrJson) == 0) {
            $arrJson[] = $strJson;
        }
        if (count($arrJson) <= 1) {
            return $strJson;
        }
        $arrTemp = array();
        $intKey = 0;
        foreach ($arrJson as $key=>$value) {
            if ($value != $strValueToRemove) {
                if ($key == 'preferred') {
                    $arrTemp['preferred'] = $value;
                } else {
                    $arrTemp[$intKey++] = $value;
                }
            }
        }
        return json_encode($arrTemp);
    }

    /**
     * Find a value in a JSON encoded array
     *
     * @param JSON   $strJson        The JSON encoded array.
     * @param string $strValueToFind The value to find
     *
     * @return boolean If the value is there.
     */
    function inJson($strJson = '', $strValueToFind = '')
    {
        $arrJson = (array) json_decode($strJson);
        if (count($arrJson) == 0) {
            $arrJson[] = $strJson;
        }
        foreach ($arrJson as $value) {
            if ($value == $strValueToFind) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return the decoded JSON array of data
     *
     * @param JSON $strJson The data to decode
     *
     * @return array The data, in array format.
     */
    function getJson($strJson = '')
    {
        $arrJson = (array) json_decode($strJson);
        if (count($arrJson) == 0) {
            $arrJson[] = $strJson;
        }
        $arrJson = $this->deobjectify_array($arrJson);
        return $arrJson;
    }

    /**
     * Return an array of data when presented with an object
     *
     * @param array|object $process Values to be processed
     *
     * @return array Processed array
     */
    function deobjectify_array($process)
    {
        foreach ((array) $process as $key => $value) {
            if (is_object($value)) {
                $return[$key] = deobjectify_array($value);
            } else {
                $return[$key] = $value;
            }
        }
        return $return;
    }
}