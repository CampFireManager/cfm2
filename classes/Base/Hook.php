<?php

class Base_Hook
{
    protected static $hook_handler = null;
    protected $arrHooks = array();
    protected $start = true;
    protected $arrTriggers = array(
        // Routine Actions
        'cronTick' => true,
        // Render Types
        'apiRender' => true,
        'httpRender' => true,
        'mediaRender' => true,
        // Database Actions
        'createRecord' => true,
        'updateRecord' => true,
        'deleteRecord' => true,
        // Site Specific Actions
        // Users - Actions
        'registerUser' => true,
        'arriveUser' => true,
        'leaveUser' => true,
        'promoteUser' => true,
        'addAuthenticationMethod' => true,
        'removeAuthenticationMethod' => true,
        // Users - Logging In
        'userLogin' => true,
        'deviceLogin' => true,
        'onetimeLogin' => true,
        'openidLogin' => true,
        'userLogout' => true,
        'openidLogout' => true,
        // Talks - Owner
        'lockTalk' => true,
        'proposeTalk' => true,
        'createTalk' => true,
        'removeTalk' => true,
        'editTalk' => true,
        'requestResource' => true,
        'unrequestResource' => true,
        'addPresenter' => true,
        'removePresenter' => true,
        'addLink' => true,
        'removeLink' => true,
        // Talks - Administrator/Site Actions
        'fixTalk' => true,
        'talkStart' => true,
        'acceptProposedTalk' => true,
        'declineProposedTalk' => true,
        'setTrackForTalk' => true,
        'removeTrackForTalk' => true,
        // Talks - Attendees
        'attendTalk' => true,
        'leaveTalk' => true,
        'rateTalk' => true,
        'tagTalk' => true,
        'favouriteTalk' => true,
        'unfavouriteTalk' => true,
        // Rooms
        'createRoom' => true,
        'deleteRoom' => true,
        'addResourceToRoom' => true,
        'removeResourceFromRoom' => true,
        'lockRoom' => true,
        'unlockRoom' => true,
        // Resources
        'createResource' => true,
        'destroyResource' => true,
        'paidResource' => true,
        'refundResource' => true,
        // Slots
        'createSlot' => true,
        'destroySlot' => true
    );

    /**
     * An internal function to make this a singleton. This should only be used when being used to find objects of itself.
     *
     * @return object This class by itself.
     */
    public static function getHandler()
    {
        if (Base_Hook::$hook_handler == null) {
            Base_Hook::$hook_handler = new Base_Hook();
        }
        if (Base_Hook::$hook_handler->start == true) {
            Base_Hook::$hook_handler->start = false;
            include dirname(__FILE__) . '/../../config/plugin.php';
        }
        return Base_Hook::$hook_handler;
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
        $self = Base_Hook::getHandler();
        if (is_object($objHook)) {
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

    public static function triggerHook($strAction = null, $parameters = null)
    {
        $self = Base_Hook::getHandler();
        if ($strAction == null) {
            throw new Exception('No hooks triggered', 0);
        } else {
            $activeHook = false;
            foreach ($self->arrTriggers as $strTrigger => $dummyValue) {
                if ($strAction == $strTrigger) {
                    $activeHook = true;
                }
            }
            if ($activeHook == false) {
                throw new Exception('Invalid hook triggered', 1);
            }
            $strHookAction = 'hook_' . $strAction;
            if (isset($self->arrHooks[$strAction]) && count ($self->arrHooks[$strAction]) > 0) {
                foreach ($self->arrHooks[$strAction] as $objHook) {
                    $objHook->$strHookAction($parameters);
                }
            }
        }
    }
}

