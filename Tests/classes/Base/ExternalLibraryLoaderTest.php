<?php
class Base_ExternalLibraryLoaderTest extends PHPUnit_Framework_TestCase
{
    public function testInitialize()
    {
        $ell = new Base_ExternalLibraryLoader();
        $this->assertTrue(is_object($ell));
        $this->assertTrue(get_class($ell) == 'Base_ExternalLibraryLoader');
    }
}