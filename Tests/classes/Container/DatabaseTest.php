<?php
class Container_DatabaseTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Container_Database_Testable::reset();
    }
    
    public function testInitialize()
    {
        $database = Container_Database_Testable::GetHandler();
        $this->assertTrue(is_object($database));
        $this->assertTrue(get_class($database) == 'Container_Database');
    }
    
    public function testConnectRW()
    {
        $database = Container_Database::GetConnection(
            true,
            'sqlite',
            null,
            array(
                'string' => 'sqlite::memory:', 
                'user' => null, 
                'pass' => null, 
                'init' => array()
            )
        );
        $this->assertTrue(is_object($database));
        $this->assertTrue(get_class($database) == 'PDO');
        $database = Container_Database::GetConnection(true);
        $this->assertTrue(is_object($database));
        $this->assertTrue(get_class($database) == 'PDO');
    }

    public function testConnectRO()
    {
        $database = Container_Database::GetConnection(
            false,
            'sqlite',
            array(
                'string' => 'sqlite::memory:', 
                'user' => null, 
                'pass' => null, 
                'init' => array()
            ),
            array()
        );
        $this->assertTrue(is_object($database));
        $this->assertTrue(get_class($database) == 'PDO');
        $database = Container_Database::GetConnection(false);
        $this->assertTrue(is_object($database));
        $this->assertTrue(get_class($database) == 'PDO');
    }

    public function testConnectRWDueToNoROData()
    {
        $database = Container_Database::GetConnection(
            false,
            'sqlite',
            null,
            array(
                'string' => 'sqlite::memory:', 
                'user' => null, 
                'pass' => null, 
                'init' => array()
            )
        );
        $this->assertTrue(is_object($database));
        $this->assertTrue(get_class($database) == 'PDO');
    }
    
    /**
     * @expectedException PDOException
     */
    public function testInvalidDSNReturnsAnException()
    {
        @Container_Database::getConnection(false, 'sqlite', array('string' => 'sqlite:/invalid/path.sqlite'));
    }
    
    public function testGetDatabaseType()
    {
        Container_Database::setConnection(
            'sqlite',
            null,
            array(
                'string' => 'sqlite::memory:', 
                'user' => null, 
                'pass' => null, 
                'init' => array()
            )
        );
        $this->assertTrue(Container_Database::getConnectionType() == 'sqlite');
    }
    
    /**
     * @expectedException OutOfBoundsException
     */
    public function testFailToGetDatabaseType()
    {
        @Container_Database::getConnectionType();
    }
    
    public function testGetSqlStrings()
    {
        Container_Database::setConnection(
            'sqlite',
            null,
            array(
                'string' => 'sqlite::memory:', 
                'user' => null, 
                'pass' => null, 
                'init' => array()
            )
        );
        $this->assertTrue(
            Container_Database::getSqlString(
                array(
                    'sql' => true
                )
            )
        );
        $this->assertTrue(
            Container_Database::getSqlString(
                array(
                    'sqlite' => true
                )
            )
        );
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testFailToGetSqlStringDueToUninitilized()
    {
        @Container_Database::getSqlString();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFailToGetSqlStringDueToNoStrings()
    {
        Container_Database::setConnection(
            'sqlite',
            null,
            array(
                'string' => 'sqlite::memory:', 
                'user' => null, 
                'pass' => null, 
                'init' => array()
            )
        );
        @Container_Database::getSqlString();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFailToGetSqlStringDueToNonArray()
    {
        Container_Database::setConnection(
            'sqlite',
            null,
            array(
                'string' => 'sqlite::memory:', 
                'user' => null, 
                'pass' => null, 
                'init' => array()
            )
        );
        @Container_Database::getSqlString(true);
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testFailToGetSqlStringDueToNoValidConnectionTypes()
    {
        Container_Database::setConnection(
            'sqlite',
            null,
            array(
                'string' => 'sqlite::memory:', 
                'user' => null, 
                'pass' => null, 
                'init' => array()
            )
        );
        @Container_Database::getSqlString(array('not a real sql server' => false));
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