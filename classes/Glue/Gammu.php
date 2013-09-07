<?php
/**
 * CampFire Manager is a scheduling tool predominently used at BarCamps to 
 * schedule talks based, mainly, on the number of people attending each talk
 * receives.
 *
 * PHP version 5
 *
 * @category Default
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */
/**
 * This glue is used to broker all inter-Gammu conversations.
 *
 * @category Glue_Gammu
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */

class Glue_Gammu implements Interface_Glue
{
    protected $objDbGammu = null;
    protected $strInterface = 'Gammu';
    protected $objDaemon = null;
    
    public function getGlue()
    {
        return $this->strInterface;
    }
    
    /**
     * Advise whether this Glue can send broadcast messages.
     *
     * @return string|boolean
     */
    public function canSendBroadcast()
    {
        return false;
    }
    
    /**
     * Advise whether this Glue can send Private/Directed messages.
     *
     * @return string|boolean
     */
    public function canSendPrivateMessage()
    {
        return $this->strInterface;
    }

    /**
     * This function instantiates the object using the supplied configuration
     * details.
     * 
     * @param array $arrConfigValues Values to use to set up the connection, or
     * failing that, details to read from the database.
     * 
     * @return Interface_Glue
     */
    public function __construct($arrConfigValues = array())
    {
        if (!isset($arrConfigValues['DBType']) || $arrConfigValues['DBType'] == null) {
            throw new InvalidArgumentException('Missing Gammu configuration value (DBType): ' . print_r($arrConfigValues, true));
        } elseif (!isset($arrConfigValues['DBHost']) || $arrConfigValues['DBHost'] == null) {
            throw new InvalidArgumentException('Missing Gammu configuration value (DBHost): ' . print_r($arrConfigValues, true));
        } elseif (($arrConfigValues['DBType'] != 'sqlite' && (!isset($arrConfigValues['DBPort']) || $arrConfigValues['DBPort'] == null))) {
            throw new InvalidArgumentException('Missing Gammu configuration value (DBPort): ' . print_r($arrConfigValues, true));
        } elseif (($arrConfigValues['DBType'] != 'sqlite' && (!isset($arrConfigValues['DBUser']) || $arrConfigValues['DBUser'] == null))) {
            throw new InvalidArgumentException('Missing Gammu configuration value (DBUser): ' . print_r($arrConfigValues, true));
        } elseif (($arrConfigValues['DBType'] != 'sqlite' && (!isset($arrConfigValues['DBPass']) || $arrConfigValues['DBPass'] == null))) {
            throw new InvalidArgumentException('Missing Gammu configuration value (DBPass): ' . print_r($arrConfigValues, true));
        } elseif (($arrConfigValues['DBType'] != 'sqlite' && (!isset($arrConfigValues['DBBase']) || $arrConfigValues['DBBase'] == null))) {
            throw new InvalidArgumentException('Missing Gammu configuration value (DBBase): ' . print_r($arrConfigValues, true));
        }

        if ($arrConfigValues['DBType'] != 'sqlite') {
            $this->objDbGammu = new PDO(
                    $arrConfigValues['DBType'] . 
                    ':host=' . $arrConfigValues['DBHost'] . 
                    ';port=' . $arrConfigValues['DBPort'] .
                    ';dbname=' . $arrConfigValues['DBBase'],
                    $arrConfigValues['DBUser'], 
                    $arrConfigValues['DBPass']);
        } else {
            $this->objDbGammu = new PDO($arrConfigValues['DBType'] . ':' . $arrConfigValues['DBHost']);
        }
        if ($this->objDbGammu->errorCode() > 0) {
            throw new UnexpectedValueException($this->objDbGammu->errorInfo());
        }
        $this->objDbGammu->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $this->objDaemon = Object_Daemon::brokerByColumnSearch('strDaemon', $this->strInterface);
        if ($this->objDaemon == false) {
            $this->objDaemon = new Object_Daemon();
            $this->objDaemon->setKey('strDaemon', $this->strInterface);
            $this->objDaemon->setKey('intInboundCounter', 0);
            $this->objDaemon->setKey('intOutboundCounter', 0);
            $this->objDaemon->setKey('intUniqueCounter', 0);
            $this->objDaemon->setKey('lastUsedSuccessfully', '1970-01-01 00:00:00');
            $this->objDaemon->write();
        }
    }

    /**
     * This function retrieves all the unprocessed SMS messages. Note, there is
     * no "public" code to speak of, it's all classed as private. There is no
     * notion of a "public" SMS.
     * 
     * @link https://code.google.com/p/campfiremanager/source/browse/trunk/libraries/SmsSource.php#24 Source of this code
     * 
     * @return void
     */
    public function read_private()
    {
        $sql1 = "SELECT `ID`, `TimeOut`, `Signal` FROM phones";
        $query1 = $this->objDbGammu->prepare($sql1);
        $query1->execute(array());
        $result1 = $query1->fetch(PDO::FETCH_ASSOC);
        $this->objDaemon->setKey('lastUsedSuccessfully', $result1['TimeOut']);
        $this->objDaemon->setKey('intScope', $result1['Signal']);
        $this->objDaemon->write();

        $sql = "SELECT ID, SenderNumber, UDH, TextDecoded, RecipientID FROM inbox WHERE processed = 'false'";
        $query = $this->objDbGammu->prepare($sql);
        $query->execute(array());
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        if ($result == false) {
            return null;
        }
        $UDH = array();
        foreach ($result as $message) {
            try {
                // UDH is the SMS standard handler for longer text messages.
                if ($message['UDH'] != '') {
                    // UDH starts 050003 (chars 0-5)
                    // (Not stored here!)
                    // then has a "unique message byte" (chars 6-7)
                    $uniq = 0 + substr($message['UDH'], 6, 2);
                    // then the number of messages in this long message (chars 8-9)
                    $size = 0 + substr($message['UDH'], 8, 2);
                    // lastly this message number (chars 10-11)
                    $mNum = 0 + substr($message['UDH'], 10, 2);
                    
                    // Store each part in an array
                    $UDH[$uniq][$mNum] = $message['TextDecoded'];
                    // Sort it by the message part (so in other words, sort 
                    // everything under $UDH[$uniq]
                    ksort($UDH[$uniq]);
                    // Then, if we've got the whole message, store it, then mark
                    // it as completed, and ditch the elements from the UDH
                    // array.
                    if(count($UDH[$uniq]) == $size) {
                        if (count(Object_Input::brokerByColumnSearch('strSender', $message['SenderNumber'], false, false, 1, 'DESC')) == 0) {
                            $this->objDaemon->setKey('intUniqueCounter', $this->objDaemon->getKey('intUniqueCounter') + 1);
                        }

                        $text = '';
                        foreach($UDH[$uniq] as $textPart) {
                            $text .= $textPart;
                        }
                        $strSender = $message['SenderNumber'];
                        $textMessage = $text;
                        $intNativeID = $message['ID'];
                        Object_Input::import($strSender, $this->strInterface, $textMessage, $intNativeID);
                        $sqlUpdateInbox = "UPDATE inbox SET processed = 'true' WHERE UDH = ?";
                        $qryUpdateInbox = $this->objDbGammu->prepare($sqlUpdateInbox);
                        $qryUpdateInbox->execute(array('050003' . $uniq . '%'));
                        unset($UDH[$uniq]);

                        $this->objDaemon->setKey('intInboundCounter', $this->objDaemon->getKey('intInboundCounter') + 1);
                        $this->objDaemon->write();
                    }
                } else {
                    if (count(Object_Input::brokerByColumnSearch('strSender', $message['SenderNumber'], false, false, 1, 'DESC')) == 0) {
                        $this->objDaemon->setKey('intUniqueCounter', $this->objDaemon->getKey('intUniqueCounter') + 1);
                    }

                    $strSender = $message['SenderNumber'];
                    $textMessage = $message['TextDecoded'];
                    $intNativeID = $message['ID'];
                    Object_Input::import($strSender, $this->strInterface, $textMessage, $intNativeID);
                    $sqlUpdateInbox = "UPDATE inbox SET processed = 'true' WHERE ID = ?";
                    $qryUpdateInbox = $this->objDbGammu->prepare($sqlUpdateInbox);
                    $qryUpdateInbox->execute(array($message['ID']));

                    $this->objDaemon->setKey('intInboundCounter', $this->objDaemon->getKey('intInboundCounter') + 1);
                    $this->objDaemon->setKey('lastUsedSuccessfully', date('Y-m-d H:i:s'));
                    $this->objDaemon->write();
                }
            } catch (Exception $e) {
                error_log('Error moving data from Gammu Inbox to CFM2 Inbox: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * This function is here to comply with interface requirements, but is
     * not actually used.
     * 
     * @return void
     */
    public function read_public()
    {
        
    }
        
    /**
     * This function is here to comply with interface requirements, but is
     * not actually used.
     * 
     * @return void
     */
    public function follow_followers()
    {
        
    }
    
    /**
     * This function calls the service, sending a message.
     * 
     * @return void
     */
    public function send()
    {
        $sqlInsert = 'INSERT INTO outbox '
            . '(CreatorID, MultiPart, DestinationNumber, UDH, TextDecoded, Coding, SenderID)'
            . ' VALUES '
            . '(:CreatorID, :MultiPart, :DestinationNumber, :UDH, :TextDecoded, :Coding, :SenderID);';
        $qryInsert = $this->objDbGammu->prepare($sqlInsert);
        $sqlInsertLarge = 'INSERT INTO outbox_multipart '
            . '(SequencePosition, UDH, TextDecoded, ID, Coding)'
            . ' VALUES '
            . '(:SequencePosition, :UDH, :TextDecoded, :ID, :Coding);';
        $qryInsertLarge = $this->objDbGammu->prepare($sqlInsertLarge);

        $messages = Object_Output::brokerByColumnSearch('isActioned', false);
        foreach ($messages as $message) {
            if ($message->getKey('strInterface') != $this->strInterface) {
                continue;
            }
            if ($message->getKey('strReceiver') == null || $message->getKey('strReceiver') == '') {
                $message->setKey('strError', 'No recipient');
                $message->setKey('isActioned', 1);
                $message->write();
                continue;
            }
            try {
                // Reset the insert data
                $data = array(
                    'CreatorID' => 'CFM2',
                    'MultiPart' => false,
                    'DestinationNumber' => $message->getKey('strReceiver'),
                    'UDH' => null,
                    'TextDecoded' => '',
                    'Coding' => 'Default_No_Compression',
                    'SenderID' => 'cfm2'
                );

                // So, now we've got our defaults set up - let's check whether this
                // is actually more than 1 message long.
                $arrMessageChunks = str_split($message->getKey('textMessage'), 160);
                if (count($arrMessageChunks) == 1) {
                    $data['TextDecoded'] = $message->getKey('textMessage');
                    $qryInsert->execute($data);
                } else {
                    $uniq = str_pad(dechex(rand(0, 255)), 2, "0");
                    $size = str_pad(dechex(count($arrMessageChunks)), 2, "0");
                    foreach ($arrMessageChunks as $intChunkID => $strChunk) {
                        $intChunkID++;
                        $mNum = str_pad(dechex($intChunkID), 2, "0");
                        $data['UDH'] = '050003' . $uniq . $size . $mNum;
                        if ($intChunkID == 1) {
                            $data['TextDecoded'] = $strChunk;
                            $qryInsert->execute($data);
                            $LargeData['ID'] = $this->objDbGammu->lastInsertId();
                        } else {
                            //(:SequencePosition, :UDH, :TextDecoded, :ID, :Coding)
                            $LargeData['UDH'] = $data['UDH'];
                            $LargeData['SequencePosition'] = $intChunkID;
                            $LargeData['TextDecoded'] = $strChunk;
                            $LargeData['Coding'] = $data['Coding'];
                            $qryInsertLarge->execute($LargeData);
                        }
                    }
                }
                $message->setKey('isActioned', 1);
                $message->write();
                $this->objDaemon->setKey('intOutboundCounter', $this->objDaemon->getKey('intOutboundCounter') + 1);
                $this->objDaemon->setKey('lastUsedSuccessfully', date('Y-m-d H:i:s'));
                $this->objDaemon->write();
            } catch (Exception $e) {
                error_log('Error moving data from CFM2 Outbox to Gammu Outbox at ' . $e->getLine() . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * This function returns an array containing the objects for all these glues
     * 
     * @return array
     */
    public static function brokerAllGlues()
    {
        $arrConfig = Object_SecureConfig::brokerAll();
        $config = array();
        if (isset($arrConfig['Gammu_DBType'])) {
            $config['DBType'] = $arrConfig['Gammu_DBType']->getKey('value');
        }
        if (isset($arrConfig['Gammu_DBHost'])) {
            $config['DBHost'] = $arrConfig['Gammu_DBHost']->getKey('value');
        }
        if (isset($arrConfig['Gammu_DBPort'])) {
            $config['DBPort'] = $arrConfig['Gammu_DBPort']->getKey('value');
        }
        if (isset($arrConfig['Gammu_DBUser'])) {
            $config['DBUser'] = $arrConfig['Gammu_DBUser']->getKey('value');
        }
        if (isset($arrConfig['Gammu_DBPass'])) {
            $config['DBPass'] = $arrConfig['Gammu_DBPass']->getKey('value');
        }
        if (isset($arrConfig['Gammu_DBBase'])) {
            $config['DBBase'] = $arrConfig['Gammu_DBBase']->getKey('value');
        }
        if (count($config) > 0) {
            return array(new Glue_Gammu($config));
        } else {
            return array();
        }
    }
}