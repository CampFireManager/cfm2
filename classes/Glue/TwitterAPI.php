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
 * This glue is used to broker all inter-Twitter conversations. It is mostly
 * based on the TwitterManager class, created by Jack Wearden and processes
 * created in (but not derived from) the example files from the upstream OAuth
 * library.
 * 
 * This Glue replaces the TwitterManager class. Please see the following file if 
 * you want to see what Jack wrote. There's nothing wrong with it, it just 
 * doesn't match how I need things to fit together.
 * 
 * https://github.com/CampFireManager/cfm2/blob/3c7710ba8a7913cd8d4754ceedabfc41bca51365/classes/Plugin/Twitter/ExternalLibraries/TwitterManager.php
 *
 * @category Glue_Twitter
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @author   Jack Wearden <jack.weirdy@googlemail.com>
 * @author   themattharris <https://twitter.com/intent/follow?screen_name=themattharris>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */

class Glue_TwitterAPI implements Interface_Glue
{
    protected $oAuth = null;
    protected $strInterface = 'Twitter';
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
        return $this->strInterface;
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
     * failing that, details to read from the SecureConfig table.
     * 
     * @return Glue_TwitterAPI
     */
    public function __construct($arrConfigValues = array())
    {
        if (!isset($arrConfigValues['ConsumerKey']) ||
            !isset($arrConfigValues['ConsumerSecret']) ||
            !isset($arrConfigValues['UserToken']) ||
            !isset($arrConfigValues['UserSecret'])
        ) {
            throw new InvalidArgumentException('Missing Twitter configuration value');
        }
        
        $libTwitterHelper = Base_ExternalLibraryLoader::loadLibrary("TwitterHelper");
        if ($libTwitterHelper == false) {
            throw new LogicException("Failed to load Twitter Helper");
        }
        
        include_once $libTwitterHelper . '/tmhOAuth.php';
        include_once $libTwitterHelper . '/tmhUtilities.php';

        $this->oAuth = new tmhOAuth(
            array(
                'consumer_key'    => $arrConfigValues['ConsumerKey'],
                'consumer_secret' => $arrConfigValues['ConsumerSecret'],
                'user_token'      => $arrConfigValues['UserToken'],
                'user_secret'     => $arrConfigValues['UserSecret']
            )
        );
        
        $this->objDaemon = end(Object_Daemon::brokerByColumnSearch('strDaemon', $this->strInterface));
        if ($this->objDaemon == false) {
            $this->objDaemon = new Object_Daemon();
            $this->objDaemon->setKey('strDaemon', $this->strInterface);
            $this->objDaemon->setKey('intInboundCounter', 0);
            $this->objDaemon->setKey('intOutboundCounter', 0);
            $this->objDaemon->setKey('intUniqueCounter', 0);
            $this->objDaemon->setKey('lastUsedSuccessfully', '1970-01-01 00:00:00');
            $this->objDaemon->create();
        }
    }

    /**
     * This function calls the service, and retrieves a list of private messages
     * 
     * @return void
     */
    public function read_private()
    {        
        $this->oAuth->request('GET', 'https://api.twitter.com/1/account/rate_limit_status.json', array(), true);
        if ($this->oAuth->response['code'] == 200) {
            $data = json_decode($this->oAuth->response['response'], true);
            $this->objDaemon->setKey('intScope', $data['remaining_hits']);
            $this->objDaemon->write();
        }
        
        $lastmessage = Object_Input::brokerByColumnSearch('strInterface', $this->strInterface . '-private', false, false, 1, 'DESC');
        if ($lastmessage == false) {
            $args['since_id'] = 0;
        } else {
            $lastmessage = end($lastmessage);
            $args['since_id'] = $lastmessage->getKey('intNativeID');
        }
        
        $this->oAuth->request('GET', 'https://api.twitter.com/1/direct_messages.json', $args, true);
        $return = 0;
        if ($this->oAuth->response['code'] == 200) {
            $data = json_decode($this->oAuth->response['response'], true);
            if (is_array($data)) {
                foreach ($data as $tweet) {
                    if (count(Object_Input::brokerByColumnSearch('strSender', $tweet['sender_screen_name'], false, false, 1, 'DESC')) == 0) {
                        $this->objDaemon->setKey('intUniqueCounter', $this->objDaemon->getKey('intUniqueCounter') + 1);
                    }
                    $return = new Object_Input();
                    $return->setKey('strInterface', $this->strInterface . '-private');
                    $return->setKey('strSender', $tweet['sender_screen_name']);
                    $return->setKey('textMessage', $tweet['text']);
                    $return->setKey('intNativeID', $tweet['id_str']);
                    $return->setKey('isActioned', 0);
                    $return->create();
                    $this->objDaemon->setKey('intInboundCounter', $this->objDaemon->getKey('intInboundCounter') + 1);
                }
            }
            $this->objDaemon->setKey('lastUsedSuccessfully', date('Y-m-d H:i:s'));
            $return++;
            $this->objDaemon->write();                
        } else {
            $data = htmlentities($this->oAuth->response['response']);
            error_log('There was an error in the OAuth library fetching direct messages. ' . print_r($data, true));
            throw new HttpResponseException('Error fetching OAuth Direct Messages');
        }
        return $return;
    }

    /**
     * This function calls the service, and retrieves a list of public messages
     * 
     * @return void
     */
    public function read_public()
    {
        $this->oAuth->request('GET', 'https://api.twitter.com/1/account/rate_limit_status.json', array(), true);
        if ($this->oAuth->response['code'] == 200) {
            $data = json_decode($this->oAuth->response['response'], true);
            $this->objDaemon->setKey('intScope', $data['remaining_hits']);
            $this->objDaemon->write();
        }

        $lastmessage = Object_Input::brokerByColumnSearch('strInterface', $this->strInterface . '-public', false, false, 1, 'DESC');
        if ($lastmessage == false) {
            $args['since_id'] = 1;
        } else {
            $lastmessage = end($lastmessage);
            $args['since_id'] = $lastmessage->getKey('intNativeID');
        }
        
        $return = 0;
        
        $this->oAuth->request('GET', 'https://api.twitter.com/1/statuses/mentions.json', $args, true);
        if ($this->oAuth->response['code'] == 200) {
            $data = json_decode($this->oAuth->response['response'], true);
            if (isset($data)) {
                foreach ($data as $tweet) {
                    if (count(Object_Input::brokerByColumnSearch('strSender', $tweet['user']['screen_name'], false, false, 1, 'DESC')) == 0) {
                        $this->objDaemon->setKey('intUniqueCounter', $this->objDaemon->getKey('intUniqueCounter') + 1);
                    }
                    $return = new Object_Input();
                    $return->setKey('strInterface', $this->strInterface . '-public');
                    $return->setKey('strSender', $tweet['user']['screen_name']);
                    $return->setKey('textMessage', $tweet['text']);
                    $return->setKey('intNativeID', $tweet['id_str']);
                    $return->setKey('isActioned', 0);
                    $return->create();
                    $this->objDaemon->setKey('intInboundCounter', $this->objDaemon->getKey('intInboundCounter') + 1);
                    $return++;
                }                
            }
            $this->objDaemon->setKey('lastUsedSuccessfully', date('Y-m-d H:i:s'));
            $this->objDaemon->write();
        } else {
            $data = htmlentities($this->oAuth->response['response']);
            error_log('There was an error in the OAuth library fetching mentions. ' . print_r($data, true));
            throw new HttpResponseException('Error fetching OAuth Mentions');
        }
        return $return;
    }

    /**
     * This function calls the service, sending a message.
     * 
     * @return void
     */
    public function send()
    {
        $this->oAuth->request('GET', 'https://api.twitter.com/1/account/rate_limit_status.json', array(), true);
        if ($this->oAuth->response['code'] == 200) {
            $data = json_decode($this->oAuth->response['response'], true);
            $this->objDaemon->setKey('intScope', $data['remaining_hits']);
            $this->objDaemon->write();
        }

        $messages = Object_Output::brokerByColumnSearch('isActioned', 0);
        $return = 0;
        foreach ($messages as $message) {
            if ($message->getKey('strInterface') == $this->strInterface) {
                // Skip on!
            } elseif (preg_match('/^([^-]+)/', $message->getKey('strInterface'), $matches) == 1) {
                if ($matches[1] != $this->strInterface) {
                    continue;
                }
            }
            if ($message->getKey('strReceiver') != null && $message->getKey('strReceiver') != '') {
                $status = $this->oAuth->request('POST', $this->oAuth->url('1/direct_messages/new'), array('text' => substr($message->getKey('textMessage'), 0, 158 - strlen($message->getKey('strReceiver'))), 'screen_name' => $message->getKey('strReceiver')));
            } else {
                $status = $this->oAuth->request('POST', $this->oAuth->url('1/statuses/update'), array('status' => substr($message->getKey('textMessage'), 0, 160)));
            }
            if ($status == 200) {
                $message->setKey('isActioned', 1);
                $message->write();
                $this->objDaemon->setKey('intOutboundCounter', $this->objDaemon->getKey('intOutboundCounter') + 1);
                $return++;
                $this->objDaemon->setKey('lastUsedSuccessfully', date('Y-m-d H:i:s'));
                $this->objDaemon->write();
            } else {
                $message->setKey('strError', $status . ': ' . $this->oAuth->response['response']);
                $message->write();
            }
        }
        return $return;
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
        if (isset($arrConfig['Twitter_ConsumerKey'])) {
            $config['ConsumerKey'] = $arrConfig['Twitter_ConsumerKey'];
        }
        if (isset($arrConfig['Twitter_ConsumerSecret'])) {
            $config['ConsumerSecret'] = $arrConfig['Twitter_ConsumerSecret'];
        }
        if (isset($arrConfig['Twitter_UserToken'])) {
            $config['UserToken'] = $arrConfig['Twitter_UserToken'];
        }
        if (isset($arrConfig['Twitter_UserSecret'])) {
            $config['UserSecret'] = $arrConfig['Twitter_UserSecret'];
        }
        if (count($config) > 0) {
            return array(new Glue_TwitterAPI($config));
        } else {
            return array();
        }
    }

    /**
     * This function follows back any followers, or authorizes connections to
     * this glue connection.
     * 
     * @return void
     */
    public function follow_followers()
    {
        $this->oAuth->request('GET', 'https://api.twitter.com/1/account/rate_limit_status.json', array(), true);
        if ($this->oAuth->response['code'] == 200) {
            $data = json_decode($this->oAuth->response['response'], true);
            $this->objDaemon->setKey('intScope', $data['remaining_hits']);
            $this->objDaemon->write();
        }

        $this->oAuth->request('GET', 'https://api.twitter.com/1/account/verify_credentials.json', array(), true);
        if ($this->oAuth->response['code'] == 200) {
            $data = json_decode($this->oAuth->response['response'], true);
            $user_id = $data['id'];
        }
        
        $previous_cursor = -2;
        $cursor = -1;
        $friends = array();
        while($cursor > $previous_cursor) {
            $this->oAuth->request('GET', 'https://api.twitter.com/1/friends/ids.json', array('user_id' => $user_id, 'cursor' => $cursor), true);
            if ($this->oAuth->response['code'] == 200) {
                $data = json_decode($this->oAuth->response['response'], true);
                $previous_cursor = $cursor;
                $cursor = $data['next_cursor'];
                foreach ($data['ids'] as $friend_id) {
                    $friends[$friend_id] = true;
                }
            }
        }

        $previous_cursor = -2;
        $cursor = -1;
        $followers = array();
        $return = 0;
        while($cursor > $previous_cursor) {
            $this->oAuth->request('GET', 'https://api.twitter.com/1/followers/ids.json', array('user_id' => $user_id, 'cursor' => $cursor), true);
            if ($this->oAuth->response['code'] == 200) {
                $data = json_decode($this->oAuth->response['response'], true);
                $previous_cursor = $cursor;
                $cursor = $data['next_cursor'];
                foreach ($data['ids'] as $follower_id) {
                    $followers[$follower_id] = true;
                }
            }
        }
        
        foreach ($followers as $follower_id => $dummy) {
            $dummy = null;
            if (!isset($friends[$follower_id])) {
                $this->oAuth->request('POST', 'https://api.twitter.com/1/friendships/create.json', array('user_id' => $follower_id), true);
                $return++;
            }
        }
        
        $this->oAuth->request('GET', 'https://api.twitter.com/1/account/rate_limit_status.json', array(), true);
        if ($this->oAuth->response['code'] == 200) {
            $data = json_decode($this->oAuth->response['response'], true);
            $this->objDaemon->setKey('intScope', $data['remaining_hits']);
            $this->objDaemon->write();
        }
        return $return;
    }
}