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

// This is vital - it clears the cleartext passwords stored for display purposes
Base_Hook::addHooks(new Plugin_TalkFixer());
Base_Hook::addHooks(new Plugin_LimboTalks());
Base_Hook::addHooks(new Plugin_ResetCleartexts());
Base_Hook::addHooks(new Plugin_InputParser());
Base_Hook::addHooks(new Plugin_GlueBroadcaster());
Base_Hook::addHooks(new Plugin_JoindIn());
