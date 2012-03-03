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
Base_Hook::addHook(new Plugin_ResetCleartexts());

// Base_Hook::addHook(new Plugin_Twitter());
// Base_Hook::addHook(new Plugin_Verbose());
