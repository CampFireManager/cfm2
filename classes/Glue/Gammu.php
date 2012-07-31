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
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */
/**
 * This glue is used to broker all inter-Gammu conversations.
 *
 * @category Glue_Gammu
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Glue_Gammu implements Interface_Glue
{
    protected $sms = null;
    protected $strInterface = null;
    protected $objDaemon = null;

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
            $DBType = Object_SecureConfig::brokerByID($GluePrefix . '_DBType', 'mysql')->getKey('value');
        }
        if (isset($arrConfigValues['DBHost'])) {
            $DBHost = $arrConfigValues['DBHost'];
        } else {
            $DBHost = Object_SecureConfig::brokerByID($GluePrefix . '_DBHost', false)->getKey('value');
        }
        if (isset($arrConfigValues['DBPort'])) {
            $DBPort = $arrConfigValues['DBPort'];
        } else {
            $DBPort = Object_SecureConfig::brokerByID($GluePrefix . '_DBPort', false)->getKey('value');
        }
        if (isset($arrConfigValues['DBUser'])) {
            $DBUser = $arrConfigValues['DBUser'];
        } else {
            $DBUser = Object_SecureConfig::brokerByID($GluePrefix . '_DBUser', false)->getKey('value');
        }
        if (isset($arrConfigValues['DBPass'])) {
            $DBPass = $arrConfigValues['DBPass'];
        } else {
            $DBPass = Object_SecureConfig::brokerByID($GluePrefix . '_DBPass', false)->getKey('value');
        }
        if (isset($arrConfigValues['DBBase'])) {
            $DBBase = $arrConfigValues['DBBase'];
        } else {
            $DBBase = Object_SecureConfig::brokerByID($GluePrefix . '_DBBase', false)->getKey('value');
        }
        if ($DBType == false 
            || $DBHost == false 
            || ($DBType != 'sqlite' && $DBPort == false)
            || ($DBType != 'sqlite' && $DBUser == false)
            || ($DBType != 'sqlite' && $DBPass == false)
            || ($DBType != 'sqlite' && $DBBase == false)
        ) {
            throw new InvalidArgumentException("Insufficient detail to connect to Gammu Database");
        }
        
        $this->strInterface = $GluePrefix;
        
        if ($DBType != 'sqlite') {
            $this->sms = new PDO($DBType . '://' . $DBHost . ':' . $DBPort . '/' . $DBBase, $DBUser, $DBPass);
        } else {
            $this->sms = new PDO($DBType . '://' . $DBHost);
        }
        
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
        $sql = "SELECT max(TimeOut) AS TimeOut, max(Signal) AS Sigal FROM phones";
        $query = $this->sms->prepare($sql);
        $query->execute(array());
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $this->objDaemon->setKey('lastUsedSuccessfully', $result['TimeOut']);
        $this->objDaemon->setKey('intScope', $result['Signal']);
        $this->objDaemon->write();

        $sql = "SELECT ID, SenderNumber, UDH, TextDecoded, RecipientID FROM inbox WHERE processed = 'false'";
        $query = $this->sms->prepare($sql);
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
                        $qryUpdateInbox = $this->sms->prepare($sqlUpdateInbox);
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
                    $qryUpdateInbox = $this->sms->prepare($sqlUpdateInbox);
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
        $qryInsert = $this->sms->prepare($sqlInsert);
        $sqlInsertLarge = 'INSERT INTO outbox_multipart '
            . '(SequencePosition, UDH, TextDecoded, ID, Coding)'
            . ' VALUES '
            . '(:SequencePosition, :UDH, :TextDecoded, :ID, :Coding);';
        $qryInsertLarge = $this->sms->prepare($sqlInsertLarge);

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
                            $data['ID'] = $this->sms->lastInsertId();
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
        $arrConfig = Container_SecureConfig::brokerAll();
        $return = array();
        foreach ($arrConfig as $key => $objConfig) {
            if (preg_match('/^Glue_Gammu-[^_]+/', $key)) {
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