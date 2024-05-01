<?php
namespace app\forms;
use action\Element; 
use php\time\Time;

use std, gui, framework, app;


class pda_fragments_stat extends AbstractForm
{
    /**
     * @event show 
     */
    function InitRaiting(UXWindowEvent $e = null)
    {    
        $this->statistic_num->text = "9700\n999\n0\n\n10699";
    }
    /**
     * @event icon.click-2x 
     */
    function RedirectRaiting(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->fragment_pda->content->RankingBtn();
        $this->form('maingame')->fragment_pda->content->fragment_ranking->content->ActorinListBtn();        
    }
    function UpdateRaiting()
    {
        if ($this->form('maingame')->skull_enemy->visible)
        {
            $this->statistic_num->text = "10021\n1000\n1\n\n11022";  
            $this->form('maingame')->fragment_pda->content->fragment_ranking->content->actor_in_raiting->text = "1.                   Саня Бетон                                             11022";                           
        }     
        else 
        {
            $this->form('maingame')->fragment_pda->content->fragment_ranking->content->actor_in_raiting->text = "1.                   Саня Бетон                                             10699"; 
            $this->InitRaiting();           
        }  
    }
    function ActorFailText()
    {
        $this->tab_final->show();
        $this->final_label->show();
        $this->final_label->text = $this->a_fail->text;
    }
    function EnemyFailText()
    {
        $this->tab_final->show();
        $this->final_label->show();
        $this->final_label->text = $this->e_fail->text;     
    }
    function ResetFinalText()
    {
        $this->tab_final->hide();
        $this->final_label->hide();
        $this->final_label->text = "";
    }
    function DeathFilter()
    {
        if ($this->form('maingame')->skull_actor->visible)
        {
            $this->death_filter->show();
        }
        else 
        {
            $this->death_filter->hide();
        }   
    }
}
