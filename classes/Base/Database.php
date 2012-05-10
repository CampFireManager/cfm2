<?php

class Base_Database
{
    protected $arrDsnRw = null;
    protected $arrDsnRo = null;
    protected $objPdoRw = null;
    protected $objPdoRo = null;
    protected $strDbType = 'mysql';

    public function setConnectionVars(
        $strDbType = 'mysql',
        $arrDsnRo = null, 
        $arrDsnRw = null
    ) {
        if ($this->strDbType != $strDbType && $strDbType != null) {
            $this->strDbType = $strDbType;
        }
        if ($this->arrDsnRo != $arrDsnRo && $arrDsnRo != null) {
            $this->arrDsnRo = $arrDsnRo;
        }
        if ($this->arrDsnRw != $arrDsnRw && $arrDsnRw != null) {
            $this->arrDsnRw = $arrDsnRw;
        }
    }
    
    public function getConnection(
        $boolRequireWrite = false,
        $strDbType = 'mysql',
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

    public function getConnectionTypeVar()
    {
        return $this->strDbType;
    }
}