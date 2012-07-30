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
        if (isset($arrConfigValues['db_prefix'])) {
            $db_prefix = $arrConfigValues['db_prefix'];
        } else {
            $db_prefix = 'Gammu';
        }
        
        if (isset($arrConfigValues['db_type'])) {
            $db_type = $arrConfigValues['db_type'];
        } else {
            $db_type = Object_SecureConfig::brokerByID($db_prefix . 'DBType', 'mysql')->getKey('value');
        }
        if (isset($arrConfigValues['db_host'])) {
            $db_host = $arrConfigValues['db_host'];
        } else {
            $db_host = Object_SecureConfig::brokerByID($db_prefix . 'DBHost', false)->getKey('value');
        }
        if (isset($arrConfigValues['db_port'])) {
            $db_port = $arrConfigValues['db_port'];
        } else {
            $db_port = Object_SecureConfig::brokerByID($db_prefix . 'DBPort', false)->getKey('value');
        }
        if (isset($arrConfigValues['db_user'])) {
            $db_user = $arrConfigValues['db_user'];
        } else {
            $db_user = Object_SecureConfig::brokerByID($db_prefix . 'DBUser', false)->getKey('value');
        }
        if (isset($arrConfigValues['db_pass'])) {
            $db_pass = $arrConfigValues['db_pass'];
        } else {
            $db_pass = Object_SecureConfig::brokerByID($db_prefix . 'DBPass', false)->getKey('value');
        }
        if (isset($arrConfigValues['db_base'])) {
            $db_base = $arrConfigValues['db_base'];
        } else {
            $db_base = Object_SecureConfig::brokerByID($db_prefix . 'DBBase', false)->getKey('value');
        }
        if ($db_type == false 
            || $db_host == false 
            || ($db_type != 'sqlite' && $db_port == false)
            || ($db_type != 'sqlite' && $db_user == false)
            || ($db_type != 'sqlite' && $db_pass == false)
            || ($db_type != 'sqlite' && $db_base == false)
        ) {
            throw new InvalidArgumentException("Insufficient detail to connect to Gammu Database");
        }
        
        $this->strInterface = $db_prefix;
        
        if ($db_type != 'sqlite') {
            $this->sms = new PDO($db_type . '://' . $db_host . ':' . $db_port . '/' . $db_base, $db_user, $db_pass);
        } else {
            $this->sms = new PDO($db_type . '://' . $db_host);
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
                    }
                } else {
                    $strInterface = $this->strInterface . '-private_' . $message['RecipientID'];
                    $strSender = $message['SenderNumber'];
                    $textMessage = $message['TextDecoded'];
                    $intNativeID = $message['ID'];
                    Object_Input::import($strSender, $strInterface, $textMessage, $intNativeID);
                    $sqlUpdateInbox = "UPDATE inbox SET processed = 'true' WHERE ID = ?";
                    $qryUpdateInbox = $this->sms->prepare($sqlUpdateInbox);
                    $qryUpdateInbox->execute(array($message['ID']));
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
        $since_timestamp = null;
        $since_id = null;
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
                        if ($chunkid == 0) {
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
            } catch (Exception $e) {
                error_log('Error moving data from CFM2 Outbox to Gammu Outbox: ' . $e->getMessage());
            }
        }
    }
}
