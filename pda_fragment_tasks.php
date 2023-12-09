<?php
namespace app\forms;

use std, gui, framework, app;


class pda_fragment_tasks extends AbstractForm
{
    /**
     * @event quest_detail_btn.click-Left 
     */
    function DetailTask(UXMouseEvent $e = null)
    {    
        $this->task_detail_text->show();      
    }
    /**
     * @event frame_hide.click-Left 
     */
    function ClearDetailTask(UXMouseEvent $e = null) //заебись, сделал один фрейм на весь экран и тем самым сократил количество функций :like
    {    
        if ($this->task_detail_text->visible) 
        {
            $this->task_detail_text->hide();            
        }               
    }
}
