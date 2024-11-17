<?php
namespace app\forms;

use std, gui, framework, app;


class pda extends AbstractForm
{
    /**
     * @event ranking_btn.click-Left 
     */
    function RankingBtn(UXMouseEvent $e = null)
    { 
        if ($this->Pda_Ranking->visible) {} else 
        {
            $this->DefaultState(); 
        }    
        
        $this->Pda_Ranking->show();  
        $this->pda_background->hide();                                             
    }
    /**
     * @event tasks_btn.click-Left 
     */
    function TasksBtn(UXMouseEvent $e = null)
    {  
        if ($this->Pda_Tasks->visible) {} else 
        {
            $this->DefaultState(); 
        }     
        
        $this->Pda_Tasks->show();
        $this->pda_background->hide();                                                                                             
    }   
    /**
     * @event contacts_btn.click-Left 
     */
    function ContactsBtn(UXMouseEvent $e = null)
    {   
        if ($this->Pda_Contacts->visible) {} else 
        {
            $this->DefaultState(); 
        }      
        
        $this->Pda_Contacts->show(); 
        $this->pda_background->hide();                                                                                
    } 
    /**
     * @event stat_btn.click-Left 
     */
    function StatisticBtn(UXMouseEvent $e = null)
    {    
        if ($this->Pda_Statistic->visible) {} else 
        {
            $this->DefaultState();
        } 
        
        $this->Pda_Statistic->show(); 
        $this->pda_background->hide();  
        $this->form('maingame')->Pda->content->Pda_Tasks->content->Step_DeletePda();                                                                  
    }  
    function DefaultState()
    {
        $this->Pda_Ranking->hide(); 
        $this->Pda_Contacts->hide();
        $this->Pda_Tasks->hide();
        $this->Pda_Statistic->hide();   
        $this->pda_background->show();
            
        $this->form('maingame')->Pda->content->Pda_Ranking->content->HideUserInfo();    
        $this->form('maingame')->Pda->content->Pda_Tasks->content->ClearDetailTask();  
        $this->form('maingame')->Pda->content->Pda_Contacts->content->HideCharacter();                 
    }
    function SetPDAOpacity()
    {
        $this->tasks_label->opacity = 100;
        $this->contacts_label->opacity = 100;
        $this->ranks_label->opacity = 100;
        $this->stat_label->opacity = 100;                    
    }     
}
