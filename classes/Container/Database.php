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
 * This class initiates all the connections to the database server.
 *
 * @category Container_Database
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */

class Container_Database
{
    protected static $self = null;
    protected $objDatabase = null;
    
    /**
     * This simple function makes the class into a singleton.
     *
     * @return Container_Database
     */
    protected static function GetHandler()
    {
        if (self::$self == null) {
            self::$self = new self();
        }
        return self::$self;
    }

    /**
     * This flushes the database connections for this session. Mostly used in
     * unit testing.
     * 
     * @return void
     */
    protected static function reset()
    {
        self::$self = null;
    }
        
    /**
     * This function sets the values to be used with this database connection.
     *
     * @param string $strDbType The type of database we're using
     * @param array  $arrDsnRo  The elements of the DSN for the read-only 
     * elements of the database connection.
     * @param array  $arrDsnRw  The elements of the DSN for the read-write
     * elements of the database connection.
     * 
     * @return void
     */
    public static function setConnection(
        $strDbType = null,
        $arrDsnRo = null, 
        $arrDsnRw = null
    ) {
        $self = self::GetHandler();
        $self->objDatabase = new Base_Database();
        $self->objDatabase->setConnectionVars($strDbType, $arrDsnRo, $arrDsnRw);
    }
    
    /**
     * This initializes the database connection, based on whether we require
     * a read-only database connection (and have the parameters specified), or a
     * read-write connection (and have the parameters specified).
     *
     * @param boolean $boolRequireWrite Only create a R/W connection if we 
     * actually need one. Until then, just create a R/O connection.
     * @param string  $strDbType        The type of database we're using
     * @param array   $arrDsnRo         The elements of the DSN for the 
     * read-only elements of the database connection.
     * @param array   $arrDsnRw         The elements of the DSN for the 
     * read-write elements of the database connection.
     *
     * @return PDO
     */
    public static function getConnection(
        $boolRequireWrite = false,
        $strDbType = null,
        $arrDsnRo = null, 
        $arrDsnRw = null
    ) {
        $self = self::GetHandler();
        if (! is_object($self->objDatabase)) {
            $self->objDatabase = new Base_Database();
        }
        return $self->objDatabase->getConnection(
            $boolRequireWrite, 
            $strDbType, 
            $arrDsnRo, 
            $arrDsnRw
        );
    }
    
    /**
     * Return the string representing the database type
     *
     * @return string 
     */
    public static function getConnectionType()
    {
        $self = self::getHandler();
        if (is_object($self->objDatabase)) {
            return $self->objDatabase->getConnectionTypeVar();
        } else {
            throw new OutOfBoundsException("Database has not been initialized");
        }
    }

    /**
     * This function allows you to specify all your known SQL varients for a 
     * given request, and then just pick the right one for your connected 
     * database type. The default type to return is "sql" unless you specify
     * something more accurately, e.g. "mysql" or "pgsql".
     *
     * @param array $arrStrings The strings to pick between
     * 
     * @return string
     */
    public static function getSqlString($arrStrings = array())
    {
        $self = self::getHandler();
        if (is_object($self->objDatabase)) {
            try {
                return $self->objDatabase->getSqlString($arrStrings);
            } catch (Exception $e) {
                throw $e;
            }
        } else {
            throw new OutOfBoundsException("Database has not been initialized");
        }
    }
}