<?php

class Base_Session
{
    /**
    * Overide the standard session_start() call, with our preferred longer cookie timer.
    *
    * @return void
    */
    function start()
    {
        if (session_id()==='') {
            // 604800 is 7 Days in seconds
            $currentCookieParams = session_get_cookie_params();
            session_set_cookie_params(604800, $currentCookieParams["path"], $currentCookieParams["domain"], $currentCookieParams["secure"], $currentCookieParams["httponly"]);
            session_start();
            setcookie(session_name(), session_id(), time() + 604800, $currentCookieParams["path"], $currentCookieParams["domain"]);
        }
    }

}