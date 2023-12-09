<?php
namespace app\forms;

use std, gui, framework, app;


class pda_fragment_contacts extends AbstractForm
{
    /**
     * @event alex_frame.click-Left 
     */
    function CharacterAlexClick(UXMouseEvent $e = null)
    {    
        $this->selected->show();
        $this->bio->show();                     
    }
    /**
     * @event frame_01.click-Left 
     */
    function OtherClick(UXMouseEvent $e = null)
    {    
        if ($this->selected->visible) // а вот тут условие нужно, ведь не факт что игрок сразу тыкнет на алекса, а лишний код нам не нужон
        {
            $this->selected->hide();
            $this->bio->hide();            
        }          
    }

}
