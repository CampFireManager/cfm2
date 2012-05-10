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
        $strDbType = 'mysql',
        $arrDsnRo = null, 
        $arrDsnRw = null
    ) {
        $self = self::GetHandler();
        $self->objDatabase = new Base_Database();
        $self->objDatabase->setConnectionVars($strDbType, $arrDsnRo, $arrDsnRw);
    }
    
    public static function getConnection(
        $boolRequireWrite = false,
        $strDbType = 'mysql',
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

}

class Container_Database_Testable extends Container_Database
{
    public static function GetHandler()
    {
        return parent::GetHandler();
    }
    
    public static function reset()
    {
        parent::reset();
    }
}