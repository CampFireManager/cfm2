<?php
class Object_TagTest extends PHPUnit_Framework_TestCase
{    
    public function testObjectTagCreation()
    {
        $objTag = new Object_Tag();
        $this->assertTrue(is_object($objTag));
    }
}