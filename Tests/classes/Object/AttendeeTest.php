<?php
class Object_AttendeeTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $config = Container_Config_Testable::GetHandler();
        $config->LoadFile('democonfig.php');
        $config->SetUpDatabaseConnection();
        $objConfig = new Object_Config_Demo();
        $objConfig->initializeDemo();
        $objSecureConfig = new Object_SecureConfig_Demo();
        $objSecureConfig->initializeDemo();
        $config->LoadDatabaseConfig();
        $objAttendee = new Object_Attendee_Demo();
        $objAttendee->initializeDemo();
    }
    
    public function testObjectAttendeeCreation()
    {
        $objAttendee = new Object_Attendee();
        $this->assertTrue(is_object($objAttendee));
        $data = $objAttendee->getSelf();
        $this->assertTrue($data['intAttendeeID'] == null);
        $this->assertTrue($data['intUserID'] == null);
        $this->assertTrue($data['intTalkID'] == null);
        $this->assertTrue($data['lastChange'] == null);
        $this->assertTrue($objAttendee->getKey('intAttendeeID') == null);
    }
    
    public function testBrokerAllAttendeeObjects()
    {
        $data = Object_Attendee::brokerAll();
        $this->assertTrue(count($data) == 3);
        $item = $data[0]->getSelf();
        $this->assertTrue($item['intAttendeeID'] == 1);
        $this->assertTrue($item['intUserID'] == '2');
        $this->assertTrue($item['intTalkID'] == '1');
        $this->assertTrue($data[1]->getKey('intAttendeeID') == 2);
        $this->assertTrue($data[1]->getKey('intUserID') == '3');
        $this->assertTrue($data[1]->getKey('intTalkID') == '1');
        $this->assertTrue($data[2]->getKey('intAttendeeID') == 3);
        $this->assertTrue($data[2]->getKey('intUserID') == '4');
        $this->assertTrue($data[2]->getKey('intTalkID') == '1');
    }
    
    public function testBrokerByIDAttendeeObjects()
    {
        $data = Object_Attendee::brokerByID(1);
        $this->assertTrue(is_object($data));
        $item = $data->getSelf();
        $this->assertTrue($item['intAttendeeID'] == 1);
        $this->assertTrue($item['intUserID'] == '2');
        $this->assertTrue($item['intTalkID'] == '1');
    }
}