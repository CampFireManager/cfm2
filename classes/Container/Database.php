<?php

class Container_Database
{
    protected static $self = null;
    protected $objDatabase = null;
    
    protected static function GetHandler()
    {
        if (self::$self == null) {
            self::$self = new self();
        }
        return self::$self;
    }

    protected static function reset()
    {
        self::$self = null;
    }
        
    public static function setConnection(
        $strDbType = null,
        $arrDsnRo = null, 
        $arrDsnRw = null
    ) {
        $self = self::GetHandler();
        $self->objDatabase = new Base_Database();
        $self->objDatabase->setConnectionVars($strDbType, $arrDsnRo, $arrDsnRw);
    }
    
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
    
    public static function getConnectionType()
    {
        $self = self::getHandler();
        if (is_object($self->objDatabase)) {
            return $self->objDatabase->getConnectionTypeVar();
        } else {
            throw new OutOfBoundsException("Database has not been initialized");
        }
    }

    public static function getSqlString($arrStrings = array()) {
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