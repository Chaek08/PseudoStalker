<?php
namespace app\forms;
use action\Element; 
use php\time\Time;

use std, gui, framework, app;
use app\forms\classes\Localization;

class pda_fragments_stat extends AbstractForm
{
    private $localization;

    public function __construct() 
    {
        parent::__construct();

        $this->localization = new Localization($language);
    }
    
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
        $this->form('maingame')->Pda->content->RankingBtn();
        $this->form('maingame')->Pda->content->Pda_Ranking->content->ActorInListBtn();        
    }
    function UpdateRaiting()
    {
        if ($GLOBALS['EnemyFailed'])
        {
            $this->statistic_num->text = "10021\n1000\n1\n\n11022";  
            $this->form('maingame')->Pda->content->Pda_Ranking->content->actor_in_raiting_rank->text = "11022";                           
        }
        if ($GLOBALS['ActorFailed'])
        {
            $this->form('maingame')->Pda->content->Pda_Ranking->content->goblindav_in_raiting_rank->text = "301";
        }
        if (!$GLOBALS['QuestCompleted'])
        {
            $this->InitRaiting();
            
            $this->form('maingame')->Pda->content->Pda_Ranking->content->actor_in_raiting_rank->text = "10699";
            $this->form('maingame')->Pda->content->Pda_Ranking->content->goblindav_in_raiting_rank->text = "228";           
        }
    }
    function ActorFailText()
    {
        $this->localization->setLanguage($this->form('maingame')->Options->content->Language_Switcher_Combobobx->value);
        $this->tab_final->show();
        $this->final_label->show();
        $this->final_label->text = $this->localization->get('FinalLabel_ActorFail');
    }
    function EnemyFailText()
    {
        $this->localization->setLanguage($this->form('maingame')->Options->content->Language_Switcher_Combobobx->value);
        $this->tab_final->show();
        $this->final_label->show();
        $this->final_label->text = $this->localization->get('FinalLabel_EnemyFail');
    }
    function ResetFinalText()
    {
        $this->tab_final->hide();
        $this->final_label->hide();
        $this->final_label->text = "";
    }
}
