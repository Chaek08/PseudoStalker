<?php
namespace app\forms;
use action\Element; 
use php\time\Time;

use std, gui, framework, app;


class pda_fragments_stat extends AbstractForm
{
    /**
     * @event spoiler_button.click-Left 
     */
    function SpoilerShow(UXMouseEvent $e = null)
    {    
        $this->spoiler_button->hide();
        $this->hide_spoiler_button->show();        
        $this->target_label->height = 416;     
    }
    /**
     * @event hide_spoiler_button.click-Left 
     */
    function SpoilerHide(UXMouseEvent $e = null)
    {
        $this->spoiler_button->show();
        $this->hide_spoiler_button->hide();
        $this->target_label->height = 112;         
    }
}
