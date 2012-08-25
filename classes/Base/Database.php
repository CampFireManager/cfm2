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
 * This class does all the basic database stuff - connecting to the database, 
 * returning the type of database you're connecting to, generating query strings
 * based on the type of database you're connecting to... that sort of thing.
 *
 * @category Base_Database
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */

class Base_Database
{
    protected $arrDsnRw = null;
    protected $arrDsnRo = null;
    protected $objPdoRw = null;
    protected $objPdoRo = null;
    protected $strDbType = null;

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
    public function setConnectionVars(
        $strDbType = null,
        $arrDsnRo = null, 
        $arrDsnRw = null
    ) {
        if ($strDbType != null) {
            $this->strDbType = $strDbType;
        }
        if ($arrDsnRo != null) {
            $this->arrDsnRo = $arrDsnRo;
        }
        if ($arrDsnRw != null) {
            $this->arrDsnRw = $arrDsnRw;
        }
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
    public function getConnection(
        $boolRequireWrite = false,
        $strDbType = null,
        $arrDsnRo = null, 
        $arrDsnRw = null
    ) {
        $this->setConnectionVars($strDbType, $arrDsnRo, $arrDsnRw);
        if (($boolRequireWrite == true && $this->objPdoRw != null) 
            || ($boolRequireWrite == false && $this->objPdoRo != null)
        ) {
            if ($boolRequireWrite == true) {
                return $this->objPdoRw;
            } else {
                return $this->objPdoRo;
            }
        } else {
            try {
                if ($arrDsnRo == null 
                    || count($arrDsnRo) == 0 
                    || !isset($arrDsnRo['string'])
                ) {
                    $boolRequireWrite = true;
                    $this->objPdoRo = &$this->objPdoRw;
                }
                if ($boolRequireWrite == true) {
                    $this->objPdoRw = new PDO(
                        $this->arrDsnRw['string'], 
                        $this->arrDsnRw['user'], 
                        $this->arrDsnRw['pass'], 
                        $this->arrDsnRw['init']
                    );
                    $this->objPdoRw->setAttribute(
                        PDO::ATTR_ERRMODE, 
                        PDO::ERRMODE_EXCEPTION
                    );
                    return $this->objPdoRw;
                } else {
                    $this->objPdoRo = new PDO(
                        $this->arrDsnRo['string'], 
                        $this->arrDsnRo['user'], 
                        $this->arrDsnRo['pass'], 
                        $this->arrDsnRo['init']
                    );
                    $this->objPdoRo->setAttribute(
                        PDO::ATTR_ERRMODE, 
                        PDO::ERRMODE_EXCEPTION
                    );
                    return $this->objPdoRo;
                }
            } catch (PDOException $exceptionPDO) {
                throw $exceptionPDO;
            }
        }
    }

    /**
     * Return the string representing the database type
     *
     * @return string 
     */
    public function getConnectionTypeVar()
    {
        return $this->strDbType;
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
    public function getSqlString($arrStrings = array())
    {
        if (!is_array($arrStrings) || count($arrStrings) == 0) {
            throw new InvalidArgumentException("This function does not contain any strings");
        }
        if (isset($arrStrings[$this->strDbType])) {
            return $arrStrings[$this->strDbType];
        } elseif (isset($arrStrings['sql'])) {
            return $arrStrings['sql'];
        } else {
            throw new InvalidArgumentException("The strings you passed did not include a valid string for your database type.");
        }
    }
}