<?php
namespace app\forms;
use app\forms\classes\UI\UIRoles;
use php\gui\UXImage;
use php\gui\UXImageView;
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
    
    function getCurrentLanguageFromUI()
    {
        return $this->form('Client')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value;
    }    
    
    function UpdateData()
    {
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());        

        $charInfo = new UICharacterInfo($this, $this->localization, $this->icon, $this->rank, $this->null, $this->community, $this->null, $this->tab_button, $this->reputation);
        $charInfo->setActor();
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
        $this->form('Client')->Pda->content->UpdateBtnColor();
        $this->form('Client')->Pda->content->ranks_label->textColor = '#d59b30';
    
        $this->form('Client')->Pda->content->RankingBtn();
        
        $this->form('Client')->Pda->content->Pda_Ranking->content->ResetBtnColor();
        foreach (['actor_in_raiting_pos', 'actor_in_raiting_name', 'actor_in_raiting_rank'] as $labelName)
        {
            $this->form('Client')->Pda->content->Pda_Ranking->content->{$labelName}->textColor = '#cccccc';
        }
        $this->form('Client')->Pda->content->Pda_Ranking->content->ActorInListBtn();
    }
    function UpdateRaiting()
    {
        if ($GLOBALS['EnemyFailed'])
        {
            $this->statistic_num->text = "10021\n1000\n1\n\n11022";  
            $this->form('Client')->Pda->content->Pda_Ranking->content->actor_in_raiting_rank->text = "11022";                           
        }
        if ($GLOBALS['ActorFailed'])
        {
            $this->form('Client')->Pda->content->Pda_Ranking->content->goblindav_in_raiting_rank->text = "301";
        }
        if (!$GLOBALS['QuestCompleted'])
        {
            $this->InitRaiting();
            
            $this->form('Client')->Pda->content->Pda_Ranking->content->actor_in_raiting_rank->text = "10699";
            $this->form('Client')->Pda->content->Pda_Ranking->content->goblindav_in_raiting_rank->text = "228";           
        }
    }
    function UpdateFinalLabel()
    {
        $this->tab_final->hide();
        $this->final_label->hide();
        $this->final_label->text = null;
        
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());        
        
        if ($GLOBALS['ActorFailed'])
        {
            $this->tab_final->show();
            $this->final_label->show();
            $this->final_label->text = $this->localization->get('FinalLabel_ActorFail');
        }
        if ($GLOBALS['EnemyFailed'])
        {
            $this->tab_final->show();
            $this->final_label->show();
            $this->final_label->text = $this->localization->get('FinalLabel_EnemyFail');
        }
    }
}
