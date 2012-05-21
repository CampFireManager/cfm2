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
 * This helper library enables OpenID authentication, by chaining together the
 * OpenID libraries, and making appropriate requests of them.
 *
 * @category Base_GeneralFunctions
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */


class Base_OpenID
{
    protected static $_self = null;

    protected $_nickname     = FALSE;
    protected $_email        = TRUE;
    protected $_realname     = FALSE;
    protected $_language     = FALSE;
    protected $_dateofbirth  = FALSE;
    protected $_gender       = FALSE;
    protected $_postcode     = FALSE;
    protected $_country      = FALSE;
    protected $_timezone     = FALSE;

    // There are more AX attributes we can ask for, but most will not be supplied. Also, these others
    // don't correspond with the SReg attributes that can be requested.
    // For details see http://www.axschema.org/types/ and http://www.axschema.org/types/experimental/

    // If you know about another sreg or ax attribute you want to request, specify them in here, using
    // the templates below to add them in.

    protected $_attributeAx = array();
    protected $_attributeSReg = array();

    protected static $_consumer = null;

    /**
     * An internal function to make this a singleton
     *
     * @return object This class by itself.
     */
    private static function getHandler()
    {
        if (self::$_self == null) {
            self::$_self = new self();
        }
        return self::$_self;
    }


    /**
     * Load appropriate libraries and set certain variables
     *
     * @return void
     */
    function __construct()
    {
        // start session (needed for YADIS)
        Base_GeneralFunctions::startSession();

        $libOpenID = Base_ExternalLibraryLoader::loadLibrary("PHP_OpenID");
        if ($libOpenID == false) {
            throw new BadMethodCallException("Failed to load OpenID");
        }
        set_include_path(get_include_path() . PATH_SEPARATOR . $libOpenID);

        include_once "Auth/OpenID/Consumer.php";
        include_once "Auth/OpenID/FileStore.php";
        include_once "Auth/OpenID/SReg.php";
        include_once "Auth/OpenID/AX.php";

        // create file storage area for OpenID data
        $store = new Auth_OpenID_FileStore(Container_Config::brokerByID('TemporaryFiles', '/tmp')->getKey('value') . '/OPENID_STORE');

        // create OpenID consumer
        $this->consumer = new Auth_OpenID_Consumer($store);
    }


    /**
     * Request authentication from the $id
     *
     * @param string $strOpenID The requested OpenID authentication
     * @param string $base      The path where these functions are triggered from
     * @param string $success   The path to return to after authentication is completed successfully
     * @param string $fail      The path to return to after authentication is completed unsuccessfully or fails
     *
     * @return void
     */
    public static function request(
        $strOpenID = '', 
        $base = '', 
        $success = '', 
        $fail = ''
    )
    {
        $handler = self::getHandler();
        $auth = $handler->consumer->begin($strOpenID);
        if (!$auth) {
            $_SESSION['OPENID_AUTH'] = false;
            $_SESSION['OPENID_FAILED_REASON'] = 0;
            header("Location: $fail");
        }

        $_SESSION['OPENID_SUCCESS'] = $success;
        $_SESSION['OPENID_FAILED'] = $fail;

        if (isset($handler->_nickname) and $handler->_nickname == TRUE) {
            $handler->_attributeAx[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/friendly', 1, 1, 'friendly');
            $handler->_attributeSReg[] = 'nickname';
        }

        if (isset($handler->_email) and $handler->_email == TRUE) {
            $handler->_attributeAx[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/contact/email', 1, 1, 'email');
            $handler->_attributeSReg[] = 'email';
        }
        if (isset($handler->_realname) and $handler->_realname == TRUE) {
            $handler->_attributeAx[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson', 1, 1, 'fullname');
            // Google doesn't actually return a response to fullname, but will return the first and last name.
            // http://code.google.com/apis/accounts/docs/OpenID.html#Parameters
            // Just to be sure we don't miss anything, we'll request the lot.
            $handler->_attributeAx[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/prefix', 1, 1, 'prefix');
            $handler->_attributeAx[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/first', 1, 1, 'firstname');
            $handler->_attributeAx[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/middle', 1, 1, 'middlename');
            $handler->_attributeAx[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/last', 1, 1, 'lastname');
            $handler->_attributeAx[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/suffix', 1, 1, 'suffix');
            $handler->_attributeSReg[] = 'fullname';
        }
        if (isset($handler->_dateofbirth) and $handler->_dateofbirth == TRUE) {
            $handler->_attributeAx[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/birthDate', 1, 1, 'dob');
            $handler->_attributeSReg[] = 'dob';
        }
        if (isset($handler->_gender) and $handler->_gender == TRUE) {
            $handler->_attributeAx[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/person/gender', 1, 1, 'gender');
            $handler->_attributeSReg[] = 'gender';
        }
        if (isset($handler->_postcode) and $handler->_postcode == TRUE) {
            $handler->_attributeAx[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/contact/postalCode/home', 1, 1, 'postcode');
            $handler->_attributeSReg[] = 'postcode';
        }
        if (isset($handler->_country) and $handler->_country == TRUE) {
            $handler->_attributeAx[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/contact/country/home', 1, 1, 'country');
            $handler->_attributeSReg[] = 'country';
        }
        if (isset($handler->_language) and $handler->_language == TRUE) {
            $handler->_attributeAx[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/pref/language', 1, 1, 'language');
            $handler->_attributeSReg[] = 'language';
        }
        if (isset($handler->_timezone) and $handler->_timezone == TRUE) {
            $handler->_attributeAx[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/pref/timezone', 1, 1, 'timezone');
            $handler->_attributeSReg[] = 'timezone';
        }

        // Add AX fetch request to authentication request
        $OpenID_AX = new Auth_OpenID_AX_FetchRequest;
        foreach ($handler->_attributeAx as $attr) {
            $OpenID_AX->add($attr);
        }
        $auth->addExtension($OpenID_AX);

        // Add SReg attributes to authentication request
        $sreg_request = Auth_OpenID_SRegRequest::build(array(), $handler->_attributeSReg);
        if ($sreg_request) {
            $auth->addExtension($sreg_request);
        }

        // redirect to OpenID provider for authentication
        $url = $auth->redirectURL($base, $base . '?return');
        header('Location: ' . $url);
    }


    /**
     * Act on the response from the OpenID Provider. Then redirect back to the completed authentication path.
     *
     * @param string $base The path where these functions are triggered from
     *
     * @return void
     */
    public static function response($base = '')
    {
        $handler = self::getHandler();
        $response = $handler->consumer->complete($base . '?return');

        if ($response->status == Auth_OpenID_SUCCESS) {
            // Get registration informations
            $sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
            $ax_resp = Auth_OpenID_AX_FetchResponse::fromSuccessResponse($response);

            $arr_auth = get_object_vars($response);
            $endpoint = get_object_vars($arr_auth['endpoint']);

            $openid_url = $endpoint['claimed_id'];

            $name_prefix = '';
            $name_first = '';
            $name_middle = '';
            $name_last = '';
            $name_suffix = '';
            $name_full = '';
            $name_alias = '';
            $email = '';
            $language = '';
            $dateofbirth = '';
            $gender = '';
            $postcode = '';
            $country = '';
            $timezone = '';
            if (isset($sreg_resp) and is_object($sreg_resp)) {
                $arr_sreg_resp = get_object_vars($sreg_resp);
                $arr_sreg_data = $arr_sreg_resp['data'];
                if (isset($arr_sreg_data) and is_array($arr_sreg_data) and count($arr_sreg_data) > 0) {
                    if (isset($arr_sreg_data['fullname'])) {
                        $name_full = $arr_sreg_data['fullname'];
                    }
                    if (isset($arr_sreg_data['nickname'])) {
                        $name_alias = $arr_sreg_data['nickname'];
                    }
                    if (isset($arr_sreg_data['email'])) {
                        $email = $arr_sreg_data['email'];
                    }
                    if (isset($arr_sreg_data['language'])) {
                        $language = $arr_sreg_data['language'];
                    }
                    if (isset($arr_sreg_data['dob'])) {
                        $dateofbirth = $arr_sreg_data['dob'];
                    }
                    if (isset($arr_sreg_data['gender'])) {
                        $gender = $arr_sreg_data['gender'];
                    }
                    if (isset($arr_sreg_data['postcode'])) {
                        $postcode = $arr_sreg_data['postcode'];
                    }
                    if (isset($arr_sreg_data['country'])) {
                        $country = $arr_sreg_data['country'];
                    }
                    if (isset($arr_sreg_data['timezone'])) {
                        $timezone = $arr_sreg_data['timezone'];
                    }
                }
            }
            if (isset($ax_resp) and is_object($ax_resp)) {
                $arr_ax_resp = get_object_vars($ax_resp);
                $arr_ax_data = $arr_ax_resp['data'];
                if (isset($arr_ax_data["http://axschema.org/namePerson/prefix"]) and count($arr_ax_data["http://axschema.org/namePerson/prefix"])>0) {
                    $name_prefix = $arr_ax_data["http://axschema.org/namePerson/prefix"][0];
                }
                if (isset($arr_ax_data["http://axschema.org/namePerson/first"]) and count($arr_ax_data["http://axschema.org/namePerson/first"])>0) {
                    $name_first = $arr_ax_data["http://axschema.org/namePerson/first"][0];
                }
                if (isset($arr_ax_data["http://axschema.org/namePerson/middle"]) and count($arr_ax_data["http://axschema.org/namePerson/middle"])>0) {
                    $name_middle = $arr_ax_data["http://axschema.org/namePerson/middle"][0];
                }
                if (isset($arr_ax_data["http://axschema.org/namePerson/last"]) and count($arr_ax_data["http://axschema.org/namePerson/last"])>0) {
                    $name_last = $arr_ax_data["http://axschema.org/namePerson/last"][0];
                }
                if (isset($arr_ax_data["http://axschema.org/namePerson/suffix"]) and count($arr_ax_data["http://axschema.org/namePerson/suffix"])>0) {
                    $name_suffix = $arr_ax_data["http://axschema.org/namePerson/suffix"][0];
                }
                if (isset($arr_ax_data["http://axschema.org/namePerson"]) and count($arr_ax_data["http://axschema.org/namePerson"])>0) {
                    $name_full = $arr_ax_data["http://axschema.org/namePerson"][0];
                }
                if (isset($arr_ax_data["http://axschema.org/namePerson/friendly"]) and count($arr_ax_data["http://axschema.org/namePerson/friendly"])>0) {
                    $name_alias = $arr_ax_data["http://axschema.org/namePerson/friendly"][0];
                }
                if (isset($arr_ax_data["http://axschema.org/contact/email"]) and count($arr_ax_data["http://axschema.org/contact/email"])>0) {
                    $email = $arr_ax_data["http://axschema.org/contact/email"][0];
                }
                if (isset($arr_ax_data["http://axschema.org/pref/language"]) and count($arr_ax_data["http://axschema.org/pref/language"])>0) {
                    $language = $arr_ax_data["http://axschema.org/pref/language"][0];
                }
                if (isset($arr_ax_data['http://axschema.org/birthDate']) and count($arr_ax_data['http://axschema.org/birthDate'])>0) {
                    $dateofbirth = $arr_ax_data['http://axschema.org/birthDate'][0];
                }
                if (isset($arr_ax_data['http://axschema.org/person/gender']) and count($arr_ax_data['http://axschema.org/person/gender'])>0) {
                    $gender = $arr_ax_data['http://axschema.org/person/gender'][0];
                }
                if (isset($arr_ax_data['http://axschema.org/contact/postalCode/home']) and count($arr_ax_data['http://axschema.org/contact/postalCode/home'])>0) {
                    $postcode = $arr_ax_data['http://axschema.org/contact/postalCode/home'][0];
                }
                if (isset($arr_ax_data['http://axschema.org/contact/country/home']) and count($arr_ax_data['http://axschema.org/contact/country/home'])>0) {
                    $country = $arr_ax_data['http://axschema.org/contact/country/home'][0];
                }
                if (isset($arr_ax_data['http://axschema.org/pref/timezone']) and count($arr_ax_data['http://axschema.org/pref/timezone'])>0) {
                    $timezone = $arr_ax_data['http://axschema.org/pref/timezone'][0];
                }
            }
            if ($name_full == '' and ($name_prefix != '' or $name_first != '' or $name_middle != '' or $name_last != '' or $name_suffix != '')) {
                foreach (array($name_prefix, $name_first, $name_middle, $name_last, $name_suffix) as $name_part) {
                    if ($name_full != '' and $name_part != '') {
                        $name_full .= ' ';
                    }
                    if ($name_part != '') {
                        $name_full .= $name_part;
                    }
                }
            }
            $_SESSION['OPENID_AUTH'] = array('url' => $openid_url,
                                             'fullname' => $name_full,
                                             'nickname' => $name_alias,
                                             'email' => $email,
                                             'language' => $language,
                                             'dob' => $dateofbirth,
                                             'gender' => $gender,
                                             'postcode' => $postcode,
                                             'country' => $country,
                                             'timezone' => $timezone);
        } else {
            $_SESSION['OPENID_AUTH'] = false;
            header("Location: {$_SESSION['OPENID_FAILED']}");
        }

        // redirect to restricted application page
        header("Location: {$_SESSION['OPENID_SUCCESS']}");
    }

    /**
     * The Setter for the AX/SReg value named
     *
     * @param boolean $state Should this value be requested
     *
     * @return void
     */
    public static function set_nickname($state)
    {
        $handler = self::getHandler();
        $handler->_nickname = $state;
    }

    /**
     * The Setter for the AX/SReg value named
     *
     * @param boolean $state Should this value be requested
     *
     * @return void
     */
    public static function set_email($state)
    {
        $handler = self::getHandler();
        $handler->_email = $state;
    }

    /**
     * The Setter for the AX/SReg value named
     *
     * @param boolean $state Should this value be requested
     *
     * @return void
     */
    public static function set_realname($state)
    {
        $handler = self::getHandler();
        $handler->_realname = $state;
    }

    /**
     * The Setter for the AX/SReg value named
     *
     * @param boolean $state Should this value be requested
     *
     * @return void
     */
    public static function set_language($state)
    {
        $handler = self::getHandler();
        $handler->_language = $state;
    }

    /**
     * The Setter for the AX/SReg value named
     *
     * @param boolean $state Should this value be requested
     *
     * @return void
     */
    public static function set_dateofbirth($state)
    {
        $handler = self::getHandler();
        $handler->_dateofbirth = $state;
    }

    /**
     * The Setter for the AX/SReg value named
     *
     * @param boolean $state Should this value be requested
     *
     * @return void
     */
    public static function set_gender($state)
    {
        $handler = self::getHandler();
        $handler->_gender = $state;
    }

    /**
     * The Setter for the AX/SReg value named
     *
     * @param boolean $state Should this value be requested
     *
     * @return void
     */
    public static function set_postcode($state)
    {
        $handler = self::getHandler();
        $handler->_postcode = $state;
    }

    /**
     * The Setter for the AX/SReg value named
     *
     * @param boolean $state Should this value be requested
     *
     * @return void
     */
    public static function set_country($state)
    {
        $handler = self::getHandler();
        $handler->_country = $state;
    }

    /**
     * The Setter for the AX/SReg value named
     *
     * @param boolean $state Should this value be requested
     *
     * @return void
     */
    public static function set_timezone($state)
    {
        $handler = self::getHandler();
        $handler->_timezone = $state;
    }

    /**
     * The Setter for the AX/SReg value named
     *
     * @param boolean $array These values should be requested
     *
     * @return void
     */
    public static function set_ax_attribute($array)
    {
        $handler = self::getHandler();
        $handler->_attributeAx = $array;
    }

    /**
     * The Setter for the AX/SReg value named
     *
     * @param boolean $array These values should be requested
     *
     * @return void
     */
    public static function set_sreg_attribute($array)
    {
        $handler = self::getHandler();
        $handler->_attributeSReg = $array;
    }
}