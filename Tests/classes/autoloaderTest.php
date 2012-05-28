<?php
class autoloaderTest extends PHPUnit_Framework_TestCase
{
    public function testValidAutoload()
    {
        $this->assertTrue(__autoload('Object_Config'));
    }
    
    public function testInvalidAutoload()
    {
        $this->assertFalse(__autoload('blahblah'));
    }
}