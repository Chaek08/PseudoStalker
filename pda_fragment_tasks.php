<?php
namespace app\forms;

use php\gui\UXImage;
use std, gui, framework, app;
use php\time\Time;

class pda_fragment_tasks extends AbstractForm
{
    private $localization;

    public function __construct() 
    {
        parent::__construct();

        $this->localization = new Localization($language);
        
        $this->UpdateQuestTime();
    }
    
    function getCurrentLanguageFromUI()
    {
        return $this->form('Client')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value;
    }    
    
    public $SDK_QuestName;
    public $SDK_QuestIcon;
    public $SDK_QuestDesc;
    public $SDK_QuestStep1;
    public $SDK_QuestStep2;
    public $SDK_QuestTarget;
    
    function InitTasks()
    {
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());  
          
        $buttons = [
            'active_task'  => $this->localization->get('ActiveTaskTooltip'),
            'passive_task' => $this->localization->get('PassiveTaskTooltip'),
            'failed_task'  => $this->localization->get('FailedTaskTooltip'),
            'quest_detail_btn' =>  $this->localization->get('TaskDetailTooltip')           
        ];
    
        $this->activePressedTaskLabel = null;
    
        foreach ($buttons as $btnName => $tooltipText)
        {
            $label = $this->{$btnName};
    
            $tooltip = new CustomTooltip($this->form('Client'));
            $tooltip->setText($tooltipText);
    
            $label->on('mouseEnter', function($e) use ($tooltip, $label) {
                if ($tooltip->showTimer)
                { 
                    $tooltip->showTimer->cancel(); 
                    $tooltip->showTimer = null; 
                }
                $tooltip->showTimer = Timer::after($tooltip->delayMs, function () use ($tooltip) {
                    uiLater(function () use ($tooltip) {
                        $tooltip->repositionAtCursor();
                        $tooltip->show();
                    });
                });
    
                if ($label->textColor != "#d59b30")
                {
                    $label->textColor = "white";
                }
            });
    
            $label->on('mouseExit', function($e) use ($tooltip, $label) {
                if ($tooltip->showTimer)
                { 
                    $tooltip->showTimer->cancel(); 
                    $tooltip->showTimer = null; 
                }
                $tooltip->hide();
    
                if ($label->textColor != "#d59b30") {
                    $label->textColor = "#777778";
                }
            });
    
            $label->on('mouseMove', function($e) use ($tooltip) {
                if ($tooltip->visible)
                {
                    $tooltip->repositionAtCursor();
                }
            });
    
            $label->on("mouseDown", function($e) use ($btnName) {
                $this->activePressedTaskLabel = $btnName;
            });
        }
    
        $this->on("mouseUp", function($e) use ($buttons) {
            if ($this->activePressedTaskLabel != null)
            {
                $btnName = $this->activePressedTaskLabel;
                $label = $this->{$btnName};
    
                if ($label->hover)
                {
                    $this->ResetBtnColor();
                    $label->textColor = "#d59b30";
    
                    switch ($btnName)
                    {
                        case 'active_task':
                            $this->ShowActiveTasks();
                            break;
                        case 'passive_task':
                            $this->ShowPassiveTasks();
                            break;
                        case 'failed_task':
                            $this->ShowFailedTasks();
                            break;
                    }
                }
                else if ($label->textColor != "#d59b30")
                {
                    $label->textColor = "#777778";
                }
    
                $this->activePressedTaskLabel = null;
            }
        });
    }

    function UpdateData()
    {
        $quest_name = trim($this->SDK_QuestName);
        $quest_icon = trim($this->SDK_QuestIcon);
        $quest_desc = trim($this->SDK_QuestDesc);
        $quest_step1 = trim($this->SDK_QuestStep1);
        $quest_step2 = trim($this->SDK_QuestStep2);
        $quest_target = trim($this->SDK_QuestTarget);
        
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());        
        
        $this->task_label->text = $quest_name != '' ? $quest_name : $this->localization->get('DefeatEnemy_Task');
        $this->icon_task->image = new UXImage($quest_icon != '' ? $quest_icon : 'res://.data/ui/pda/icon_Task.png');
        $this->task_detail_text->text = $quest_desc != '' ? $quest_desc : $this->localization->get('TaskDetails');
        $this->step1->text = $quest_step1 != '' ? $quest_step1 : $this->localization->get('TalkToGoblin_Task');
        $this->step2->text = $quest_step2 != '' ? $quest_step2 : $this->localization->get('DefeatGoblin_Task');
        $this->form('Client')->Pda->content->Pda_Statistic->content->target_label->text = $quest_target != '' ? $quest_target : $this->localization->get('Target_Label');
        
        if (!$GLOBALS['QuestStep1']) $this->form('Client')->MainGame->content->Task_Step_Label->text = $quest_step1 != '' ? $quest_step1 : $this->localization->get('TalkToGoblin_Task');
        if ($GLOBALS['QuestStep1'] && !$GLOBALS['QuestCompleted']) $this->form('Client')->MainGame->content->Task_Step_Label->text = $quest_step2 != '' ? $quest_step2 : $this->localization->get('DefeatGoblin_Task');
        
        $this->form('Client')->MainGame->content->MessageBox->content->Task_Name->text = $quest_name != '' ? $quest_name : $this->localization->get('DefeatEnemy_Task');
        $this->form('Client')->MainGame->content->MessageBox->content->Icon->image = new UXImage($quest_icon != '' ? $quest_icon : 'res://.data/ui/pda/icon_Task.png');
    }
    /**
    * @event quest_detail_btn.click-Left 
     */
    function DetailTask(UXMouseEvent $e = null)
    {
        if ($this->task_detail_text->visible)
        {
            $this->task_detail_text->hide();
            $this->tab_detail->text = null;
            
            $this->quest_detail_btn->image = new UXImage('res://.data/ui/pda/task_detail_off.png');
            $this->quest_detail_btn->hoverImage = new UXImage('res://.data/ui/pda/task_detail_on.png');
            $this->quest_detail_btn->clickImage = new UXImage('res://.data/ui/pda/task_detail_on.png');
        }
        else
        {
            $this->task_detail_text->show();
            
            $this->localization->setLanguage($this->getCurrentLanguageFromUI());
            
            $this->tab_detail->text = $this->localization->get('TabTaskDetail');

            $this->quest_detail_btn->image = new UXImage('res://.data/ui/pda/task_detail_opened.png');
            $this->quest_detail_btn->hoverImage = new UXImage('res://.data/ui/pda/task_detail_opened.png');
            $this->quest_detail_btn->clickImage = new UXImage('res://.data/ui/pda/task_detail_opened.png');
        }
    }
    /**
     * @event frame_hide.click-Left 
     */
    function ClearDetailTask(UXMouseEvent $e = null) //заебись, сделал один фрейм на весь экран и тем самым сократил количество функций :like
    {               
        if (!$this->task_detail_text->visible) return;

        $this->task_detail_text->hide();
        $this->tab_detail->text = null;
        
        $this->quest_detail_btn->image = new UXImage('res://.data/ui/pda/task_detail_off.png');
        $this->quest_detail_btn->hoverImage = new UXImage('res://.data/ui/pda/task_detail_on.png'); 
        $this->quest_detail_btn->clickImage = new UXImage('res://.data/ui/pda/task_detail_on.png');
    }
    /**
     * @event active_task.click-Left 
     */
    function ShowActiveTasks(UXMouseEvent $e = null)
    {    
        $GLOBALS['QuestCompleted'] ? $this->DeleteTask() : $this->AddTask();
    }
    /**
     * @event passive_task.click-Left 
     */
    function ShowPassiveTasks(UXMouseEvent $e = null)
    {    
        
        $GLOBALS['EnemyFailed'] ? $this->AddTask() : $this->DeleteTask();
    }
    /**
     * @event failed_task.click-Left 
     */
    function ShowFailedTasks(UXMouseEvent $e = null)
    {    
        
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
        foreach (['active_task', 'passive_task', 'failed_task'] as $btn)
        {
            $this->{$btn}->textColor = "#777778";
        }      
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
           if ($this->form('Client')->Pda->content->Pda_Statistic->visible)
           {
               if ($GLOBALS['NeedToCheckPDA'] && $GLOBALS['HudVisible']) $this->form('Client')->MainGame->content->pda_icon->show();
           }
           else
           {
               if ($GLOBALS['NeedToCheckPDA'] && $GLOBALS['HudVisible']) $this->form('Client')->MainGame->content->pda_icon->show();
               $this->form('Client')->Pda->content->stat_label->graphic = new UXImageView(new UXImage('res://.data/ui/pda/mainbtn_new_icon.png')); 
           }     
        }
        else 
        {
           if ($GLOBALS['NeedToCheckPDA'] && $GLOBALS['HudVisible']) $this->form('Client')->MainGame->content->pda_icon->hide();
           $this->form('Client')->Pda->content->stat_label->graphic = new UXImageView(new UXImage('res://.data/ui/pda/mainbtn_icon.png'));              
        }
    }
    function Step_DeletePda()
    {
        $GLOBALS['NeedToCheckPDA'] = false;
        
        $this->form('Client')->Pda->content->stat_label->graphic = new UXImageView(new UXImage('res://.data/ui/pda/mainbtn_icon.png'));
        
        if ($GLOBALS['HudVisible'])
        {
           $this->form('Client')->MainGame->content->pda_icon->hide();
        }
    }
    function Step1_Complete()
    {
        $this->step1->graphic = new UXImageView(new UXImage('res://.data/ui/pda/task_step_complete.png'));        
        
        if ($GLOBALS['AllSounds'])
        {
            $this->form('Client')->playSoundAsync('res://.data/audio/pda.mp3', 'pda_task');
        }
        $GLOBALS['Task_Status_Update'] = true;
        $this->form('Client')->MainGame->content->ShowMessageBox();
        
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
        
        $this->form('Client')->MainGame->content->Task_Step_Label->text = $quest_step2 != '' ? $quest_step2 : $this->localization->get('DefeatGoblin_Task');
        $this->form('Client')->MainGame->content->ShowTaskStep();
        
        $GLOBALS['QuestStep1'] = true;
    }
    function Step2_Complete()
    {
        $this->step2->graphic = new UXImageView(new UXImage('res://.data/ui/pda/task_step_complete.png'));
        $this->DeleteTask();
        
        $this->form('Client')->Pda->content->Pda_Contacts->content->UpdateContacts();
        
        if ($GLOBALS['AllSounds'])
        {
            $this->form('Client')->playSoundAsync('res://.data/audio/pda.mp3', 'pda_task');
        }
        $GLOBALS['Task_Status_Update'] = true;
        $this->form('Client')->MainGame->content->ShowMessageBox();
        $this->form('Client')->MainGame->content->Task_Step_Label->text = $quest_step2 != '' ? $quest_step2 : $this->localization->get('DefeatEnemy_Task');        
        
        $GLOBALS['QuestCompleted'] = true;
        
        $this->form('Client')->MainGame->content->ShowTaskStep();
    }   
    function Step2_Failed()
    {
        $this->step2->graphic = new UXImageView(new UXImage('res://.data/ui/pda/task_step_failed.png')); 
        $this->DeleteTask();  
        
        if ($GLOBALS['AllSounds'])
        {
            $this->form('Client')->playSoundAsync('res://.data/audio/pda.mp3', 'pda_task');
        }
        $GLOBALS['Task_Status_Failed'] = true;
        $this->form('Client')->MainGame->content->ShowMessageBox();
        $this->form('Client')->MainGame->content->Task_Step_Label->text = $quest_name != '' ? $quest_name : $this->localization->get('DefeatEnemy_Task');        

        $GLOBALS['QuestCompleted'] = true;    //технически выполнен пусть и завален нахуй
        
        $this->form('Client')->MainGame->content->ShowTaskStep();
    }
    function StepReset()
    {
        $this->step1->graphic = new UXImageView(new UXImage('res://.data/ui/pda/task_step_process.png'));    
        $this->step2->graphic = new UXImageView(new UXImage('res://.data/ui/pda/task_step_process.png'));    
    }    
    function DeleteTask()
    {
        $this->task_label->hide();
        $this->icon_task->hide();
        $this->quest_detail_btn->hide(); 
        $this->task_detail_text->hide();  
        $this->time_quest_hm->hide();
        $this->time_quest_date->hide();
        $this->step1->hide();
        $this->step2->hide();
        $this->tab_detail->text = null;
        
        $this->quest_detail_btn->image = new UXImage('res://.data/ui/pda/task_detail_off.png');
        $this->quest_detail_btn->hoverImage = new UXImage('res://.data/ui/pda/task_detail_on.png'); 
        $this->quest_detail_btn->clickImage = new UXImage('res://.data/ui/pda/task_detail_on.png');        
                     
    }
}
