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
        if ($this->selected_new->visible) // а вот тут условие нужно, ведь не факт что игрок сразу тыкнет на алекса, а лишний код нам не нужон
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
        //$this->selected_new->show(); 
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
        //$this->selected_new->hide();  
        if($this->bio->visible)
        {
            $this->bio->hide();
        }             
    }    
}
