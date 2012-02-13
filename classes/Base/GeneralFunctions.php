<?php

class Base_GeneralFunctions
{
    /**
     * This function looks for a value within an array or an object, and returns it if it's there. If it isn't it
     * returns the default value.
     *
     * @param mixed   $haystack     The object or array to check within
     * @param string  $needle       The key or property to look for
     * @param mixed   $default      The value to return if the key or property doesn't exist
     * @param boolean $emptyisfalse If true, and the result of the check returns an empty string, return the default value
     *
     * @return mixed The value found, or the default if not.
     */
    function getValue($haystack = null, $needle = null, $default = false, $emptyisfalse = true)
    {
        if ($haystack != null && $needle !== null) {
            if (is_array($haystack) && count($haystack) > 0 && isset($haystack[$needle])) {
                if ($emptyisfalse == true && (string) $haystack[$needle] == '') {
                    return $default;
                } else {
                    return $haystack[$needle];
                }
            } elseif (is_object($haystack) && isset($haystack->$needle)) {
                if ($emptyisfalse == true && (string) $haystack->$needle == '') {
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
     * @param boolean $alphaLower Toggles the use of lowercase letters (a-z). Default is 1 (lowecase letters may be used).
     * @param boolean $alphaUpper Toggles the use of uppercase letters (A-Z). Default is 1 (uppercase letters may be used).
     * @param boolean $num        Toggles the use of numbers (0-9). Default is 1 (numbers may be used).
     * @param integer $batch      Specify the number of strings to create. Default is 1 (returns one string). When $batch is not 1 the function returns an array of multiple strings.
     * 
     * @return string The generated string
     * 
     * @link http://www.php.net/manual/en/function.rand.php#94788
     */
    function genRandStr($minLen, $maxLen, $alphaLower = true, $alphaUpper = true, $num = true, $batch = 1) {
        $alphaLowerArray = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
        $alphaUpperArray = array('A', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'M', 'N', 'P', 'Q', 'R', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $numArray = array(2, 3, 4, 6, 7, 9);

        if (isset($minLen) && isset($maxLen)) {
            if ($minLen == $maxLen) {
                $strLen = $minLen;
            } else {
                $strLen = rand($minLen, $maxLen);
            }
            $merged = array_merge($alphaLowerArray, $alphaUpperArray, $numArray);

            if ($alphaLower && $alphaUpper && $num) {
                $finalArray = array_merge($alphaLowerArray, $alphaUpperArray, $numArray);
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
                $i = 1;
                while ($i <= $strLen) {
                    $rand = rand(0, $count-1);
                    $newChar = $finalArray[$rand];
                    $str .= $newChar;
                    $i++;
                }
                $result = $str;
            } else {
                $j = 1;
                $result = array();
                while ($j <= $batch) {
                    $str = '';
                    $i = 1;
                    while ($i <= $strLen) {
                        $rand = rand(0, $count-1);
                        $newChar = $finalArray[$rand];
                        $str .= $newChar;
                        $i++;
                    }
                    $result[] = $str;
                    $j++;
                }
            }
            return $result;
        }
    }

}