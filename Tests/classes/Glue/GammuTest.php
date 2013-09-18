<?php
class Glue_GammuTest extends PHPUnit_Framework_TestCase
{
    /**
     * Holding variable for the setup testing database
     * @var PDO
     */
    protected $objDbGammu = null;
    
    /**
     * Holding variable for the setup testing class
     * @var Glue_GammuTestable
     */
    protected $objGlueGammu = null;
    
    public function setUp()
    {
        Base_Cache::flush();
        $config = Container_Config_Testable::GetHandler();
        $config->LoadFile('unittest.php');
        $config->SetUpDatabaseConnection();
        $objConfig = new Object_Config_Demo();
        $objConfig->initializeDemo();
        $objSecureConfig = new Object_SecureConfig_Demo();
        $objSecureConfig->initializeDemo();
        $config->LoadDatabaseConfig();
        $objDaemon = new Object_Daemon();
        $objDaemon->initialize();
        $objInput = new Object_Input();
        $objInput->initialize();
        $objOutput = new Object_Output();
        $objOutput->initialize();
        $this->objGlueGammu = NULL;
        $this->objGlueGammu = Glue_GammuTestable::initialize();
        $this->objDbGammu = Glue_GammuTestable::getDb($this->objGlueGammu);
    }
    
    public function testSetupHasWorked()
    {
        $this->assertTrue(get_class($this->objDbGammu) == "PDO");
    }
    
    public function testMessageInjectionWorks()
    {
        Glue_GammuTestable::injectInboxItem($this->objGlueGammu, '+447777777777', 'testing');
        $qry = $this->objDbGammu->prepare("SELECT * FROM inbox");
        $qry->execute();
        $result = $qry->fetchAll(PDO::FETCH_ASSOC);
        $this->assertTrue(count($result) == 1);
        $this->assertTrue($result[0]['SenderNumber'] == "+447777777777");
        $this->assertTrue($result[0]['TextDecoded'] == "testing");
        $this->assertTrue($result[0]['RecipientID'] == "Glue_Gammu-SMSD");
        $this->assertTrue($result[0]['Processed'] == "false");
        Glue_GammuTestable::injectInboxItem($this->objGlueGammu, '+447777777777', 'testing 2');
        $qry2 = $this->objDbGammu->prepare("SELECT * FROM inbox");
        $qry2->execute();
        $result2 = $qry2->fetchAll(PDO::FETCH_ASSOC);
        $this->assertTrue(count($result2) == 2);
    }

    public function testResetWorks()
    {
        Glue_GammuTestable::injectInboxItem($this->objGlueGammu, '+447777777777', 'testing');
        $qry = $this->objDbGammu->prepare("SELECT * FROM inbox");
        $qry->execute();
        $result = $qry->fetchAll(PDO::FETCH_ASSOC);
        $this->assertTrue(count($result) == 1);
    }
    
    public function testProcessInbox()
    {
        Glue_GammuTestable::injectInboxItem($this->objGlueGammu, '+447777777777', 'testing');
        $this->objGlueGammu->read_private();
        $arrInput = Object_Input::brokerAll();
        $this->assertTrue(count($arrInput) == 1);
        $this->assertTrue(is_object($arrInput[1]));
        $this->assertTrue($arrInput[1]->getKey('textMessage') == 'testing');
        $this->assertTrue($arrInput[1]->getKey('strSender') == '+447777777777');
        $this->assertTrue($arrInput[1]->getKey('strInterface') == 'Gammu');
    }

    public function testReplyToInbox()
    {
        $arrOutbox = Glue_GammuTestable::checkOutboxItem($this->objGlueGammu);
        $this->assertTrue(count($arrOutbox) == 0);
        Glue_GammuTestable::injectInboxItem($this->objGlueGammu, '+447777777777', 'testing');
        $this->objGlueGammu->read_private();
        $arrInput = Object_Input::brokerAll();
        $this->assertTrue(count($arrInput) == 1);
        $this->assertTrue(is_object($arrInput[1]));
        Object_Output::replyToInput($arrInput[1], "Test Succeeded");
        $system_state = Object_User::isSystem();
        Object_User::isSystem(true);
        $arrGlue = Glue_GammuTestable::brokerAllGlues();
        foreach ($arrGlue as $objGlue) {
            $objGlue->send();
        }
        Object_User::isSystem($system_state);
        $arrOutbox2 = Glue_GammuTestable::checkOutboxItem($this->objGlueGammu);
        $this->assertTrue(count($arrOutbox2) == 1);
        $this->assertTrue($arrOutbox2[0]['DestinationNumber'] == "+447777777777"); 
        $this->assertTrue($arrOutbox2[0]['TextDecoded'] == "Test Succeeded");
        $this->assertTrue($arrOutbox2[0]['SenderID'] == "cfm2");
    }

    
    public function testSendMessage()
    {
        $arrOutbox = Glue_GammuTestable::checkOutboxItem($this->objGlueGammu);
        $this->assertTrue(count($arrOutbox) == 0);
        $system_state = Object_User::isSystem();
        Object_User::isSystem(true);
        $arrGlue = Glue_GammuTestable::brokerAllGlues();
        foreach ($arrGlue as $objGlue) {
            $this->assertTrue($objGlue->getGlue() == "Gammu");
            $strInterface = $objGlue->canSendPrivateMessage();
            if ($strInterface != false) {
                $output = new Object_Output();
                $output->setKey('textMessage', "Testing 123");
                $output->setKey('strInterface', $strInterface);
                $output->setKey('strReceiver', '+447777777777');
                $output->create();
                $objGlue->send();
            }
        }
        Object_User::isSystem($system_state);
        $arrOutbox2 = Glue_GammuTestable::checkOutboxItem($this->objGlueGammu);
        $this->assertTrue(count($arrOutbox2) == 1);
        $this->assertTrue($arrOutbox2[0]['DestinationNumber'] == "+447777777777"); 
        $this->assertTrue($arrOutbox2[0]['TextDecoded'] == "Testing 123");
        $this->assertTrue($arrOutbox2[0]['SenderID'] == "cfm2");
    }

    public function testSendLongerMessage()
    {
        $arrOutbox = Glue_GammuTestable::checkOutboxItem($this->objGlueGammu);
        $this->assertTrue(count($arrOutbox) == 0);
        $system_state = Object_User::isSystem();
        Object_User::isSystem(true);
        $arrGlue = Glue_GammuTestable::brokerAllGlues();
        foreach ($arrGlue as $objGlue) {
            $this->assertTrue($objGlue->getGlue() == "Gammu");
            $strInterface = $objGlue->canSendPrivateMessage();
            if ($strInterface != false) {
                $output = new Object_Output();
                $output->setKey('textMessage', "123456789012345678901234567890"
                                              ."123456789012345678901234567890"
                                              ."123456789012345678901234567890"
                                              ."123456789012345678901234567890"
                                              ."123456789012345678901234567890"
                                              ."123456789012345678901234567890");
                $output->setKey('strInterface', $strInterface);
                $output->setKey('strReceiver', '+447777777777');
                $output->create();
                $objGlue->send();
            }
        }
        Object_User::isSystem($system_state);
        
        $arrOutbox2 = Glue_GammuTestable::checkOutboxItem($this->objGlueGammu);
        $arrOutbox3 = Glue_GammuTestable::checkOutboxMultipart($this->objGlueGammu);
        $this->assertTrue(count($arrOutbox2) == 1);
        $this->assertTrue($arrOutbox2[0]['DestinationNumber'] == "+447777777777"); 
        $this->assertTrue($arrOutbox2[0]['TextDecoded'] == "123456789012345678901234567890"
                                                          ."123456789012345678901234567890"
                                                          ."123456789012345678901234567890"
                                                          ."123456789012345678901234567890"
                                                          ."123456789012345678901234567890"
                                                          ."1234567890");
        $this->assertTrue($arrOutbox2[0]['SenderID'] == "cfm2");
        $this->assertTrue(substr($arrOutbox2[0]['UDH'], -4) === '2010');
        $this->assertTrue(substr($arrOutbox2[0]['UDH'], 0, 10) === substr($arrOutbox3[0]['UDH'], 0, 10));
        $this->assertTrue(substr($arrOutbox3[0]['UDH'], -4) === '2020');
        $this->assertTrue($arrOutbox3[0]['TextDecoded'] == "12345678901234567890");
        $this->assertTrue(substr($arrOutbox2[0]['UDH'], 0, 10) === substr($arrOutbox3[0]['UDH'], 0, 10));
    }

}

class Glue_GammuTestable extends Glue_Gammu
{
    /**
     * Inject a received message into Gammu's Inbox queue
     * 
     * @param Glue_Gammu $self         The Glue_Gammu object to use
     * @param string     $number       The phone number the "message" was 
     * "received" from
     * @param string     $message      The "message" received
     * @param string     $actioned     A string version of whether or not the 
     * message has been processed by us
     * @param string     $datereceived The "date" the message was "received"
     * @param string     $dateinserted The "date" the message was injected into 
     * the table
     *
     * @return Glue_Gammu
     */
    public static function injectInboxItem(
        $self, $number, $message, $actioned = "false", $datereceived = '', 
        $dateinserted = ''
    ) {
        if ($datereceived == '') {
            $datereceived = date('Y-m-d H:i:s');
        }
        if ($dateinserted == '') {
            $dateinserted = $datereceived;
        }
        $inject = $self->objDbGammu->prepare("INSERT INTO `inbox` (`UpdatedInDB`, `ReceivingDateTime`, `Text`, `SenderNumber`, `Coding`, `UDH`, `SMSCNumber`, `Class`, `TextDecoded`, `ID`, `RecipientID`, `Processed`) VALUES 
            (:datereceived, :dateinserted, '', :number, 'Default_No_Compression', '', 
            '+447771234567', -1, :message, NULL, 'Glue_Gammu-SMSD', :actioned)");
        $inject->execute(array('datereceived' => $datereceived, 'dateinserted' => $dateinserted, 'number' => $number, 'message' => $message, 'actioned' => $actioned));
        return $self;
    }

    /**
     * Gets a collection of data from the Outbox storage
     * 
     * @param Glue_Gammu $self
     * 
     * @return array
     */
    public static function checkOutboxItem($self)
    {
        $read = $self->objDbGammu->prepare("SELECT * FROM `outbox`");
        $read->execute();
        $result = $read->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * Gets a collection of data from the Outbox storage
     * 
     * @param Glue_Gammu $self
     * 
     * @return array
     */
    public static function checkOutboxMultipart($self)
    {
        $read = $self->objDbGammu->prepare("SELECT * FROM `outbox_multipart`");
        $read->execute();
        $result = $read->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    
    /**
     * Return the DB object
     * 
     * @param Glue_GammuTestable $self This testable suite
     * 
     * @return PDO
     */
    public static function getDb($self)
    {
        return $self->objDbGammu;
    }
    
    /**
     * Initialize a Unit Testing SQLite database for further processing.
     * 
     * @param string $path A path to where to hold this sqlite database
     * 
     * @return Glue_GammuTestable
     */
    public static function initialize($path = '')
    {
        if ($path == '') {
            $path = dirname(__FILE__) . '/../../../config/unittest_gammu.sqlite';
        }
        $arrConfigValues = array();
        $arrConfigValues['GluePrefix'] = "Glue_Gammu-SMSD";
        $arrConfigValues['DBType'] = "sqlite";
        $arrConfigValues['DBHost'] = $path;
        if (file_exists($path)) {
            unlink($path);
        }
        $self = new Glue_GammuTestable($arrConfigValues);
        // This is based on the Gammu SMSD version 13 SQLite Schema
        $self->objDbGammu->exec("
            CREATE TABLE inbox (
              UpdatedInDB NUMERIC NOT NULL DEFAULT (datetime('now')),
              ReceivingDateTime NUMERIC NOT NULL DEFAULT (datetime('now')),
              Text TEXT NOT NULL,
              SenderNumber TEXT NOT NULL DEFAULT '',
              Coding TEXT NOT NULL DEFAULT 'Default_No_Compression',
              UDH TEXT NOT NULL,
              SMSCNumber TEXT NOT NULL DEFAULT '',
              Class INTEGER NOT NULL DEFAULT '-1',
              TextDecoded TEXT NOT NULL DEFAULT '',
              ID INTEGER PRIMARY KEY AUTOINCREMENT,
              RecipientID TEXT NOT NULL,
              Processed TEXT NOT NULL DEFAULT 'false',
              CHECK (Coding IN 
              ('Default_No_Compression','Unicode_No_Compression','8bit','Default_Compression','Unicode_Compression')) 
            );
            CREATE TABLE outbox (
              UpdatedInDB NUMERIC NOT NULL DEFAULT (datetime('now')),
              InsertIntoDB NUMERIC NOT NULL DEFAULT (datetime('now')),
              SendingDateTime NUMERIC NOT NULL DEFAULT (datetime('now')),
              SendBefore time NOT NULL DEFAULT '23:59:59',
              SendAfter time NOT NULL DEFAULT '00:00:00',
              Text TEXT,
              DestinationNumber TEXT NOT NULL DEFAULT '',
              Coding TEXT NOT NULL DEFAULT 'Default_No_Compression',
              UDH TEXT,
              Class INTEGER DEFAULT '-1',
              TextDecoded TEXT NOT NULL DEFAULT '',
              ID INTEGER PRIMARY KEY AUTOINCREMENT,
              MultiPart TEXT NOT NULL DEFAULT 'false',
              RelativeValidity INTEGER DEFAULT '-1',
              SenderID TEXT,
              SendingTimeOut NUMERIC NOT NULL DEFAULT (datetime('now')),
              DeliveryReport TEXT DEFAULT 'default',
              CreatorID TEXT NOT NULL,
              CHECK (Coding IN 
              ('Default_No_Compression','Unicode_No_Compression','8bit','Default_Compression','Unicode_Compression')),
              CHECK (DeliveryReport IN ('default','yes','no'))
            );
            CREATE TABLE outbox_multipart (
              Text TEXT,
              Coding TEXT NOT NULL DEFAULT 'Default_No_Compression',
              UDH TEXT,
              Class INTEGER DEFAULT '-1',
              TextDecoded TEXT DEFAULT NULL,
              ID INTEGER,
              SequencePosition INTEGER NOT NULL DEFAULT '1',
              CHECK (Coding IN 
              ('Default_No_Compression','Unicode_No_Compression','8bit','Default_Compression','Unicode_Compression')),
             PRIMARY KEY (ID, SequencePosition)
            );
            CREATE TABLE phones (
              ID TEXT NOT NULL,
              UpdatedInDB NUMERIC NOT NULL DEFAULT (datetime('now')),
              InsertIntoDB NUMERIC NOT NULL DEFAULT (datetime('now')),
              TimeOut NUMERIC NOT NULL DEFAULT (datetime('now')),
              Send TEXT NOT NULL DEFAULT 'no',
              Receive TEXT NOT NULL DEFAULT 'no',
              IMEI TEXT PRIMARY KEY NOT NULL,
              Client TEXT NOT NULL,
              Battery INTEGER NOT NULL DEFAULT -1,
              Signal INTEGER NOT NULL DEFAULT -1,
              Sent INTEGER NOT NULL DEFAULT 0,
              Received INTEGER NOT NULL DEFAULT 0
            );
        ");
        $self->objDbGammu->exec("INSERT INTO `phones` (`ID`, `UpdatedInDB`, `InsertIntoDB`, `TimeOut`, `Send`, `Receive`, `IMEI`, `Client`, `Battery`, `Signal`, `Sent`, `Received`) VALUES ('Glue_Gammu-SMSD', '" . date("Y-m-d H:i:s") . "', '" . date("Y-m-d H:i:s") . "', '" . date("Y-m-d H:i:s") . "', 'yes', 'yes', '123456789012345', 'Dummy Client String', 0, 100, 0, 5);");
        return $self;
    }
    
    public static function brokerAllGlues($path = '') {
        if ($path == '') {
            $path = dirname(__FILE__) . '/../../../config/unittest_gammu.sqlite';
        }
        $arrConfigValues = array();
        $arrConfigValues['GluePrefix'] = "Glue_Gammu-SMSD";
        $arrConfigValues['DBType'] = "sqlite";
        $arrConfigValues['DBHost'] = $path;
        return array(new Glue_Gammu($arrConfigValues));
    }
}
