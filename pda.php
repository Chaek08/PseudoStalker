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

    function DefaultState()
    {
        $this->Pda_Ranking->hide(); 
        $this->Pda_Contacts->hide();
        $this->Pda_Tasks->hide();
        $this->Pda_Statistic->hide();
        
        $this->Pda_Background->show();
            
        $this->form('maingame')->Pda->content->Pda_Ranking->content->HideUserInfo();    
        $this->form('maingame')->Pda->content->Pda_Tasks->content->ClearDetailTask();  
        $this->form('maingame')->Pda->content->Pda_Contacts->content->HideCharacter();                 
    } 
    /**
     * @event ranking_btn.click-Left 
     */
    function RankingBtn(UXMouseEvent $e = null)
    { 
        if (!$this->Pda_Ranking->visible) $this->DefaultState();
        
        $this->Pda_Ranking->content->UpdateData();
        $this->Pda_Ranking->show();
        
        $this->Pda_Background->hide();                              
    }
    /**
     * @event tasks_btn.click-Left 
     */
    function TasksBtn(UXMouseEvent $e = null)
    {  
        if (!$this->Pda_Tasks->visible) $this->DefaultState();
        
        $this->Pda_Tasks->content->UpdateData();
        $this->Pda_Tasks->show();
        
        $this->Pda_Background->hide();                                                                                             
    }   
    /**
     * @event contacts_btn.click-Left 
     */
    function ContactsBtn(UXMouseEvent $e = null)
    {   
        if (!$this->Pda_Contacts->visible) $this->DefaultState();      
        
        $this->Pda_Contacts->content->UpdateData();
        $this->Pda_Contacts->show();
        
        $this->Pda_Background->hide();                                                                                
    } 
    /**
     * @event stat_btn.click-Left 
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
