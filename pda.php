<?php
namespace app\forms;

use std, gui, framework, app;


class pda extends AbstractForm
{
    /**
     * @event toolbar_frame_ranking.click-Left 
     */
    function RankingBtn(UXMouseEvent $e = null)
    { 
        $this->fragment_ranking->show();
        $this->fragment_contacts->hide();
        $this->fragment_tasks->hide();
        $this->fragment_stat->hide();   
        $this->pda_background->hide();                                             
    }
    /**
     * @event toolbar_frame_tasks.click-Left 
     */
    function TasksBtn(UXMouseEvent $e = null)
    {  
        $this->fragment_tasks->show();
        $this->fragment_ranking->hide();
        $this->fragment_contacts->hide();  
        $this->fragment_stat->hide(); 
        $this->pda_background->hide();                                                                                             
    }   
    /**
     * @event toolbar_frame_contacts.click-Left 
     */
    function ContactsBtn(UXMouseEvent $e = null)
    {    
        $this->fragment_contacts->show();
        $this->fragment_ranking->hide();
        $this->fragment_stat->hide(); 
        $this->fragment_tasks->hide();  
        $this->pda_background->hide();                                                                                
    } 
    /**
     * @event toolbar_frame_statistic.click-Left 
     */
    function StatisticBtn(UXMouseEvent $e = null)
    {    
        $this->fragment_stat->show();
        $this->fragment_ranking->hide();
        $this->fragment_contacts->hide(); 
        $this->fragment_tasks->hide();  
        $this->pda_background->hide();                                                                            
    }
    /**
     * @event pda_panel.click-Left 
     */
    function PanelPdaClick(UXMouseEvent $e = null)
    {    
        $this->fragment_ranking->hide(); 
        $this->fragment_contacts->hide();
        $this->fragment_tasks->hide();
        $this->fragment_stat->hide();   
        $this->pda_background->show();                     
    }
            
}
