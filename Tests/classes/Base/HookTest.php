<?php
class Base_HookTest extends PHPUnit_Framework_TestCase
{
    public function testInitialize()
    {
        $hook = new Base_Hook();
        $this->assertTrue(is_object($hook));
        $this->assertTrue(get_class($hook) == 'Base_Hook');
        $this->assertFalse($hook->isFileLoaded());
        $hook->Load('plugin.php');
        $this->assertTrue($hook->isFileLoaded());
    }
    
    /**
     * @expectedException UnexpectedValueException
     */
    public function testNoFilename()
    {
        $hook = new Base_Hook();
        @$hook->Load();
    }
    
    /**
     * @expectedException UnexpectedValueException
     */
    public function testInvalidFilename()
    {
        $hook = new Base_Hook();
        @$hook->Load('A Dummy File');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testUnloadableConfig()
    {
        $hook = new Base_Hook();
        @$hook->Load('.');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddHookWithNoTriggers()
    {
        $hook = new Base_Hook();
        @$hook->addHooks(new Testable_HookWithNoTriggers());
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddNonObjectHook()
    {
        $hook = new Base_Hook();
        @$hook->addHooks(true);
    }

    public function testAddNewTrigger()
    {
        // Because of the way this works, I can only test that running the
        // trigger action doesn't throw an exception!
        
        $hook = new Base_Hook();
        $hook->addTrigger('SomethingRandom');
        $hook->triggerHook('SomethingRandom');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testTriggerHookWithNoTriggerType()
    {
        $hook = new Base_Hook();
        @$hook->triggerHook();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testTriggerHookWithInvalidTriggerType()
    {
        $hook = new Base_Hook();
        @$hook->triggerHook('blahblah');
    }
    
    /**
     * @expectedException LogicException
     */
    public function testAddValidHook()
    {
        $hook = new Base_Hook();
        $hook->addHooks(new Testable_HookWithTrigger());
        @$hook->triggerHook('cronTick');
    }
}

class Testable_HookWithNoTriggers
{
    
}

class Testable_HookWithTrigger
{
    public function hook_cronTick() {
        throw new LogicException("Actually, there's no easy way to pass back that this blighter should work!");
    }
}