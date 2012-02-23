<?php

class Base_Hook
{
    protected static $hook_handler = null;
    protected static $arrHooks = array();
    protected static $arrTriggers = array(
        'cronTick' => true,
        'apiRender' => true,
        'httpRender' => true,
        'userLogin' => true,
        'deviceLogin' => true,
        'createRecord' => true,
        'updateRecord' => true,
        'deleteRecord' => true
    );

    /**
     * An internal function to make this a singleton. This should only be used when being used to find objects of itself.
     *
     * @return object This class by itself.
     */
    public static function getHandler()
    {
        if (self::$hook_handler == null) {
            self::$hook_handler = new self();
        }
        return self::$hook_handler;
    }

    protected function __construct()
    {
        include_once dirname(__FILE__) . '/../../config/plugin.php';
    }

    public static function addTrigger($strTrigger = null)
    {
        if ($strTrigger == null) {
            return false;
        }
        $this->arrTriggers[$strTrigger] = true;
    }
    
    public static function addHook($objHook = null)
    {
        if (is_object($objHook)) {
            $boolTriggerSet = false;
            foreach (self::$arrTriggers as $strTrigger => $dummyValue) {
                if (method_exists($objHook, 'hook_' . $strTrigger)) {
                    self::$arrHooks[$strTrigger][] = $objHook;
                    $boolTriggerSet = true;
                }
            }
            if ($boolTriggerSet == false) {
                throw new Exception('No recognised hooks', 0);
            }
        } else {
            throw new Exception('Not an object', 1);
        }
    }

    public static function triggerHook($strAction = null, $parameters = null)
    {
        if ($strAction == null) {
            throw new Exception('No hooks triggered', 0);
        } else {
            $activeHook = false;
            foreach (self::$arrTriggers as $strTrigger => $dummyValue) {
                if ($strAction == $strTrigger) {
                    $activeHook = true;
                }
            }
            if ($activeHook == false) {
                throw new Exception('Invalid hook triggered', 1);
            }
            $strHookAction = 'hook_' . $strAction;
            if (isset(self::$arrHooks[$strAction]) && count (self::$arrHooks[$strAction]) > 0) {
                foreach (self::$arrHooks[$strAction] as $objHook) {
                    $objHook->$strHookAction($parameters);
                }
            }
        }
    }
}

