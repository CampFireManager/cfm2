<?php
require_once dirname(__FILE__) . '/../../../classes/autoloader.php';

class Container_RequestTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Container_Request_Testable::reset();
    }
    
    public function testInitialize()
    {
        $request = Container_Request_Testable::GetHandler();
        $this->assertTrue(is_object($request));
        $this->assertTrue(get_class($request) == 'Container_Request');
    }

}