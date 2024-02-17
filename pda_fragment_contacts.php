<?php
namespace app\forms;

use std, gui, framework, app;


class pda_fragment_contacts extends AbstractForm
{
    /**
     * @event alex_frame.click-Left 
     */
    function CharacterClick(UXMouseEvent $e = null)
    {    
        $this->selected_new->show();
        $this->bio->show();                     
    }
    /**
     * @event frame.click-Left 
     */
    function HideCharacter(UXMouseEvent $e = null)
    {    
        if ($this->selected_new->visible)
        {
            $this->selected_new->hide();
            $this->bio->hide();            
        }          
    }
    function AddEnemyContacts()
    {
        $this->name->show();
        $this->character->show();  
        $this->character_desc->show();
        $this->online_icon->show();
        $this->alex_frame->show();
        $this->icon_alex->show();
        if($this->bio->visible)
        {
            $this->bio->hide();
        }           
    }
    function DeleteEnemyContacts()
    {
        $this->name->hide();
        $this->character->hide();  
        $this->character_desc->hide();
        $this->online_icon->hide();
        $this->alex_frame->hide();
        $this->icon_alex->hide();
        if($this->bio->visible)
        {
            $this->bio->hide();
        }             
    }    
}
