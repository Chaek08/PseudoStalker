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
    function UpdateRaiting()
    {
        if ($this->form('maingame')->skull_enemy->visible)
        {
            $this->statistic_num->text = "10021\n1000\n1\n\n11022";  
            $this->form('maingame')->fragment_pda->content->fragment_ranking->content->actor_in_raiting->text = "1.                   Саня Кабан                                               11022";                           
        }     
        else 
        {
            $this->form('maingame')->fragment_pda->content->fragment_ranking->content->actor_in_raiting->text = "1.                   Саня Кабан                                               10699"; 
            $this->InitRaiting();           
        }  
    }
    function ActorFailText()
    {
        $this->tab_final->show();
        $this->final_label->show();
        $this->final_label->text = "Я проиграл... Алекс остался жив.. Теперь возможно он заразит Kosta Kruta!!!";
    }
    function EnemyFailText()
    {
        $this->tab_final->show();
        $this->final_label->show();
        $this->final_label->text =
"Вот я и победил алекса, во время боевого контакта он вколол мне укол, на котором было написано 'Гомосекоптомин'
Теперь, кажись, Kosta Krut со мной будет сражаться... А пока иду в местную больницу, колоть противоядие
Чуствую себя как мусор..";        
    }
    function ResetFinalText()
    {
        $this->tab_final->hide();
        $this->final_label->hide();
        $this->final_label->text = "";
    }
}
