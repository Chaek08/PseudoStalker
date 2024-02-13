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
        $this->task_detail_text->toggle() == $this->task_detail_text->visible;      
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
    
    function AddTask()
    {
        $this->task_label->show();
        $this->task_detail_text->show();
        $this->icon_task->show();
        $this->quest_detail_btn->show();
        $this->time_quest->show();
    }
    function DeleteTask()
    {
        $this->task_label->hide();
        $this->task_detail_text->hide();
        $this->icon_task->hide();
        $this->quest_detail_btn->hide();   
        $this->time_quest->hide();             
    }
}
