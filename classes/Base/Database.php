<?php

class Base_Database
{
    protected $_arrDsnRw = null;
    protected $_arrDsnRo = null;
    protected $_objPdoRw = null;
    protected $_objPdoRo = null;
    protected $_strDbType = null;

    public function setConnectionVars(
        $_strDbType = null,
        $_arrDsnRo = null, 
        $_arrDsnRw = null
    )
    {
        if ($_strDbType != null) {
            $this->_strDbType = $_strDbType;
        }
        if ($_arrDsnRo != null) {
            $this->_arrDsnRo = $_arrDsnRo;
        }
        if ($_arrDsnRw != null) {
            $this->_arrDsnRw = $_arrDsnRw;
        }
    }
    
    public function getConnection(
        $boolRequireWrite = false,
        $_strDbType = null,
        $_arrDsnRo = null, 
        $_arrDsnRw = null
    )
    {
        $this->setConnectionVars($_strDbType, $_arrDsnRo, $_arrDsnRw);
        if (($boolRequireWrite == true && $this->_objPdoRw != null) 
            || ($boolRequireWrite == false && $this->_objPdoRo != null)
        ) {
            if ($boolRequireWrite == true) {
                return $this->_objPdoRw;
            } else {
                return $this->_objPdoRo;
            }
        } else {
            try {
                if ($_arrDsnRo == null 
                    || count($_arrDsnRo) == 0 
                    || !isset($_arrDsnRo['string'])
                ) {
                    $boolRequireWrite = true;
                    $this->_objPdoRo = &$this->_objPdoRw;
                }
                if ($boolRequireWrite == true) {
                    $this->_objPdoRw = new PDO(
                        $this->_arrDsnRw['string'], 
                        $this->_arrDsnRw['user'], 
                        $this->_arrDsnRw['pass'], 
                        $this->_arrDsnRw['init']
                    );
                    $this->_objPdoRw->setAttribute(
                        PDO::ATTR_ERRMODE, 
                        PDO::ERRMODE_EXCEPTION
                    );
                    return $this->_objPdoRw;
                } else {
                    $this->_objPdoRo = new PDO(
                        $this->_arrDsnRo['string'], 
                        $this->_arrDsnRo['user'], 
                        $this->_arrDsnRo['pass'], 
                        $this->_arrDsnRo['init']
                    );
                    $this->_objPdoRo->setAttribute(
                        PDO::ATTR_ERRMODE, 
                        PDO::ERRMODE_EXCEPTION
                    );
                    return $this->_objPdoRo;
                }
            } catch (PDOException $exceptionPDO) {
                throw $exceptionPDO;
            }
        }
    }

    public function getConnectionTypeVar()
    {
        return $this->_strDbType;
    }
    
    public function getSqlString($arrStrings = array())
    {
        if (!is_array($arrStrings) || count($arrStrings) == 0) {
            throw new InvalidArgumentException("This function does not contain any strings");
        }
        if (isset($arrStrings[$this->_strDbType])) {
            return $arrStrings[$this->_strDbType];
        } elseif (isset($arrStrings['sql'])) {
            return $arrStrings['sql'];
        } else {
            throw new InvalidArgumentException("The strings you passed did not include a valid string for your database type.");
        }
    }
}