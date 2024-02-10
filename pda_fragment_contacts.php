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
}
