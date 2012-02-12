<?php

class Base_Hook
{
    protected static $self = null;
    protected $arrHooks = array();
    protected $arrTriggers = array(
        'cronTick' => true,
        'apiRender' => true,
        'httpRender' => true,
        'userLogin' => true,
        'deviceLogin' => true,
        'createRecord' => true,
        'updateRecord' => true,
        'deleteRecord' => true
    );

    protected function getSelf()
    {
        if (self::$self == null) {
            self::$self = new self();
        }
        return self::$self;
    }

    protected function __construct()
    {
        include_once dirname(__FILE__) . '/../../config/plugin.php';
    }

    public function addTrigger($strTrigger = null)
    {
        if ($strTrigger == null) {
            return false;
        }
        $this->arrTriggers[$strTrigger] = true;
    }
    
    public function addHook($strAction = null, $objHook = null)
    {
        if (is_object($objHook)) {
            $self = self::getSelf();
            $boolTriggerSet = false;
            foreach ($self->arrTriggers as $strTrigger => $dummyValue) {
                if (method_exists($objHook, 'hook_' . $strTrigger)) {
                    $self->arrHooks[$strTrigger][] = $objHook;
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

    public function triggerHook($strAction = null, $parameters = null)
    {
        echo "Trigger Hooks - $strAction\r\n";
        if ($strAction == null) {
            throw new Exception('No hooks triggered', 0);
        } else {
            $self = self::getSelf();
            foreach ($self->arrTriggers as $strTrigger => $dummyValue) {
                if ($strAction != $strTrigger) {
                    throw new Exception('Invalid hook triggered', 1);
                }
            }
            $strHookAction = 'hook_' . $strAction;
            foreach ($self->arrHooks[$strAction] as $objHook) {
                $objHook->$strHookAction($parameters);
            }
        }
    }
}

