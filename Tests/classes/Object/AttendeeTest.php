<?php
class Object_AttendeeTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $config = Container_Config_Testable::GetHandler();
        $config->LoadFile('unittest.php');
        $config->SetUpDatabaseConnection();
        $objConfig = new Object_Config_Demo();
        $objConfig->initializeDemo();
        $objSecureConfig = new Object_SecureConfig_Demo();
        $objSecureConfig->initializeDemo();
        $config->LoadDatabaseConfig();
        $objAttendee = new Object_Attendee_Demo();
        $objAttendee->initializeDemo();
        $objDefaultSlotType = new Object_DefaultSlotType_Demo();
        $objDefaultSlotType->initializeDemo();
        $objResource = new Object_Resource_Demo();
        $objResource->initializeDemo();
        $objRoom = new Object_Room_Demo();
        $objRoom->initializeDemo();
        $objSlot = new Object_Slot_Demo();
        $objSlot->initializeDemo();
        $objTalk = new Object_Talk_Demo();
        $objTalk->initializeDemo();
        $objUser = new Object_User_Demo();
        $objUser->initializeDemo();
        Object_User::isSystem(false);
    }
    
    public function testObjectAttendeeCreation()
    {
        $objAttendee = new Object_Attendee();
        $this->assertTrue(is_object($objAttendee));
        $data = $objAttendee->getSelf();
        $this->assertTrue($data['intAttendeeID'] == null);
        $this->assertTrue($data['intUserID'] == null);
        $this->assertTrue($data['intTalkID'] == null);
        $this->assertTrue($objAttendee->getKey('intAttendeeID') == null);
    }

    public function testBrokerByIDAttendeeObjects()
    {
        $data = Object_Attendee::brokerByID(1);
        $this->assertTrue(is_object($data));
        $item = $data->getSelf();
        $this->assertTrue($item['intAttendeeID'] == 1);
        $this->assertTrue($item['intUserID'] == '2');
        $this->assertTrue($item['intTalkID'] == '1');
        $this->assertFalse(Object_Attendee::brokerByID(0));
        $data = Object_Attendee::brokerByID(1);
        $this->assertTrue(is_object($data));
        $item = $data->getSelf();
        $this->assertTrue($item['intAttendeeID'] == 1);
        $this->assertTrue($item['intUserID'] == '2');
        $this->assertTrue($item['intTalkID'] == '1');
    }          

    /**
     * @expectedException OutOfBoundsException
     */
    public function testFailBrokerByColumnSearchNoColumn()
    {
        @Object_Attendee::brokerByColumnSearch();
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testFailBrokerByColumnSearch()
    {
        @Object_Attendee::brokerByColumnSearch('dummy', '1');
    }
    
    /**
     * @expectedException OutOfBoundsException
     */
    public function testFailCountByColumnSearchNoColumn()
    {
        @Object_Attendee::countByColumnSearch();
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testFailCountByColumnSearch()
    {
        @Object_Attendee::countByColumnSearch('dummy', '1');
    }

    public function testCountCommands()
    {
        $this->assertTrue(5 == Object_Attendee::countAll());
        $this->assertTrue(0 == Object_Attendee::countByColumnSearch('intTalkID'));
        $this->assertTrue(0 == Object_Attendee::countByColumnSearch('intTalkID', '0'));
        $this->assertTrue(3 == Object_Attendee::countByColumnSearch('intTalkID', '1'));
        $this->assertTrue(5 == Object_Attendee::countByColumnSearch('intTalkID', '%'));
    }
    
    public function testBrokerByColumnSearchAttendeeObjects()
    {
        $data = Object_Attendee::brokerByColumnSearch('intTalkID');
        $this->assertTrue(is_array($data));
        $this->assertTrue(count($data) == 0);
        $data = Object_Attendee::brokerByColumnSearch('intTalkID', '0');
        $this->assertTrue(is_array($data));
        $this->assertTrue(count($data) == 0);
        $data = Object_Attendee::brokerByColumnSearch('intTalkID', '1');
        $this->assertTrue(count($data) == 3);
        $item = $data[1]->getSelf();
        $this->assertTrue($item['intAttendeeID'] == 1);
        $this->assertTrue($item['intUserID'] == '2');
        $this->assertTrue($item['intTalkID'] == '1');
        $data = Object_Attendee::brokerByColumnSearch('intTalkID', '%');
        $this->assertTrue(count($data) == 5);
        $item = $data[1]->getSelf();
        $this->assertTrue($item['intAttendeeID'] == 1);
        $this->assertTrue($item['intUserID'] == '2');
        $this->assertTrue($item['intTalkID'] == '1');
    }
    
    public function testBrokerAllAttendeeObjects()
    {
        $data = Object_Attendee::brokerAll();
        $this->assertTrue(count($data) == 5);
        $item = $data[1]->getSelf();
        $this->assertTrue($item['intAttendeeID'] == 1);
        $this->assertTrue($item['intUserID'] == '2');
        $this->assertTrue($item['intTalkID'] == '1');
        $this->assertTrue($data[2]->getKey('intAttendeeID') == 2);
        $this->assertTrue($data[2]->getKey('intUserID') == '3');
        $this->assertTrue($data[2]->getKey('intTalkID') == '1');
        $this->assertTrue($data[3]->getKey('intAttendeeID') == 3);
        $this->assertTrue($data[3]->getKey('intUserID') == '4');
        $this->assertTrue($data[3]->getKey('intTalkID') == '1');
    }

    public function testLastModified()
    {
        $this->assertTrue(preg_match('/\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d/', Object_Attendee::lastChangeAll()) == 1);
        $this->assertNull($data = Object_Attendee::lastChangeByColumnSearch('intTalkID'));
        $this->assertNull($data = Object_Attendee::lastChangeByColumnSearch('intTalkID', '0'));
        $this->assertTrue(preg_match('/\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d/', Object_Attendee::lastChangeByColumnSearch('intTalkID', '1')) == 1);
        $this->assertTrue(preg_match('/\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d/', Object_Attendee::lastChangeByColumnSearch('intTalkID', '%')) == 1);
    }
    
    public function testGetSelf()
    {
        $data = Object_Attendee::brokerByID(1);
        $this->assertFalse($data->isFull());
        $item = $data->getSelf();
        $this->assertTrue(count($item) == 7);
        $this->assertTrue($item['intAttendeeID'] == 1);
        $this->assertTrue($item['intUserID'] == '2');
        $this->assertTrue($item['intTalkID'] == '1');
        $this->assertTrue(preg_match('/\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d/', $item['lastChange']) == 1);
        $data->setFull(true);
        $this->assertTrue($data->isFull());
        $item = $data->getSelf();
        $this->assertTrue(count($item) == 10);
        $this->assertTrue($item['intAttendeeID'] == 1);
        $this->assertTrue($item['intUserID'] == '2');
        $this->assertTrue($item['intTalkID'] == '1');
        $this->assertTrue(preg_match('/\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d/', $item['lastChange']) == 1);
        $this->assertTrue(is_array($item['isEditable']));
        $this->assertTrue(count($item['isEditable']) == 0);
        $objUser = new Object_User_Demo();
        $objUser->initializeDemo();
        $objUserauth = new Object_Userauth_Demo();
        $objUserauth->initializeDemo();
    }
}