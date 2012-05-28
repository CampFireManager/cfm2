<?php
class Object_ScreenTest extends PHPUnit_Framework_TestCase
{
    public function testObjectScreenCreation()
    {
        $objScreen = new Object_Screen();
        $this->assertTrue(is_object($objScreen));
    }
}