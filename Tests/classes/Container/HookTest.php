<?php
class Container_HookTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Container_Hook_Testable::reset();
    }
    
    public function testInitialize()
    {
        $hook = Container_Hook_Testable::GetHandler();
        $this->assertTrue(is_object($hook));
        $this->assertTrue(get_class($hook) == 'Base_Hook');
    }
    
    public function testValidLoad()
    {
        $hook = Container_Hook::Load();
        $this->assertTrue(is_object($hook));
        $this->assertTrue(get_class($hook) == 'Base_Hook');
    }

    public function testValidReLoad()
    {
        $hook = Container_Hook::Load();
        $this->assertTrue(is_object($hook));
        $this->assertTrue(get_class($hook) == 'Base_Hook');
        $this->assertTrue($hook->isFileLoaded());
        $hook = Container_Hook::Load('plugin.php', true);
        $this->assertTrue($hook->isFileLoaded());
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidLoad()
    {
        @Container_Hook::Load('.');
    }
}

class Container_Hook_Testable extends Container_Hook
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