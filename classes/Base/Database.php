<?php
/**
 * CampFire Manager is a scheduling tool predominently used at BarCamps to 
 * schedule talks based, mainly, on the number of people attending each talk
 * receives.
 *
 * PHP version 5
 *
 * @category CampFireManager2
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */
/**
 * This singleton class initializes the connection to the database (whether 
 * read-write or read-only) and returns a handler for that to the rest of the
 * code.
 *
 * @category Base
 * @package  Database
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Base_Database
{
    protected static $db_handler = null;
    protected $rw_db = null;
    protected $ro_db = null;

    /**
     * This function creates or returns an instance of this class.
     *
     * @return object The Handler object
     */
    private static function getHandler()
    {
        if (self::$db_handler == null) {
            self::$db_handler = new self();
        }
        return self::$db_handler;
    }

    /**
     * This creates or returns the database object - depending on RO/RW requirements.
     *
     * @param boolean $RequireWrite Does this connection require write access?
     *
     * @return object A PDO instance for the query.
     */
    public function getConnection($RequireWrite = false)
    {
        $self = self::getHandler();
        if (($RequireWrite == true AND $self->rw_db != null) OR ($RequireWrite == false AND $self->ro_db != null)) {
            if ($RequireWrite == true) {
                return $self->rw_db;
            } else {
                return $self->ro_db;
            }
        } else {
            include dirname(__FILE__) . '/../../config/default.php';
            try {
                if (!isset($RO_DSN)) {
                    $RequireWrite = true;
                    $self->ro_db = &$self->rw_db;
                }
                if ($RequireWrite == true) {
                    $self->rw_db = new PDO($RW_DSN['string'], $RW_DSN['user'], $RW_DSN['pass'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
                    $self->rw_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    return $self->rw_db;
                } else {
                    $self->ro_db = new PDO($RO_DSN['string'], $RO_DSN['user'], $RO_DSN['pass'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
                    $self->ro_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    return $self->ro_db;
                }
            } catch (PDOException $e) {
                echo "Error connecting: " . $e->getMessage();
                die();
            }
        }
    }
}