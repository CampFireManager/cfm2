<?php

class Object_Track extends Base_GenericObject
{
    // Generic Object Requirements
    protected $arrDBItems = array(
    	'strTrackName' => array('type' => 'varchar', 'length' => 255)
    );
    protected $strDBTable = "track";
    protected $strDBKeyCol = "intTrackID";
    protected $mustBeAdminToModify = true;
    // Local Object Requirements
    protected $intTrackID = null;
    protected $strTrackName = null;
    // Post-processing Properties
    protected $arrTalks = null;
    
    function getSelf()
    {
        $self = parent::getSelf();
        if ($this->booleanFull) {
            if ($this->arrTalks == null) {
                $this->arrTalks = Object_Talk::brokerByColumnSearch('intTrackID', $this->intTrackID);
            }
            if ($this->arrTalks == null) {
                foreach($this->arrTalks as $talk) {
                    $self['arrTalks'][] = $talk->getSelf();
                }
            }
        }
        return $self;
        
    }
}

class Object_Track_Demo extends Object_Track
{
    protected $mustBeAdminToModify = false;
    protected $arrDemoData = array(
        array('intTrackID' => 1, 'strTrackName' => 'Coding'),
        array('intTrackID' => 1, 'strTrackName' => 'Novice')
    );
}