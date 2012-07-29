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
 * This glue is used to broker all inter-Twitter conversations. It is mostly
 * based on the TwitterManager class, created by Jack Wearden and processes
 * created in (but not derived from) the example files from the upstream OAuth
 * library.
 * 
 * This Glue replaces the TwitterManager class. Please see the following file if 
 * you want to see what Jack wrote. There's nothing wrong with it, it just 
 * doesn't match how I need things to fit together.
 * 
 * https://github.com/JonTheNiceGuy/cfm2/blob/3c7710ba8a7913cd8d4754ceedabfc41bca51365/classes/Plugin/Twitter/ExternalLibraries/TwitterManager.php
 *
 * @category Glue_Twitter
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @author   Jack Wearden <jack.weirdy@googlemail.com>
 * @author   themattharris <https://twitter.com/intent/follow?screen_name=themattharris>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Glue_TwitterAPI implements Interface_Glue
{
    protected $oAuth = null;

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
            $db_prefix = 'Twitter';
        }
        if (isset($arrConfigValues['consumer_prefix'])) {
            $consumer_prefix = $arrConfigValues['consumer_prefix'];
        } else {
            $consumer_prefix = $db_prefix;
        }
        
        if (isset($arrConfigValues['consumer_key'])) {
            $cfgCK = $arrConfigValues['consumer_key'];
        } else {
            $cfgCK = Object_SecureConfig::brokerByID($consumer_prefix . 'ConsumerKey', false)->getKey('value');
        }
        if (isset($arrConfigValues['consumer_secret'])) {
            $cfgCS = $arrConfigValues['consumer_secret'];
        } else {
            $cfgCS = Object_SecureConfig::brokerByID($consumer_prefix . 'ConsumerSecret', false)->getKey('value');
        }
        if (isset($arrConfigValues['user_key'])) {
            $cfgUK = $arrConfigValues['user_secret'];
        } else {
            $cfgUK = Object_SecureConfig::brokerByID($db_prefix . 'UserKey', false)->getKey('value');
        }
        if (isset($arrConfigValues['user_secret'])) {
            $cfgUS = $arrConfigValues['user_secret'];
        } else {
            $cfgUS = Object_SecureConfig::brokerByID($db_prefix . 'UserSecret', false)->getKey('value');
        }
        if (isset($arrConfigValues['api_host'])) {
            $cfgAPI = $arrConfigValues['api_host'];
        } else {
            $cfgAPI = Object_SecureConfig::brokerByID($db_prefix . 'APIHost', 'api.twitter.com')->getKey('value');
        }
        if ($cfgCK == false) {
            throw new InvalidArgumentException("No Consumer Key");
        }
        if ($cfgCS == false) {
            throw new InvalidArgumentException("No Consumer Secret");
        }
        if ($cfgUK == false) {
            throw new InvalidArgumentException("No User Token");
        }
        if ($cfgUS == false) {
            throw new InvalidArgumentException("No User Secret");
        }
        
        $libTwitterHelper = Base_ExternalLibraryLoader::getVersion("TwitterHelper");
        if ($libTwitterHelper == false) {
            return false;
        }
        include $libTwitterHelper . '/tmhOAuth.php';
        include $libTwitterHelper . '/tmhUtilities.php';

        $this->oAuth = new tmhOAuth(
            array(
                'consumer_key'    => $cfgCK,
                'consumer_secret' => $cfgCS,
                'user_token'      => $cfgUK,
                'user_secret'     => $cfgUS,
                'host'            => $cfgAPI
            )
        );
    }

    /**
     * This function calls the service, and retrieves a list of messages
     * 
     * @param timestamp $since_timestamp The timestamp of the last successful
     * retrieval of a message
     * @param integer   $since_id        The last ID of a successfully received
     * message.
     * 
     * @return array
     */
    public function read_private($since_timestamp = null, $since_id = null)
    {
        if ($this->oAuth->config['use_ssl']) {
            $api_path = 'https://';
        } else {
            $api_path = 'http://';
        }
        $api_path .= $this->oAuth->config['host'];
        $args = array();
        if ($since_id != null && $since_id != '') {
            $args['since_id'] = $since_id;
        }
        
        $this->oAuth->request('GET', $api_path . '/1/direct_messages.json', $args, true);
        if ($this->oAuth->response['code'] == 200) {
            $data = json_decode($this->oAuth->response['response'], true);
            foreach ($data['results'] as $tweet) {
                $return[] = array('strSender' => $tweet['from_user'], 'textMessage' => $tweet['text'], 'intNativeID' => 'D' . $tweet['id_str']);
            }
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
     * @param timestamp $since_timestamp The timestamp of the last successful
     * retrieval of a message
     * @param integer   $since_id        The last ID of a successfully received
     * message.
     * 
     * @return array
     */
    public function read_public($since_timestamp = null, $since_id = null)
    {
        if ($this->oAuth->config['use_ssl']) {
            $api_path = 'https://';
        } else {
            $api_path = 'http://';
        }
        $api_path .= $this->oAuth->config['host'];
        $args = array();
        if ($since_id != null && $since_id != '') {
            $args['since_id'] = $since_id;
        }
        
        $this->oAuth->request('GET', $api_path . '/1/statuses/mentions.json', $args, true);
        if ($this->oAuth->response['code'] == 200) {
            $data = json_decode($this->oAuth->response['response'], true);
            foreach ($data['results'] as $tweet) {
                $return[] = array('strSender' => $tweet['from_user'], 'textMessage' => $tweet['text'], 'intNativeID' => $tweet['id_str']);
            }
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
     * @param string $message     The message to send
     * @param string $destination The destination (if required) to send it to.
     * 
     * @return boolean
     */
    public function send($message, $destination = null)
    {
        if ($destination != null && $destination != '') {
            $status = $this->objTwitterConnection->request('POST', $this->objTwitterConnection->url('1/direct_messages/new'), array('text' => $message, 'screen_name' => $destination));
        } else {
            $status = $this->objTwitterConnection->request('POST', $this->objTwitterConnection->url('1/statuses/update'), array('status' => $message));
        }
        if ($status == 200) {
            return true;
        } else {
            $data = htmlentities($this->oAuth->response['response']);
            error_log('There was an error in the OAuth library sending. ' . print_r($data, true));
            throw new HttpResponseException('Error Sending using OAuth');
        }
    }
}