<?php

class Base_Config
{
    protected static $handler = null;
    protected $arrConfigGlobal = null;
    protected $arrConfigSecure = null;
    protected $arrConfigLocal = null;
    protected $arrConfig = null;
    
    function initialize()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `config` (`key` varchar(255) NOT NULL, `value` text NOT NULL, PRIMARY KEY (`key`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;
            CREATE TABLE IF NOT EXISTS `secureconfig` (`key` varchar(255) NOT NULL, `value` text NOT NULL, PRIMARY KEY (`key`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ";
        try {
            $db = base_database::getConnection();
            $db->exec($sql);
        } catch (PDOException $e) {
            error_log('Initialize base_config failed: ' . $e->getMessage());
            die("An error occurred creating the configuration tables");
        }
        
    }

    /**
     * This function creates or returns an instance of this class.
     *
     * @return object The Handler object
     */
    private static function getHandler()
    {
        if (self::$handler == null) {
            self::$handler = new self();
        }
        return self::$handler;
    }

    /**
     * This gets the value from the configuration table or file as appropriate
     *
     * @param string $searchKey    The key to search for in the global or local config
     * @param string $defaultValue The default to return should the key not exist.
     *
     * @return string The config value to use
     */
    public function getConfig($searchKey = null, $defaultValue = null)
    {
        $handler = self::getHandler();

        $handler->readConfig();

        if (null != $searchKey) {
            if (isset($handler->arrConfig[$searchKey])) {
                return $handler->arrConfig[$searchKey]['value'];
            } else {
                return $defaultValue;
            }
        } else {
            return $handler->arrConfig;
        }
    }

    /**
    * This gets the value from the configuration file
    *
    * @param string $searchKey    The key to search for in the local config
    * @param string $defaultValue The default to return should the key not exist.
    *
    * @return string The config value to use
    */
    public function getConfigLocal($searchKey = null, $defaultValue = null)
    {
        $handler = self::getHandler();

        $handler->readConfig();

        if (null != $searchKey) {
            if (isset($handler->arrConfigLocal[$searchKey])) {
                return $handler->arrConfigLocal[$searchKey];
            } else {
                return $defaultValue;
            }
        } else {
            return $handler->arrConfigLocal;
        }
    }

    /**
    * This gets the value from the configuration table
    *
    * @param string $searchKey    The key to search for in the global config
    * @param string $defaultValue The default to return should the key not exist.
    *
    * @return string The config value to use
    */
    public function getConfigGlobal($searchKey = null, $defaultValue = null)
    {
        $handler = self::getHandler();

        $handler->readConfig();

        if (null != $searchKey) {
            if (isset($handler->arrConfigGlobal[$searchKey])) {
                return $handler->arrConfigGlobal[$searchKey];
            } else {
                return $defaultValue;
            }
        } else {
            return $handler->arrConfigGlobal;
        }
    }

    /**
    * This gets the value from the configuration table
    *
    * @param string $searchKey    The key to search for in the secure config.
    * @param string $defaultValue The default to return should the key not exist.
    *
    * @return string The config value to use
    */
    public function getConfigSecure($searchKey = null, $defaultValue = null)
    {
        $handler = self::getHandler();

        $handler->readConfig();

        if (null != $searchKey) {
            if (isset($handler->arrConfigSecure[$searchKey])) {
                return $handler->arrConfigSecure[$searchKey];
            } else {
                return $defaultValue;
            }
        } else {
            return $handler->arrConfigSecure;
        }
    }

    /**
     * This function reads the config files to use later
     *
     * @return void
     */
    private function readConfig()
    {
        $handler = self::getHandler();
        if (null == $handler->arrConfigGlobal && null == $handler->arrConfigLocal && null == $handler->arrConfigSecure) {
            include dirname(__FILE__) . '/../../config/default.php';

            $handler->arrConfigLocal = $APPCONFIG;

            $db = base_database::getConnection();
            try {
                // This is just for things like API keys, password salts etc.
                // If we have, at a later point, a function to export the database
                // en masse, then we can encrypt all the output from the secureconfig
                // table. It shouldn't overide the global or local config settings
                $sql = "SELECT * FROM secureconfig";
                $query = $db->prepare($sql);
                $query->execute();
                $handler->arrConfigSecure = $query->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);

                // This is just the regular global configuration settings.
                $sql = "SELECT * FROM config";
                $query = $db->prepare($sql);
                $query->execute();
                $handler->arrConfigGlobal = $query->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
                foreach ($handler->arrConfigGlobal as $key => $value) {
                    $handler->arrConfig[$key] = array('isLocal' => false, 'isOverriden' => false, 'value' => $value);
                }
                
                // This is the configuration settings local to this individual machine.
                foreach ($handler->arrConfigLocal as $key => $value) {
                    if (isset($handler->arrConfig[$key])) {
                        $handler->arrConfig[$key] = array('isLocal' => true, 'isOverriden' => true, 'value' => $value);
                    } else {
                        $handler->arrConfig[$key] = array('isLocal' => true, 'isOverriden' => false, 'value' => $value);
                    }
                }
            } catch(Exception $e) {
                error_log($e);
                die();
            }
        }
    }

    /**
     * This function lets us update (add, update, delete) the config table in the database.
     *
     * @param string $key   The key to use to update the config table
     * @param string $value The value to store in the config table. Set to null to erase the entry.
     *
     * @return boolean Success or failure of the operation.
     */
    function setGlobalConfig($key = null, $value = null)
    {
        if (null == $key) {
            return false;
        }
        $db = base_database::getConnection();
        try {
            $sql = "SELECT value FROM config WHERE key = ? LIMIT 0, 1";
            $query = $db->prepare($sql);
            $query->execute(array($key));
            // This section of code, thanks to code example here:
            // http://www.lornajane.net/posts/2011/handling-sql-errors-in-pdo
            if ($query->errorCode() != 0) {
                throw new Exception("SQL Error: " . print_r(array('sql'=>$sql, 'error'=>$query->errorInfo()), true), 1);
            }
            $finder = $query->fetchAll(PDO::FETCH_ASSOC);
            if ($finder == false) {
                $sql = "INSERT INTO config (key, value) VALUES (?, ?)";
                $query = $db->prepare($sql);
                $query->execute(array($key, $value));
                // This section of code, thanks to code example here:
                // http://www.lornajane.net/posts/2011/handling-sql-errors-in-pdo
                if ($query->errorCode() != 0) {
                    throw new Exception("SQL Error: " . print_r(array('sql'=>$sql, 'error'=>$query->errorInfo()), true), 1);
                }
            } else {
                if (null == $value) {
                    $sql = "DELETE FROM config WHERE key = ?";
                    $query = $db->prepare($sql);
                    $query->execute(array($key));
                } else {
                    $sql = "UPDATE config SET value = ? WHERE key = ?";
                    $query = $db->prepare($sql);
                    $query->execute(array($value, $key));
                }
                // This section of code, thanks to code example here:
                // http://www.lornajane.net/posts/2011/handling-sql-errors-in-pdo
                if ($query->errorCode() != 0) {
                    throw new Exception("SQL Error: " . print_r(array('sql'=>$sql, 'error'=>$query->errorInfo()), true), 1);
                }
            }
        } catch(Exception $e) {
            error_log($e);
            return false;
        }
        return true;
    }
}