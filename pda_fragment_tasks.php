<?php
namespace app\forms;

use std, gui, framework, app;
use php\time\Time;

class pda_fragment_tasks extends AbstractForm
{
    /**
     * @event show 
     */
    function InitTasks(UXWindowEvent $e = null)
    {
        $this->UpdateQuestTime();
    }
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
    /**
     * @event active_task.click-Left 
     */
    function ShowActiveTasks(UXMouseEvent $e = null)
    {    
        $this->ResetBtnColor();    
        $this->active_task->textColor = "#b3b31a";
        
        $GLOBALS['QuestCompleted'] ? $this->DeleteTask() : $this->AddTask();
    }
    /**
     * @event passive_task.click-Left 
     */
    function ShowPassiveTasks(UXMouseEvent $e = null)
    {    
        $this->ResetBtnColor();    
        $this->passive_task->textColor = "#b3b31a";
        
        $GLOBALS['EnemyFailed'] ? $this->AddTask() : $this->DeleteTask();
    }
    /**
     * @event failed_task.click-Left 
     */
    function ShowFailedTasks(UXMouseEvent $e = null)
    {    
        $this->ResetBtnColor();
        $this->failed_task->textColor = "#b3b31a";
        
        if ($GLOBALS['ActorFailed']) //актор проиграл
        {
            $this->AddTask();
            $this->step2->graphic = new UXImageView(new UXImage('res://.data/ui/pda/task_step_failed.png'));
        }
        else
        {
            $this->DeleteTask();
        }        
    }
    function ResetBtnColor()
    {
        $this->active_task->textColor = "white";
        $this->passive_task->textColor = "white";   
        $this->failed_task->textColor = "white";          
    }
    function AddTask()
    {
        $this->task_label->show();
        $this->icon_task->show();
        $this->quest_detail_btn->show();
        $this->time_quest_hm->show();
        $this->time_quest_date->show();        
        $this->step1->show();
        $this->step2->show();
    }
    function UpdateQuestTime()
    {
        $this->time_quest_hm->text = Time::now()->toString('HH:mm');
        $this->time_quest_date->text = Time::now()->toString('dd/MM/YYYY');
    }    
    function Step_UpdatePda()
    { 
        if ($GLOBALS['QuestCompleted'])
        {
           if ($this->form('maingame')->Pda->content->Pda_Statistic->visible)
           {
               if (ToggleHudFeature && $GLOBALS['HudVisible'] && NeedToCheckPDA) $this->form('maingame')->pda_icon->show();
           }
           else
           {
               if (ToggleHudFeature && $GLOBALS['HudVisible'] && NeedToCheckPDA) $this->form('maingame')->pda_icon->show();
               $this->form('maingame')->Pda->content->stat_label->graphic = new UXImageView(new UXImage('res://.data/ui/pda/mainbtn_new_icon.png'));                
           }            
        }
        else 
        {
           if ($this->form('maingame')->pda_icon->visible) $this->form('maingame')->pda_icon->hide();
           $this->form('maingame')->Pda->content->stat_label->graphic = new UXImageView(new UXImage('res://.data/ui/pda/mainbtn_icon.png'));              
        }
    }
    function Step_DeletePda()
    {
        if (ToggleHudFeature || $GLOBALS['HudVisible'] && NeedToCheckPDA)
        {
           $this->form('maingame')->pda_icon->hide();
           $this->form('maingame')->Pda->content->stat_label->graphic = new UXImageView(new UXImage('res://.data/ui/pda/mainbtn_icon.png'));
           
           $GLOBALS['NeedToCheckPDA'] = false;
        }
        else
        {
            if ($this->form('maingame')->pda_icon->visible)
            {
                $this->form('maingame')->pda_icon->hide();
                $this->form('maingame')->Pda->content->stat_label->graphic = new UXImageView(new UXImage('res://.data/ui/pda/mainbtn_icon.png'));
            }
        }
    }
    function Step1_Complete()
    {
        $this->step1->graphic = new UXImageView(new UXImage('res://.data/ui/pda/task_step_complete.png'));        
        
        if ($GLOBALS['AllSounds'])
        {
            Media::open('res://.data/audio/pda.mp3', 'pda_task');
        }    
    }
    function Step2_Complete()
    {
        $this->step2->graphic = new UXImageView(new UXImage('res://.data/ui/pda/task_step_complete.png'));
        $this->DeleteTask();   
        
        if ($GLOBALS['AllSounds'])
        {
            Media::open('res://.data/audio/pda.mp3', 'pda_task');
        }                
    }  
    function StepReset()
    {
        $this->step1->graphic = new UXImageView(new UXImage('res://.data/ui/pda/task_step_process.png'));    
        $this->step2->graphic = new UXImageView(new UXImage('res://.data/ui/pda/task_step_process.png'));    
    } 
    function Step2_Failed()
    {
        $this->step2->graphic = new UXImageView(new UXImage('res://.data/ui/pda/task_step_failed.png')); 
        $this->DeleteTask();  
        
        if ($GLOBALS['AllSounds'])
        {
            Media::open('res://.data/audio/pda.mp3', 'pda_task');
        }                           
    }
    function DeleteTask()
    {
        $this->task_label->hide();
        $this->icon_task->hide();
        $this->quest_detail_btn->hide(); 
        $this->task_detail_text->hide();  
        $this->quest_detail_btn->image = new UXImage('res://.data/ui/pda/task_detail_off.png');
        $this->quest_detail_btn->hoverImage = new UXImage('res://.data/ui/pda/task_detail_on.png'); 
        $this->quest_detail_btn->clickImage = new UXImage('res://.data/ui/pda/task_detail_on.png');        
        $this->time_quest_hm->hide();
        $this->time_quest_date->hide(); 
        $this->step1->hide();
        $this->step2->hide();                     
    }
}
