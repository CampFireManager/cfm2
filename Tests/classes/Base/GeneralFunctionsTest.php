<?php
class Base_GeneralFunctionsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException BadMethodCallException
     */
    public function testInitialize()
    {
        @$cache = new Base_GeneralFunctions();
    }
    
    
}