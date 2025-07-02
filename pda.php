<?php
namespace app\forms;

use std, gui, framework, app;


class pda extends AbstractForm
{
    public $SDK_ActorName = '';
    public $SDK_ActorIcon = '';
    public $SDK_ActorBio = '';    
    public $SDK_EnemyName = '';
    public $SDK_EnemyIcon = '';
    public $SDK_EnemyBio = '';
    public $SDK_ValerokName = '';
    public $SDK_ValerokIcon = '';
    public $SDK_ValerokBio = '';
    
    public $SDK_DeRoleName = '';
    public $SDK_DeRoleColor = '';
    public $SDK_DeRoleIcon = '';
    public $SDK_LaRoleName = '';
    public $SDK_LaRoleColor = '';
    public $SDK_LaRoleIcon = '';
    public $SDK_PidoRoleName = '';
    public $SDK_PidoRoleColor = '';
    public $SDK_PidoRoleIcon = '';    
    
    public function __construct() 
    {
        parent::__construct();
    
        $this->time_year->watchMaker->format = 'dd/MM/yyyy';
    }
    function InitPDA()
    {
        $buttons = [
            'tasks_label',
            'contacts_label',
            'ranks_label',
            'stat_label'
        ];

        $this->activePressedLabel = null;

        foreach ($buttons as $btnName)
        {
            $label = $this->{$btnName};

            $label->on("mouseEnter", function($e) use ($label) {
                if ($label->textColor != "#d59b30")
                {
                    $label->textColor = "white";
                }
            });

            $label->on("mouseExit", function($e) use ($label) {
                if ($label->textColor != "#d59b30")
                {
                    $label->textColor = "#777778";
                }
            });

            $label->on("mouseDown", function($e) use ($label, $btnName) {
                $this->activePressedLabel = $btnName;
            });
        }

        $this->on("mouseUp", function($e) use ($buttons) {
            if ($this->activePressedLabel != null)
            {
                $btnName = $this->activePressedLabel;
                $label = $this->{$btnName};

                if ($label->hover)
                {
                    $this->UpdateBtnColor();
                    $label->textColor = "#d59b30";

                    switch ($btnName)
                    {
                        case 'tasks_label':
                            $this->TasksBtn();
                            break;
                        case 'contacts_label':
                            $this->ContactsBtn();
                            break;
                        case 'ranks_label':
                            $this->RankingBtn();
                            break;
                        case 'stat_label':
                            $this->StatisticBtn();
                            break;
                    }
                }
                else
                {
                    if ($label->textColor != "#d59b30")
                    {
                        $label->textColor = "#777778";
                    }
                }

                $this->activePressedLabel = null;
            }
        });
    }
    function DefaultState()
    {
        $this->Pda_Ranking->hide(); 
        $this->Pda_Contacts->hide();
        $this->Pda_Tasks->hide();
        $this->Pda_Statistic->hide();
        
        if (!$this->form('maingame')->Pda->visible) $this->UpdateBtnColor();
        
        $this->Pda_Background->show();
            
        $this->form('maingame')->Pda->content->Pda_Ranking->content->HideUserInfo();
        $this->form('maingame')->Pda->content->Pda_Tasks->content->ClearDetailTask();
        $this->form('maingame')->Pda->content->Pda_Contacts->content->HideCharacter();
    }
    function UpdateBtnColor()
    {
        foreach (['tasks_label', 'contacts_label', 'ranks_label', 'stat_label'] as $btnName)
        {
            $this->{$btnName}->textColor = '#777778';
        }
    }    
    /**
     * @event ranks_label.click-Left 
     */
    function RankingBtn(UXMouseEvent $e = null)
    { 
        if (!$this->Pda_Ranking->visible) $this->DefaultState();
               
        $this->Pda_Ranking->content->UpdateData();
        $this->Pda_Ranking->show();
        
        $this->Pda_Background->hide();                              
    }
    /**
     * @event tasks_label.click-Left 
     */
    function TasksBtn(UXMouseEvent $e = null)
    {  
        if (!$this->Pda_Tasks->visible) $this->DefaultState();
              
        $this->Pda_Tasks->content->UpdateData();
        
        $this->Pda_Tasks->content->ResetBtnColor();
        $this->Pda_Tasks->content->active_task->textColor = '#d59b30';        
        
        if (!$GLOBALS['QuestCompleted']) 
        {
            $this->Pda_Tasks->content->ShowActiveTasks();
        }
        if ($GLOBALS['QuestCompleted'] && $GLOBALS['ActorFailed'])
        {
            $this->Pda_Tasks->content->ShowFailedTasks();
        }
        if ($GLOBALS['QuestCompleted'] && $GLOBALS['EnemyFailed'])
        {
            $this->Pda_Tasks->content->ShowPassiveTasks();
        }        
        
        $this->Pda_Tasks->show();
        
        $this->Pda_Background->hide();                                                                                             
    }   
    /**
     * @event contacts_label.click-Left 
     */
    function ContactsBtn(UXMouseEvent $e = null)
    {   
        if (!$this->Pda_Contacts->visible) $this->DefaultState();     
        
        $this->Pda_Contacts->content->UpdateData();
        $this->Pda_Contacts->show();
        
        $this->Pda_Background->hide();                                                                                
    } 
    /**
     * @event stat_label.click-Left 
     */
    function StatisticBtn(UXMouseEvent $e = null)
    {    
        if (!$this->Pda_Statistic->visible) $this->DefaultState();     
        
        $this->Pda_Statistic->content->UpdateData();
        $this->Pda_Statistic->show();
                
        $this->Pda_Background->hide();
        
        $this->Pda_Ranking->content->DeathFilter();
        $this->form('maingame')->Pda->content->Pda_Tasks->content->Step_DeletePda();
    }  
}
