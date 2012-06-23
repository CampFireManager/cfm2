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
 * This class handles all hooks and triggers for the site, including loading
 * all the hook/plugin objects.
 *
 * @category Base_Hook
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Base_Hook
{
    protected $boolFileLoaded = false;
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
     * Load the plugin data to the object.
     *
     * @param string $strFileName Filename to load
     * 
     * @return void
     */
    public function Load($strFileName = null)
    {
        if ($strFileName == null) {
            throw new UnexpectedValueException(
                "You did not specify a filename."
            );
        }
        
        if (!file_exists(dirname(__FILE__) . '/../../config/' . $strFileName)) {
            throw new UnexpectedValueException("This file does not exist.");
        }
        
        if (! include dirname(__FILE__) . '/../../config/' . $strFileName) {
            throw new InvalidArgumentException("Can't load this file");
        }
        
        $this->boolFileLoaded = true;
    }
    
    /**
     * Return the value of whether the file is loaded.
     *
     * @return boolean 
     */
    public function isFileLoaded()
    {
        return $this->boolFileLoaded;
    }
    
    /**
     * This function adds a new trigger to the comprehensive list above.
     *
     * @param string $strTrigger The trigger to add
     *
     * @return void
     */
    public function addTrigger($strTrigger = null)
    {
        if ($strTrigger != null) {
            $this->arrTriggers[$strTrigger] = true;
        }
    }
    
    /**
     * This function reads a plugin object to look for the triggers which can be tied into the the hook array.
     *
     * @param object $objHook An object to process for triggers.
     * 
     * @return void
     */
    public function addHook($objHook = null)
    {
        if (is_object($objHook)) {
            $boolTriggerSet = false;
            foreach ($this->arrTriggers as $strTrigger => $dummy) {
                $dummy = null;
                if (method_exists($objHook, 'hook_' . $strTrigger)) {
                    $this->arrHooks[$strTrigger][] = $objHook;
                    $boolTriggerSet = true;
                }
            }
            if ($boolTriggerSet == false) {
                throw new InvalidArgumentException('No recognised hooks', 0);
            }
        } else {
            throw new InvalidArgumentException('Not an object', 1);
        }
    }

    /**
     * This is the code which actually does something with the hook triggers we've set
     * in the funciton above.
     *
     * @param string $strAction  The name of the trigger to action
     * @param mixed  $parameters The resource to pass to the trigger function
     * 
     * @return void
     */
    public function triggerHook($strAction = null, $parameters = null)
    {
        if ($strAction == null) {
            throw new InvalidArgumentException('No hooks triggered', 0);
        } else {
            $activeHook = false;
            foreach ($this->arrTriggers as $strTrigger => $dummy) {
                $dummy = null;
                if ($strAction == $strTrigger) {
                    $activeHook = true;
                }
            }
            if ($activeHook == false) {
                throw new InvalidArgumentException('Invalid hook triggered', 1);
            }
            $strHookAction = 'hook_' . $strAction;
            if (isset($this->arrHooks[$strAction]) 
                && count($this->arrHooks[$strAction]) > 0
            ) {
                foreach ($this->arrHooks[$strAction] as $objHook) {
                    $objHook->$strHookAction($parameters);
                }
            }
        }
    }
}