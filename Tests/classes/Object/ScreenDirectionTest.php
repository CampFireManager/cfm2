<?php
class Object_ScreenDirectionTest extends PHPUnit_Framework_TestCase
{
    public function testObjectScreenDirectionCreation()
    {
        $objScreenDirection = new Object_ScreenDirection();
        $this->assertTrue(is_object($objScreenDirection));
    }
}