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
 * This class caches all the responses from the database searches.
 *
 * @category Plugin_Twitter
 * @package  CampFireManager2
 * @author   Jack Wearden <jack.weirdy@googlemail.com>
 * @license  http://www.example.com To Be Defined - suspect AGPL, but maybe Apache?
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class TwitterManager
{
    private static $objTwitterConnection = null;
    protected $lastStatus = null;
    protected $lastMessage = null;

    /**
     * Load this library, using the consumer key, consumer secret, user key and user secret.
     * 
     * @param string $strConsumerKey    The OAuth Consumer Key, supplied by Twitter
     * @param string $strConsumerSecret The OAuth Consumer Secret, supplied by twitter
     * @param string $strUserKey        The OAuth User Key, supplied by twitter when a user approves this application to be used on their behalf
     * @param string $strUserSecret     The OAuth User Secret, supplied by twitter when approved.
     *
     * @return void 
     */
    public function __construct($strConsumerKey = null, $strConsumerSecret = null, $strUserKey = null, $strUserSecret = null)
    {
        $libTwitterHelper = Base_ExternalLibraryLoader::getVersion("TwitterHelper");
        if ($libTwitterHelper == false) {
            return false;
        }
        include $libTwitterHelper . '/tmhOAuth.php';
        include $libTwitterHelper . '/tmhUtilities.php';

        $this->objTwitterConnection = new tmhOAuth(
            array(
                'consumer_key'      => $strConsumerKey,
                'consumer_secret'   => $strConsumerSecret,
                'user_token'        => $strUserKey,
                'user_secret'       => $strUserSecret
            )
        );
    }

    /**
     * This library posts a message, using the previously generated twitter connection.
     *
     * @param string $tweet The message to send to twitter
     * 
     * @return boolean 
     */
    public function post($tweet)
    {
        $this->lastStatus = $this->objTwitterConnection->request('POST', $this->objTwitterConnection->url('1/statuses/update'), array('status' => $tweet));
        if ($this->lastStatus == 200) {
            $this->lastMessage = null;
            return true;
        } else {
            $this->lastMessage = $this->objTwitterConnection->response['response'];
            return false;
        }
    }
    
    /**
     * This function returns the last generated error code.
     *
     * @return integer 
     */
    public function errno()
    {
        return $this->lastStatus;
    }
    
    /**
     * This function returns the last generated response code.
     *
     * @return type 
     */
    public function error()
    {
        return $this->lastMessage;
    }
}
