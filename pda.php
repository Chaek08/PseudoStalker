<?php
namespace app\forms;

use std, gui, framework, app;


class pda extends AbstractForm
{
    /**
     * @event pda_panel.click-Left 
     */
    function PanelPdaClick(UXMouseEvent $e = null)
    {    
        $this->DefaultState();                     
    }  
    /**
     * @event ranking_btn.click-Left 
     */
    function RankingBtn(UXMouseEvent $e = null)
    { 
        $this->DefaultState();    
        $this->fragment_ranking->show();  
        $this->pda_background->hide();                                             
    }
    /**
     * @event tasks_btn.click-Left 
     */
    function TasksBtn(UXMouseEvent $e = null)
    {  
        $this->DefaultState();    
        $this->fragment_tasks->show();
        $this->pda_background->hide();                                                                                             
    }   
    /**
     * @event contacts_btn.click-Left 
     */
    function ContactsBtn(UXMouseEvent $e = null)
    {   
        $this->DefaultState();     
        $this->fragment_contacts->show(); 
        $this->pda_background->hide();                                                                                
    } 
    /**
     * @event stat_btn.click-Left 
     */
    function StatisticBtn(UXMouseEvent $e = null)
    {    
        $this->DefaultState();
        $this->fragment_stat->show(); 
        $this->pda_background->hide();  
        if($this->form('maingame')->pda_icon->visible)
        {
            $this->form('maingame')->pda_icon->hide();
        }                                                                          
    }  
    function DefaultState()
    {
        $this->fragment_ranking->hide(); 
        $this->fragment_contacts->hide();
        $this->fragment_tasks->hide();
        $this->fragment_stat->hide();   
        $this->pda_background->show();         
    }      
}
