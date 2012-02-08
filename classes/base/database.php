<?php

class base_database
{
    protected static $handler = null;
    protected $rw_db = null;
    protected $ro_db = null;

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