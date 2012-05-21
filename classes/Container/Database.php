<?php

class Container_Database
{
    protected static $_self = null;
    protected $_objDatabase = null;
    
    protected static function GetHandler()
    {
        if (self::$_self == null) {
            self::$_self = new self();
        }
        return self::$_self;
    }

    protected static function reset()
    {
        self::$_self = null;
    }
        
    public static function setConnection(
        $_strDbType = null,
        $_arrDsnRo = null, 
        $_arrDsnRw = null
    )
    {
        $_self = self::GetHandler();
        $_self->_objDatabase = new Base_Database();
        $_self->_objDatabase->setConnectionVars($_strDbType, $_arrDsnRo, $_arrDsnRw);
    }
    
    public static function getConnection(
        $boolRequireWrite = false,
        $_strDbType = null,
        $_arrDsnRo = null, 
        $_arrDsnRw = null
    ) {
        $_self = self::GetHandler();
        if (! is_object($_self->_objDatabase)) {
            $_self->_objDatabase = new Base_Database();
        }
        return $_self->_objDatabase->getConnection(
            $boolRequireWrite, 
            $_strDbType, 
            $_arrDsnRo, 
            $_arrDsnRw
        );
    }
    
    public static function getConnectionType()
    {
        $_self = self::getHandler();
        if (is_object($_self->_objDatabase)) {
            return $_self->_objDatabase->getConnectionTypeVar();
        } else {
            throw new OutOfBoundsException("Database has not been initialized");
        }
    }

    public static function getSqlString($arrStrings = array())
    {
        $_self = self::getHandler();
        if (is_object($_self->_objDatabase)) {
            try {
                return $_self->_objDatabase->getSqlString($arrStrings);
            } catch (Exception $e) {
                throw $e;
            }
        } else {
            throw new OutOfBoundsException("Database has not been initialized");
        }
    }
}