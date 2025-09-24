<?php
namespace app\forms;
use app\forms\classes\UI\RatingManager;
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
        uiLater(function () {
            $rank = $this->actorCharacterInfo->getRankValue();
        
            $part1 = intdiv($rank, 2);
            $part2 = intdiv($rank, 3);
        
            $questStatus = !empty($GLOBALS['QuestCompleted']) ? 1 : 0;
        
            $total = $rank;
        
            $this->statistic_num->text = $part1 . "\n" . $part2 . "\n" . $questStatus . "\n\n" . $total;            
        });
    }
    /**
     * @event icon.click-2x 
     */
    function RedirectRaiting(UXMouseEvent $e = null)
    {    
        $this->form('Client')->Pda->content->UpdateBtnColor();
        $this->form('Client')->Pda->content->ranks_label->textColor = '#d59b30';
    
        $this->form('Client')->Pda->content->RankingBtn();
        
        $this->form('Client')->Pda->content->Pda_Ranking->content->ratingHueta->clickEntry(
            $this->form('Client')->Pda->content->Pda_Ranking->content->actorCharacterInfo->name
        );

        $this->form('Client')->Pda->content->Pda_Ranking->content->ActorInListBtn();
    }
    function UpdateRaiting()
    {
        if ($GLOBALS['EnemyFailed'])
        {
            $this->actorCharacterInfo->addRank(1500); 
            $this->form('Client')->Pda->content->Pda_Ranking->content->UpdateData();    
        }
        if ($GLOBALS['ActorFailed'])
        {
            $this->enemyCharacterInfo->addRank(1200);
            $this->form('Client')->Pda->content->Pda_Ranking->content->UpdateData();
        }
        if (!$GLOBALS['QuestCompleted'])
        {
            $this->actorCharacterInfo->resetRank();
            $this->enemyCharacterInfo->resetRank();            
            
            $this->form('Client')->Pda->content->Pda_Ranking->content->UpdateData();
        }
        
        $this->InitRaiting();
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
