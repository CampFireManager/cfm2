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
    protected $strInterface = null;
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
        if (isset($arrConfigValues['GluePrefix'])) {
            $GluePrefix = $arrConfigValues['GluePrefix'];
        } else {
            $GluePrefix = 'Gammu';
        }
        
        if (isset($arrConfigValues['DBType'])) {
            $DBType = $arrConfigValues['DBType'];
        } else {
            $DBType = Object_SecureConfig::brokerByID($GluePrefix . '_DBType');
            if (is_object($DBType)) {
                $DBType = $DBType->getKey('value');
            }
        }
        if (isset($arrConfigValues['DBHost'])) {
            $DBHost = $arrConfigValues['DBHost'];
        } else {
            $DBHost = Object_SecureConfig::brokerByID($GluePrefix . '_DBHost');
            if (is_object($DBHost)) {
                $DBHost = $DBHost->getKey('value');
            }
        }
        if (isset($arrConfigValues['DBPort'])) {
            $DBPort = $arrConfigValues['DBPort'];
        } else {
            $DBPort = Object_SecureConfig::brokerByID($GluePrefix . '_DBPort');
            if (is_object($DBPort)) {
                $DBPort = $DBPort->getKey('value');
            }
        }
        if (isset($arrConfigValues['DBUser'])) {
            $DBUser = $arrConfigValues['DBUser'];
        } else {
            $DBUser = Object_SecureConfig::brokerByID($GluePrefix . '_DBUser');
            if (is_object($DBUser)) {
                $DBUser = $DBUser->getKey('value');
            }
        }
        if (isset($arrConfigValues['DBPass'])) {
            $DBPass = $arrConfigValues['DBPass'];
        } else {
            $DBPass = Object_SecureConfig::brokerByID($GluePrefix . '_DBPass');
            if (is_object($DBPass)) {
                $DBPass = $DBPass->getKey('value');
            }
        }
        if (isset($arrConfigValues['DBBase'])) {
            $DBBase = $arrConfigValues['DBBase'];
        } else {
            $DBBase = Object_SecureConfig::brokerByID($GluePrefix . '_DBBase');
            if (is_object($DBBase)) {
                $DBBase = $DBBase->getKey('value');
            }
        }
        if ($DBType == null 
            || $DBHost == null 
            || ($DBType != 'sqlite' && $DBPort == null)
            || ($DBType != 'sqlite' && $DBUser == null)
            || ($DBType != 'sqlite' && $DBPass == null)
            || ($DBType != 'sqlite' && $DBBase == null)
        ) {
            $value = "\r\nDBType: $DBType (";
            if ($DBType == null) {
                $value .= "False";
            } else {
                $value .= "True";
            }
            $value .= ")\r\nDBHost: $DBHost (";
            if ($DBHost == null) {
                $value .= "False";
            } else {
                $value .= "True";
            }
            $value .= ")\r\nDBPort: $DBPort (";
            if ($DBPort == null) {
                $value .= "False";
            } else {
                $value .= "True";
            }
            $value .= ")\r\nDBUser: $DBUser (";
            if ($DBUser == null) {
                $value .= "False";
            } else {
                $value .= "True";
            }
            $value .= ")\r\nDBPass: $DBPass (";
            if ($DBPass == null) {
                $value .= "False";
            } else {
                $value .= "True";
            }
            $value .= ")\r\nDBBase: $DBBase (";
            if ($DBBase == null) {
                $value .= "False";
            } else {
                $value .= "True";
            }
            $value .= ")";
            throw new InvalidArgumentException("Insufficient detail to connect to Gammu Database : $value");
        }
        
        $this->strInterface = $GluePrefix;
        
        if ($DBType != 'sqlite') {
            $this->objDbGammu = new PDO($DBType . ':host=' . $DBHost . ';port=' . $DBPort .';dbname=' . $DBBase, $DBUser, $DBPass);
        } else {
            $this->objDbGammu = new PDO($DBType . ':' . $DBHost);
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
                        $strInterface = $this->strInterface . '-private_' . $message['RecipientID'];
                        $strSender = $message['SenderNumber'];
                        $textMessage = $text;
                        $intNativeID = $message['ID'];
                        Object_Input::import($strSender, $strInterface, $textMessage, $intNativeID);
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

                    $strInterface = $this->strInterface . '-private_' . $message['RecipientID'];
                    $strSender = $message['SenderNumber'];
                    $textMessage = $message['TextDecoded'];
                    $intNativeID = $message['ID'];
                    Object_Input::import($strSender, $strInterface, $textMessage, $intNativeID);
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

        $messages = Object_Output::brokerByColumnSearch('isActioned', 0);
        foreach ($messages as $message) {
            if (preg_match('/^([^-]+)/', $message->getKey('strInterface'), $matches) == 1) {
                if ($matches[1] != $this->strInterface) {
                    continue;
                }
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
                    'MultiPart' => null,
                    'DestinationNumber' => $message->getKey('strReceiver'),
                    'UDH' => null,
                    'TextDecoded' => null,
                    'Coding' => 'Default_No_Compression',
                    'SenderID' => null
                );

                // Adding the CreatorID means we can track which Glue sent which
                // message. Mostly, I'd expect to only see one Gammu Glue per 
                // event, but who knows! :)
                //
                // If we've got multiple sticks (modems), we also will need to 
                // know which is which... which is where the SenderID comes into
                // play.

                if (preg_match('/^([^-]+)-([^_]+)_(.*)$/', $message->getKey('strInterface'), $matches) == 1) {
                    $data['CreatorID'] .= '_' . $matches[1];
                    $data['SenderID'] = $matches[3];
                } elseif (preg_match('/^([^-]+)$/', $message->getKey('strInterface'), $matches) == 1) {
                    $data['CreatorID'] .= '_' . $matches[1];
                }

                // So, now we've got our defaults set up - let's check whether this
                // is actually more than 1 message long.
                $arrMessageChunks = str_split($message->getKey('textMessage'), 160);
                if (count($arrMessageChunks) == 1) {
                    $data['TextDecoded'] = $message->getKey('textMessage');
                    $qryInsert->execute($data);
                } else {
                    foreach ($arrMessageChunks as $intChunkID => $strChunk) {
                        $intChunkID++;
                        $uniq = str_pad(dechex(rand(0, 255)), 2, "0");
                        $size = str_pad(dechex(count($arrMessageChunks)), 2, "0");
                        $mNum = str_pad(dechex($intChunkID), 2, "0");
                        $data['UDH'] = '050003' . $uniq . $size . $mNum;
                        $data['TextDecoded'] = $strChunk;
                        if ($intChunkID == 1) {
                            $qryInsert->execute($data);
                            $data['ID'] = $this->objDbGammu->lastInsertId();
                        } else {
                            $data['SequencePosition'] = $intChunkID;
                            $qryInsertLarge->execute($data);
                        }
                    }
                }
                $message->setKey('isActioned', 1);
                $message->write();
                $this->objDaemon->setKey('intOutboundCounter', $this->objDaemon->getKey('intOutboundCounter') + 1);
                $this->objDaemon->setKey('lastUsedSuccessfully', date('Y-m-d H:i:s'));
                $this->objDaemon->write();
            } catch (Exception $e) {
                error_log('Error moving data from CFM2 Outbox to Gammu Outbox: ' . $e->getMessage());
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
        $return = array();
        foreach ($arrConfig as $key => $objConfig) {
            if (preg_match('/^Glue_Gammu-[^_]+$/', $objConfig->getKey('key'))) {
                $key = $objConfig->getKey('value');
                if (isset($arrConfig[$key . '_DBType']) 
                    && isset($arrConfig[$key . '_DBHost']) 
                    && isset($arrConfig[$key . '_DBPort']) 
                    && isset($arrConfig[$key . '_DBUser']) 
                    && isset($arrConfig[$key . '_DBPass']) 
                    && isset($arrConfig[$key . '_DBBase'])
                ) {
                    $return[] = new Glue_Gammu(array('GluePrefix' => $key));
                }
            }
        }
        return $return;
    }
}