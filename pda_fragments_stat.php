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
    
    private $questManager;
    private $tasksForm;    
    
    public function __construct() 
    {
        parent::__construct();

        $this->localization = new Localization($language);  
        
        uiLater(function () {
            $this->tasksForm = $this->form('Client')->Pda->content->Pda_Tasks->content;
            $this->questManager = $this->tasksForm->questManager;
        
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
        //$this->statistic_num->text = "9700\n999\n0\n\n10699";
        //todo: общий рейтинг с использованием RatingManager
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
        if (!$quest = $this->questManager->getQuest($this->tasksForm->currentQuestId)) return;
    
        if ($quest->status === QuestManager::STATUS_COMPLETED)
        {
            $this->actorCharacterInfo->addRank(1500);     
        }
    
        if ($quest->status === QuestManager::STATUS_FAILED)
        {
            $this->enemyCharacterInfo->addRank(1200);
        }
    
        if ($quest->status === QuestManager::STATUS_ACTIVE)
        {
            $this->InitRaiting();
            
            $this->actorCharacterInfo->resetRank();
            $this->enemyCharacterInfo->resetRank();
        }
        
        $this->form('Client')->Pda->content->Pda_Ranking->content->UpdateData();        
    } 
    
    function UpdateFinalLabel()
    {
        $this->tab_final->hide();
        $this->final_label->hide();
        $this->final_label->text = null;
        
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());        
        
        if (!$quest = $this->questManager->getQuest($this->tasksForm->currentQuestId)) return;
                
        if ($quest->status === QuestManager::STATUS_FAILED)
        {
            $this->tab_final->show();
            $this->final_label->show();
            $this->final_label->text = $this->localization->get('FinalLabel_ActorFail');
        }
        if ($quest->status === QuestManager::STATUS_COMPLETED)
        {
            $this->tab_final->show();
            $this->final_label->show();
            $this->final_label->text = $this->localization->get('FinalLabel_EnemyFail');
        }
    }
}
