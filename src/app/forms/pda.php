<?php
namespace app\forms;

use std, gui, framework, app;
use app\forms\classes\Localization;

class pda extends AbstractForm
{
    public $SDK_ActorName;
    public $SDK_ActorIcon;
    public $SDK_ActorBio;    
    public $SDK_EnemyName;
    public $SDK_EnemyIcon;
    public $SDK_EnemyBio;
    public $SDK_ValerokName;
    public $SDK_ValerokIcon;
    public $SDK_ValerokBio;
    public $SDK_DanilaName;
    public $SDK_DanilaIcon;
    public $SDK_DanilaBio;    
    
    public $SDK_DeRoleName;
    public $SDK_DeRoleColor;
    public $SDK_DeRoleIcon;
    public $SDK_LaRoleName;
    public $SDK_LaRoleColor;
    public $SDK_LaRoleIcon;
    public $SDK_PidoRoleName;
    public $SDK_PidoRoleColor;
    public $SDK_PidoRoleIcon;    
    
    public function __construct() 
    {
        parent::__construct();
        
        $this->time_year->watchMaker->format = 'dd/MM/YYYY';
    }
    
    function InitPDA()
    {
        $buttons = [
            'tasks_label'    => Localization::get('TaskLabelTooltip'),
            'contacts_label' => Localization::get('ContactsLabelTooltip'),
            'ranks_label'    => Localization::get('RanksLabelTooltip'),
            'stat_label'     => Localization::get('StatLabelTooltip')
        ];
    
        foreach ($buttons as $btnName => $tooltipText)
        {
            $label = $this->{$btnName};
        
            $label->classes[] = 'pda-tab';
        
            $tooltip = new CustomTooltip($this->form('Client'));
            $tooltip->setText($tooltipText);
            $tooltip->attachTo($label);
        }
        
        $this->Pda_Tasks->content->InitTasks();
    }
    
    function setActivePdaTab($name)
    {
        foreach (['tasks_label', 'contacts_label', 'ranks_label', 'stat_label'] as $btn)
        {
            $this->{$btn}->style = null;
        }
    
        $this->{$name}->style = '-fx-text-fill: #d59b30;';
    }

    function DefaultState()
    {
        $this->Pda_Ranking->hide(); 
        $this->Pda_Contacts->hide();
        $this->Pda_Tasks->hide();
        $this->Pda_Statistic->hide();
        
        $this->Pda_Background->show();
            
        $this->form('Client')->Pda->content->Pda_Ranking->content->HideUserInfo();
        $this->form('Client')->Pda->content->Pda_Tasks->content->ClearDetailTask();
        $this->form('Client')->Pda->content->Pda_Contacts->content->HideCharacter();
    }

    /**
     * @event ranks_label.click-Left 
     */
    function RankingBtn(UXMouseEvent $e = null)
    { 
        if (!$this->Pda_Ranking->visible) $this->DefaultState();
               
        $this->setActivePdaTab('ranks_label');
               
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
              
        $this->setActivePdaTab('tasks_label');
         
        $this->Pda_Tasks->content->UpdateData();
                
        if (!$GLOBALS['QuestCompleted']) 
        {
            $this->Pda_Tasks->content->ShowActiveTasks();
        }
        if ($GLOBALS['QuestCompleted'] && $this->form('Client')->MainGame->content->GameActor->isDead())
        {
            $this->Pda_Tasks->content->ShowFailedTasks();
        }
        if ($GLOBALS['QuestCompleted'] && $this->form('Client')->MainGame->content->GameEnemy->isDead())
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
        
        $this->setActivePdaTab('contacts_label');
        
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
        
        $this->setActivePdaTab('stat_label');
        
        $this->Pda_Statistic->content->UpdateData();
        $this->Pda_Statistic->show();
                
        $this->Pda_Background->hide();
        
        $this->Pda_Statistic->content->InitRaiting();
        $this->Pda_Ranking->content->DeathFilterManager();
        $this->Pda_Tasks->content->Step_DeletePda();
    }  
}
