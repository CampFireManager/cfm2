<?php
class Base_GeneralFunctionsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException BadMethodCallException
     */
    public function testInitialize()
    {
        @$fail = new Base_GeneralFunctions();
    }
    
    public function testGetValue()
    {
        $this->assertTrue(Base_GeneralFunctions::getValue(array('test' => true), 'test', false, true));
        $this->assertFalse(Base_GeneralFunctions::getValue(array(), 'test', false, true));
        $this->assertFalse(Base_GeneralFunctions::getValue(array('test' => ''), 'test', false, true));
        $this->assertFalse(Base_GeneralFunctions::getValue(null, 'test', false, true));
        $this->assertTrue(Base_GeneralFunctions::getValue(new SimpleObjectForTesting(), 'value', false, true));
        $this->assertFalse(Base_GeneralFunctions::getValue(new SimpleObjectForTesting(), 'non-existant value', false, true));
    }
    
    public function testAsBoolean()
    {
        $this->assertTrue(Base_GeneralFunctions::asBoolean(true));
        $this->assertTrue(Base_GeneralFunctions::asBoolean('true'));
        $this->assertTrue(Base_GeneralFunctions::asBoolean(1));
        $this->assertTrue(Base_GeneralFunctions::asBoolean('yes'));
        $this->assertFalse(Base_GeneralFunctions::asBoolean(false));
        $this->assertFalse(Base_GeneralFunctions::asBoolean('false'));
        $this->assertFalse(Base_GeneralFunctions::asBoolean(0));
        $this->assertFalse(Base_GeneralFunctions::asBoolean('no'));
        $this->assertFalse(Base_GeneralFunctions::asBoolean('Anything Else!'));
    }
    
    public function testJsonSizing()
    {
        $this->assertTrue(Base_GeneralFunctions::sizeJson('') == 1);
        $this->assertTrue(Base_GeneralFunctions::sizeJson(json_encode(array())) == 1);
        $this->assertTrue(Base_GeneralFunctions::sizeJson(json_encode(array('Item' => 'Value'))) == 1);
        $this->assertTrue(Base_GeneralFunctions::sizeJson(json_encode(array('Item' => 'Value', 'Item2' => 'Value'))) == 2);
    }
    
    public function testJsonAdding()
    {
        $json = Base_GeneralFunctions::addJson('', 'Data', 'Data');
        $this->assertTrue($json == '{"Data":"Data"}');
        $json = Base_GeneralFunctions::addJson($json, 'More Data', 'More Data');
        $this->assertTrue($json == '{"Data":"Data","More Data":"More Data"}');
        $json = Base_GeneralFunctions::addJson($json, 'More Data', 'Some More Data');
        $this->assertTrue($json == '{"Data":"Data","More Data":"Some More Data"}');
    }
    
    public function testJsonDeleting()
    {
        $json = Base_GeneralFunctions::delJson('{"Data":"Data"}', 'Data');
        $this->assertTrue($json == '[]');
        $json = Base_GeneralFunctions::delJson('{"Data":"Data","More Data":"More Data"}', 'Data');
        $this->assertTrue($json == '{"More Data":"More Data"}');
    }
    
    public function testGetJson()
    {
        $this->assertTrue(Base_GeneralFunctions::getJson('Not Actually JSON') == array(0 => 'Not Actually JSON'));
        $json = '{"Data":"Data"}';
        $this->assertTrue(Base_GeneralFunctions::getJson($json) == array('Data' => 'Data'));
        $json = json_encode(array('Some Data' => array('Stuff' => 'More Stuff')));
        $this->assertTrue(Base_GeneralFunctions::getJson($json) == array('Some Data' => array('Stuff' => 'More Stuff')));
    }
}

class SimpleObjectForTesting
{
    public $value = true;
}