<?php
class Object_TalkTest extends PHPUnit_Framework_TestCase
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
        Base_Cache::flush();
    }

    public function testObjectTalkCreation()
    {
        $objTalk = new Object_Talk();
        $this->assertTrue(is_object($objTalk));
        $data = $objTalk->getSelf();
        $this->assertTrue($data['intTalkID'] == null);
        $this->assertTrue($data['strTalkTitle'] == null);
        $this->assertTrue($data['hasPGContent'] == null);
        $this->assertTrue($data['strTalkSummary'] == null);
        $this->assertTrue($data['intUserID'] == null);
        $this->assertTrue($data['intRequestedRoomID'] == null);
        $this->assertTrue($data['intRequestedSlotID'] == null);
        $this->assertTrue($data['intRoomID'] == null);
        $this->assertTrue($data['intSlotID'] == null);
        $this->assertTrue($data['intTrackID'] == null);
        $this->assertTrue($data['intLength'] == null);
        $this->assertTrue($data['jsonLinks'] == null);
        $this->assertTrue($data['isLocked'] == false);
        $this->assertTrue($data['jsonResources'] == null);
        $this->assertTrue($data['jsonOtherPresenters'] == null);
        $this->assertTrue($data['lastChange'] == null);
        $this->assertTrue($objTalk->getKey('intTalkID') == null);
    }
    
    public function testBrokerByID()
    {
        $objTalk = Object_Talk::brokerByID(1);
        $data = $objTalk->getSelf();
        $this->assertTrue($data != false);
        $this->assertTrue($data['intTalkID'] == 1);
        $this->assertTrue($data['strTalkTitle'] == "Keynote");
        $this->assertTrue($data['hasPGContent'] == null);
        $this->assertTrue($data['strTalkSummary'] == 'A welcome to Barcamps');
        $this->assertTrue($data['intUserID'] == 1);
        $this->assertTrue($data['intRequestedRoomID'] == 1);
        $this->assertTrue($data['intRequestedSlotID'] == 1);
        $this->assertTrue($data['intRoomID'] == 1);
        $this->assertTrue($data['intSlotID'] == 1);
        $this->assertTrue($data['intTrackID'] == null);
        $this->assertTrue($data['intLength'] == 1);
        $json_data = json_decode($data['jsonLinks'], true);
        $this->assertTrue(count($json_data) == 2);
        $this->assertTrue($data['isLocked'] == 1);
        $json_data = json_decode($data['jsonResources'], true);
        $this->assertTrue(count($json_data) == 1);
        $json_data = json_decode($data['jsonOtherPresenters'], true);
        $this->assertTrue(count($json_data) == 0);
    }
    
    public function testUnscheduleATalk()
    {
        Object_User::isSystem(true);
        $objTalk = Object_Talk::brokerByID(1);
        $this->assertTrue(is_object($objTalk));
        $objTalk->unschedule();
        $data = $objTalk->getSelf();
        $this->assertTrue($data['intRoomID'] == -1);
        $this->assertTrue($data['intSlotID'] == -1);
        $this->assertTrue($data['isLocked'] == 0);
        Object_User::isSystem(false);
    }
}
