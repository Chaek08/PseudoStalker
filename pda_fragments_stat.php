<?php
namespace app\forms;
use action\Element; 
use php\time\Time;

use std, gui, framework, app;


class pda_fragments_stat extends AbstractForm
{
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
