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
}