<?php
class Base_ResponseTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException BadMethodCallException
     */
    public function testInitialize()
    {
        @$response = new Base_Response();
    }
    
    
}