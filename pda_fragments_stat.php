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
    
    private $actorCharacterInfo;
    private $enemyCharacterInfo;
    
    public function __construct() 
    {
        parent::__construct();

        $this->localization = new Localization($language);
        
        uiLater(function () {
            $this->localization->setLanguage($this->getCurrentLanguageFromUI());
            
            $this->actorCharacterInfo = new UICharacterInfo($this, $this->localization, $this->icon, $this->rank, $this->null, $this->community, $this->null, $this->tab_button, $this->reputation); 
            $this->actorCharacterInfo->setActor();
                    
            $this->enemyCharacterInfo = new UICharacterInfo($this, $this->localization, $this->icon_enemy, $this->rank_enemy, null, $this->community_enemy, null, $this->enemy_name);
            $this->enemyCharacterInfo->setEnemy();
        });
    }
    
    function UpdateData()
    {
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());        
    
        $this->actorCharacterInfo->setActor();
        $this->enemyCharacterInfo->setEnemy();
    }
        
    function getCurrentLanguageFromUI()
    {
        return $this->form('Client')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value;
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
            //$this->statistic_num->text = "10021\n1000\n1\n\n11022";  
            
            $this->actorCharacterInfo->addRank(1500); 
            $this->form('Client')->Pda->content->Pda_Ranking->content->actor_in_raiting_rank->text = $this->actorCharacterInfo->getRankValue();     
        }
        if ($GLOBALS['ActorFailed'])
        {
            $this->enemyCharacterInfo->addRank(1000);
            $this->form('Client')->Pda->content->Pda_Ranking->content->goblindav_in_raiting_rank->text = $this->enemyCharacterInfo->getRankValue();
        }
        if (!$GLOBALS['QuestCompleted'])
        {
            $this->InitRaiting();
            
            $this->actorCharacterInfo->resetRank();
            $this->enemyCharacterInfo->resetRank();            
            
            $this->form('Client')->Pda->content->Pda_Ranking->content->actor_in_raiting_rank->text = $this->actorCharacterInfo->getRankValue();
            $this->form('Client')->Pda->content->Pda_Ranking->content->goblindav_in_raiting_rank->text = $this->enemyCharacterInfo->getRankValue();
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
