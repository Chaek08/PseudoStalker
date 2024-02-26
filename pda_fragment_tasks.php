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
        if ($this->task_detail_text->toggle())
        {
            $this->quest_detail_btn->image = new UXImage('res://.data/ui/pda/task_detail_off.png');
            $this->quest_detail_btn->hoverImage = new UXImage('res://.data/ui/pda/task_detail_on.png'); 
            $this->quest_detail_btn->clickImage = new UXImage('res://.data/ui/pda/task_detail_on.png');             
                       
        }   
        if ($this->task_detail_text->visible)
        {
            $this->quest_detail_btn->image = new UXImage('res://.data/ui/pda/task_detail_opened.png');  
            $this->quest_detail_btn->hoverImage = new UXImage('res://.data/ui/pda/task_detail_opened.png');
            $this->quest_detail_btn->clickImage = new UXImage('res://.data/ui/pda/task_detail_opened.png');                                 
        }
        else
        {
            $this->quest_detail_btn->image = new UXImage('res://.data/ui/pda/task_detail_off.png');
            $this->quest_detail_btn->hoverImage = new UXImage('res://.data/ui/pda/task_detail_on.png'); 
            $this->quest_detail_btn->clickImage = new UXImage('res://.data/ui/pda/task_detail_on.png');               
        }
    }
    /**
     * @event frame_hide.click-Left 
     */
    function ClearDetailTask(UXMouseEvent $e = null) //заебись, сделал один фрейм на весь экран и тем самым сократил количество функций :like
    {    
        if ($this->task_detail_text->visible) 
        {
            $this->task_detail_text->hide();   
            $this->quest_detail_btn->image = new UXImage('res://.data/ui/pda/task_detail_off.png');
            $this->quest_detail_btn->hoverImage = new UXImage('res://.data/ui/pda/task_detail_on.png'); 
            $this->quest_detail_btn->clickImage = new UXImage('res://.data/ui/pda/task_detail_on.png');                      
        }               
    }
    function AddTask()
    {
        $this->task_label->show();
        $this->task_detail_text->show();
        $this->icon_task->show();
        $this->quest_detail_btn->show();
        $this->time_quest->show();
        $this->step1->show();
        $this->step2->show();
    }
    
    function Step1_Complete() {$this->step1->graphic = new UXImageView(new UXImage('res://.data/ui/pda/task_step_complete.png'));}
    function Step2_Complete() {$this->step2->graphic = new UXImageView(new UXImage('res://.data/ui/pda/task_step_complete.png'));}  
    function StepReset()
    {
        $this->step1->graphic = new UXImageView(new UXImage('res://.data/ui/pda/task_step_process.png'));    
        $this->step2->graphic = new UXImageView(new UXImage('res://.data/ui/pda/task_step_process.png'));    
    } 
     
    function DeleteTask()
    {
        $this->task_label->hide();
        $this->task_detail_text->hide();
        $this->icon_task->hide();
        $this->quest_detail_btn->hide();   
        $this->time_quest->hide();
        $this->StepReset();
        $this->step1->hide();
        $this->step2->hide();                     
    }
}
