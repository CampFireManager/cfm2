<?php
require_once dirname(__FILE__) . '/../../../classes/autoloader.php';

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
}