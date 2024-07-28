<?php
namespace app\forms;

use std, gui, framework, app;


class pda extends AbstractForm
{
    /**
     * @event show 
     */
    function InitPda(UXWindowEvent $e = null)
    {
        $this->SetOpacity();
    }
    /**
     * @event ranking_btn.click-Left 
     */
    function RankingBtn(UXMouseEvent $e = null)
    { 
        if ($this->fragment_ranking->visible) {} else 
        {
            $this->DefaultState(); 
        }    
        
        $this->fragment_ranking->show();  
        $this->pda_background->hide();                                             
    }
    /**
     * @event tasks_btn.click-Left 
     */
    function TasksBtn(UXMouseEvent $e = null)
    {  
        if ($this->fragment_tasks->visible) {} else 
        {
            $this->DefaultState(); 
        }     
        
        $this->fragment_tasks->show();
        $this->pda_background->hide();                                                                                             
    }   
    /**
     * @event contacts_btn.click-Left 
     */
    function ContactsBtn(UXMouseEvent $e = null)
    {   
        if ($this->fragment_contacts->visible) {} else 
        {
            $this->DefaultState(); 
        }      
        
        $this->fragment_contacts->show(); 
        $this->pda_background->hide();                                                                                
    } 
    /**
     * @event stat_btn.click-Left 
     */
    function StatisticBtn(UXMouseEvent $e = null)
    {    
        if ($this->fragment_stat->visible) {} else 
        {
            $this->DefaultState(); 
        } 
        
        $this->fragment_stat->show(); 
        $this->pda_background->hide();  
        $this->form('maingame')->fragment_pda->content->fragment_tasks->content->Step_DeletePda();                                                                  
    }  
    function DefaultState()
    {
        $this->fragment_ranking->hide(); 
        $this->fragment_contacts->hide();
        $this->fragment_tasks->hide();
        $this->fragment_stat->hide();   
        $this->pda_background->show();
            
        $this->form('maingame')->fragment_pda->content->fragment_ranking->content->HideUserInfo();    
        $this->form('maingame')->fragment_pda->content->fragment_tasks->content->ClearDetailTask();  
        $this->form('maingame')->fragment_pda->content->fragment_contacts->content->HideCharacter();                 
    }
    function SetOpacity()
    {
        if ($this->form('maingame')->fragment_pda->visible)
        {
            $this->tasks_label->opacity = 100;
            $this->contacts_label->opacity = 100;
            $this->ranks_label->opacity = 100;
            $this->stat_label->opacity = 100;            
        }
        
        $this->fragment_contacts->opacity = 100;
        $this->fragment_ranking->opacity = 100;
        $this->fragment_stat->opacity = 100;
        $this->fragment_tasks->opacity = 100;        
    }     
}
