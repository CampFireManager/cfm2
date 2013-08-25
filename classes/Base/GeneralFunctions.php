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
 * This class provides all the functions which are needed by code in the site
 * but which don't fit into more specific classes.
 *
 * @category Base_GeneralFunctions
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/CampFireManager/cfm2 Version Control Service
 */

class Base_GeneralFunctions
{
    /**
     * This is here purely to prevent the class being instantiated as "new".
     */
    public function __construct()
    {
        throw new BadMethodCallException("Do not instantiate this class");
    }
    
    /**
     * This function looks for a value within an array or an object, and 
     * returns it if it's there. If it isn't it returns the default value.
     *
     * @param mixed   $haystack     The object or array to check within
     * @param string  $needle       The key or property to look for
     * @param mixed   $default      The value to return if the key or property 
     * doesn't exist
     * @param boolean $emptyisfalse If true, and the result of the check returns
     * an empty string, return the default value
     *
     * @return mixed The value found, or the default if not.
     */
    public static function getValue(
        $haystack = null, 
        $needle = null, 
        $default = false, 
        $emptyisfalse = true
    ) {
        if ($haystack != null && $needle !== null) {
            if (is_array($haystack) 
                && count($haystack) > 0 
                && isset($haystack[$needle])
            ) {
                if ($emptyisfalse == true 
                    && (string) $haystack[$needle] == ''
                ) {
                    return $default;
                } else {
                    return $haystack[$needle];
                }
            } elseif (is_object($haystack) && isset($haystack->$needle)) {
                if ($emptyisfalse == true 
                    && (string) $haystack->$needle == ''
                ) {
                    return $default;
                } else {
                    return $haystack->$needle;
                }
            } else {
                return $default;
            }
        } else {
            return $default;
        }
    }

    /**
     * A function to generate and return strings of characters
     *
     * @param integer $minLen     The minimum length of the string. Required.
     * @param integer $maxLen     The maximum length of the string. Required.
     * @param boolean $alphaLower Toggles the use of lowercase letters (a-z). 
     * Default is 1 (lowecase letters may be used).
     * @param boolean $alphaUpper Toggles the use of uppercase letters (A-Z). 
     * Default is 1 (uppercase letters may be used).
     * @param boolean $num        Toggles the use of numbers (0-9). Default is 1 
     * (numbers may be used).
     * @param integer $batch      Specify the number of strings to create. 
     * Default is 1 (returns one string). When $batch is not 1 the function 
     * returns an array of multiple strings.
     * 
     * @return string The generated string
     * 
     * @link http://www.php.net/manual/en/function.rand.php#94788
     */
    public static function genRandStr(
        $minLen, 
        $maxLen, 
        $alphaLower = true, 
        $alphaUpper = true, 
        $num = true, 
        $batch = 1
    ) {
        $alphaLowerArray = array(
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'm', 'n', 
            'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'
        );
        $alphaUpperArray = array(
            'A', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'M', 'N', 'P', 'Q', 
            'R', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
        );
        $numArray = array(2, 3, 4, 6, 7, 9);

        if (isset($minLen) && isset($maxLen)) {
            if ($minLen == $maxLen) {
                $strLen = $minLen;
            } else {
                $strLen = rand($minLen, $maxLen);
            }
            if ($alphaLower && $alphaUpper && $num) {
                $finalArray = array_merge(
                    $alphaLowerArray,
                    $alphaUpperArray, 
                    $numArray
                );
            } elseif ($alphaLower && $alphaUpper && ! $num) {
                $finalArray = array_merge($alphaLowerArray, $alphaUpperArray);
            } elseif ($alphaLower && ! $alphaUpper && $num) {
                $finalArray = array_merge($alphaLowerArray, $numArray);
            } elseif (! $alphaLower && $alphaUpper && $num) {
                $finalArray = array_merge($alphaUpperArray, $numArray);
            } elseif ($alphaLower && ! $alphaUpper && ! $num) {
                $finalArray = $alphaLowerArray;
            } elseif (! $alphaLower && $alphaUpper && ! $num) {
                $finalArray = $alphaUpperArray;
            } elseif (! $alphaLower && ! $alphaUpper && $num) {
                $finalArray = $numArray;
            } else {
                return FALSE;
            }

            $count = count($finalArray);

            if ($batch == 1) {
                $str = '';
                $counter = 1;
                while ($counter <= $strLen) {
                    $rand = rand(0, $count-1);
                    $newChar = $finalArray[$rand];
                    $str .= $newChar;
                    $counter++;
                }
                $result = $str;
            } else {
                $outercounter = 1;
                $result = array();
                while ($outercounter <= $batch) {
                    $str = '';
                    $innercounter = 1;
                    while ($innercounter <= $strLen) {
                        $rand = rand(0, $count-1);
                        $newChar = $finalArray[$rand];
                        $str .= $newChar;
                        $innercounter++;
                    }
                    $result[] = $str;
                    $outercounter++;
                }
            }
            return $result;
        }
    }

    /**
     * Return boolean true for 1 and boolean false for 0
     *
     * @param integer $check Value to check
     *
     * @return boolean Result
     */
    public static function asBoolean($check)
    {
        if ($check === true) {
            return true;
        } elseif ($check === false) {
            return false;
        }
        switch((string) $check) {
        case 'no':
        case '0':
        case 'false':
            return false;
        case '1':
        case 'yes':
        case 'true':
            return true;
        default:
            return false;
        }
    }

    /**
     * Return the size of the JSON array
     *
     * @param JSON $strJson A JSON encoded array
     *
     * @return integer The size of the JSON array
     */
    public static function sizeJson($strJson = '')
    {
        $arrJson = json_decode($strJson, true);
        if (count($arrJson) == 0) {
            $arrJson[] = $strJson;
        }
        return count($arrJson);
    }

    /**
     * Add a new string to an existing JSON array.
     *
     * @param JSON   $strJson     The existing JSON array.
     * @param string $strNewKey   The key to add
     * @param string $strNewValue The value to add
     *
     * @return JSON The resulting JSON array.
     */
    public static function addJson(
        $strJson = '', 
        $strNewKey = '',
        $strNewValue = ''
    ) {
        $set = false;
        $arrJson = json_decode($strJson, true);
        if (count($arrJson) == 0 and $strJson != '') {
            $arrJson[] = $strJson;
        } elseif ($strJson == '') {
            $arrJson = array();
        }
        $arrTemp = array();
        foreach ($arrJson as $key => $value) {
            if ($value == $strNewValue) {
                $set = true;
            }
            $arrTemp[$key] = $value;
        }
        if ($set == false) {
            if ($strNewKey == null) {
                $arrTemp[] = $strNewValue;
            } else {
                $arrTemp[$strNewKey] = $strNewValue;
            }
        }
        return json_encode($arrTemp);
    }

    /**
     * This function removes a value from the JSON array.
     *
     * @param JSON   $strJson          The JSON array to operate on
     * @param string $strValueToRemove The value to remove from the array
     *
     * @return false|JSON The modified array, or false, if there is only one 
     * value.
     */
    public static function delJson($strJson = '', $strValueToRemove = '')
    {
        $arrJson = json_decode($strJson, true);
        if (count($arrJson) == 0) {
            $arrJson[] = $strJson;
        }
        $arrTemp = array();
        foreach ($arrJson as $key=>$value) {
            if ($value != $strValueToRemove) {
                $arrTemp[$key] = $value;
            }
        }
        return json_encode($arrTemp);
    }

    /**
     * Find a value in a JSON encoded array
     *
     * @param JSON   $strJson        The JSON encoded array.
     * @param string $strValueToFind The value to find
     *
     * @return boolean If the value is there.
     */
    public static function inJson($strJson = '', $strValueToFind = '')
    {
        $arrJson = json_decode($strJson, true);
        if (count($arrJson) == 0) {
            $arrJson[] = $strJson;
        }
        foreach ($arrJson as $value) {
            if ($value == $strValueToFind) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return the decoded JSON array of data
     *
     * @param JSON $strJson The data to decode
     *
     * @return array The data, in array format.
     */
    public static function getJson($strJson = '')
    {
        $arrJson = json_decode($strJson, true);
        if (count($arrJson) == 0) {
            $arrJson[] = $strJson;
        }
        return $arrJson;
    }

    /**
     * Return UTF8 encoded array
     *
     * @param array|object|string|integer|float|boolean|null $array Ideally an 
     * array of data to process, but failing that, return the data as a member 
     * of an array.
     *
     * @return array UTF8 encoded array
     *
     * @link http://www.php.net/manual/en/function.json-encode.php#99837
     */
    public static function utf8element($array = null)
    {
        $newArray = array();
        if (is_object($array)) {
            // Force objects to be recast as an array
            $array = (array) $array;
        } elseif (is_array($array)) {
            // It's an array already, we don't need to mangle it.
        } else {
            // Individual items should be recast to be the only item in an array
            $array = array($array);
        }
        foreach ($array as $key=>$val) {
            if (is_array($val) || is_object($val)) {
                $newArray[utf8_encode($key)] = static::utf8element($val);
            } elseif ($val === false) {
                $newArray[utf8_encode($key)] = 'false';
            } elseif ($val == null) {
                $newArray[utf8_encode($key)] = '';
            } else {
                $newArray[utf8_encode($key)] = utf8_encode($val);
            }
        }
        return $newArray;
    }

    /**
     * Return utf8 encoded JSON
     *
     * @param Array|object $array Incoming data
     *
     * @return string UTF8 encoded JSON string
     */
    public static function utf8json($array = array())
    {
        return json_encode(Base_GeneralFunctions::utf8element($array));
    }

    /**
     * Return utf8 encoded HTML
     *
     * @param Array|object $array Incoming data
     *
     * @return string UTF8 encoded HTML tables
     */
    public static function utf8html($array = array())
    {
	// This code from http://jsfiddle.net/KJQ9K/
	return "<html><head><title>REST API Response</title><style>" . "\r\n" .
"pre {outline: 1px solid #ccc; padding: 5px; margin: 5px; }" . "\r\n" .
".string { color: green; }" . "\r\n" .
".number { color: darkorange; }" . "\r\n" .
".boolean { color: blue; }" . "\r\n" .
".null { color: magenta; }" . "\r\n" .
".key { color: red; }" . "\r\n" .
"</style>" . "\r\n" .
"</head>" . "\r\n" .
"<body><h1>REST API Response</h1>" . "\r\n" .
'<script type="text/javascript">' . "\r\n" .
"function output(inp) {" . "\r\n" .
"    document.body.appendChild(document.createElement('pre')).innerHTML = inp;" . "\r\n" .
"}" . "\r\n" .
"function syntaxHighlight(json) {" . "\r\n" .
"    json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');" . "\r\n" .
'    return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {' . "\r\n" .
"        var cls = 'number';" . "\r\n" .
'        if (/^"/.test(match)) {' . "\r\n" .
"            if (/:$/.test(match)) {" . "\r\n" .
"                cls = 'key';" . "\r\n" .
"            } else {" . "\r\n" .
"                cls = 'string';" . "\r\n" .
"            }" . "\r\n" .
"        } else if (/true|false/.test(match)) {" . "\r\n" .
"            cls = 'boolean';". "\r\n" .
"        } else if (/null/.test(match)) {" . "\r\n" .
"            cls = 'null';". "\r\n" .
"        }". "\r\n" .
"        return '<span class=\"' + cls + '\">' + match + '</span>';" . "\r\n" .
"    });" . "\r\n" .
"}" . "\r\n" .
"var obj = " . json_encode($array) . ";" . "\r\n" .
"var str = JSON.stringify(obj, undefined, 4);" . "\r\n" .
"output(syntaxHighlight(str));" . "\r\n" .
"</script>" . "\r\n" .
"</body></html>";
    }

    /**
     * Similar to the json_encode function, this returns nested HTML tables 
     * instead of nested JSON data
     * 
     * @param array $array Data to encode
     * 
     * @return string HTML nested tables of the array of data
     */
    public static function html_encode($array = array())
    {
        $return = '<table>';
        foreach ($array as $key => $item) {
            $return .= '<tr><th>' . $key . '</th><td>';
            if (is_array($item)) {
                $return .= Base_GeneralFunctions::html_encode($item);
            } else {
                $return .= $item;
            }
            $return .= '</td></tr>';
        }
        $return .= '</table>';
        return $return;
    }

    /**
     * Return utf8 encoded XML with an optional root element name
     *
     * @param Array|object $array Incoming data
     * @param string       $root  The root element name - default to "root"
     *
     * @return string UTF8 encoded XML string
     */
    public static function utf8xml($array = array(), $root = 'root')
    {
        return Base_GeneralFunctions::xml_encode(
            array(
                $root => Base_GeneralFunctions::utf8element($array)
            )
        );
    }
    
    /**
     * Similar to the json_encode function, this returns nested XML stanzas.
     * It doesn't have the concept of parameters. Also, replaces a forward slash
     * with "[slash]"
     * 
     * @param array   $array Data to encode
     * @param integer $depth The number of spaces to indent each nested stanza 
     * by
     * 
     * @return string XML formatted data
     */
    public static function xml_encode($array = array(), $depth = 0)
    {
        $return = '';
        foreach ($array as $key => $item) {
            if (is_integer($key)) {
                $key = 'ID_' . $key;
            }
            $key = str_replace('/', '[slash]', $key);
            $key = str_replace('<', '[lt]', $key);
            $key = str_replace('>', '[gt]', $key);
            $key = str_replace('&', '[amp]', $key);
            $key = str_replace('"', '[dquote]', $key);
            $key = str_replace("'", '[squote]', $key);
            $return .= str_repeat(' ', $depth) . '<' . $key . ">";
            if (is_array($item)) {
                if (is_array($item) && count($item) > 0) {
                    $return .= "\r\n" 
                            . Base_GeneralFunctions::xml_encode($item, $depth + 4) 
                            . str_repeat(' ', $depth);
                } elseif (is_array($item)) {
                    // Don't do anything with an empty array
                } else {
                    $return .= $item;
                }
                $return .= '</' . $key . ">\r\n";
            }
            return $return;
        }
    }
    
    /**
     * Overide the standard session_start() call, with our preferred longer 
     * cookie timer.
     *
     * @return void
     */
    public static function startSession()
    {
        $objRequest = Container_Request::getRequest();
        if ($objRequest->get_strRequestMethod() != 'file' && session_id() === '') {
            // 604800 is 7 Days in seconds
            $currentCookieParams = session_get_cookie_params();
            session_set_cookie_params(
                604800, 
                $currentCookieParams["path"], 
                $currentCookieParams["domain"], 
                $currentCookieParams["secure"], 
                $currentCookieParams["httponly"]
            );
            @session_start();
            @setcookie(
                session_name(), 
                session_id(), 
                time() + 604800, 
                $currentCookieParams["path"], 
                $currentCookieParams["domain"]
            );
        }
    }
}
