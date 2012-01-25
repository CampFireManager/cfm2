<?php

class object_user extends object_generic
{
    // Generic Object Requirements
    protected $arrDBItems = array(
    	'strUserName'=>true,
    	'strUserAuthOpenID'=>true,
    	'strUserAuthHttpBasic'=>true,
        'strUserAuthMail'=>true,
        'strUserAuthXmpp'=>true,
        'strUserAuthSms'=>true,
        'isWorker'=>true,
        'isAdmin'=>true,
        'hasAttended'=>true,
        'isHere'=>true,
    );
    protected $strDBTable = "user";
    protected $strDBKeyCol = "userID";
    // Local Object Requirements
    protected $strUserName = null;
    protected $strUserAuthOpenID = null;
    protected $strUserAuthHttpBasic = null;
    protected $strUserAuthMail = null;
    protected $strUserAuthXmpp = null;
    protected $strUserAuthSms = null;
    protected $isWorker = false;
    protected $isAdmin = false;
    protected $hasAttended = false;
    protected $isHere = false;
}