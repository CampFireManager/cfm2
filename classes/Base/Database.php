<?php

class Base_Database
{
    protected $arrDsnRw = null;
    protected $arrDsnRo = null;
    protected $objPdoRw = null;
    protected $objPdoRo = null;
    protected $strDbType = null;

    public function setConnectionVars(
        $strDbType = null,
        $arrDsnRo = null, 
        $arrDsnRw = null
    )
    {
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
    
    public function getConnection(
        $boolRequireWrite = false,
        $strDbType = null,
        $arrDsnRo = null, 
        $arrDsnRw = null
    )
    {
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

    public function getConnectionTypeVar()
    {
        return $this->strDbType;
    }
    
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