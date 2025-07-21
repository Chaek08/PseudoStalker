<?php
namespace app\forms;
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
        $actor_name = trim($this->form('Client')->Pda->content->SDK_ActorName);
        $actor_icon = trim($this->form('Client')->Pda->content->SDK_ActorIcon);
        
        $role_name = trim($this->form('Client')->Pda->content->SDK_DeRoleName);
        $role_icon = trim($this->form('Client')->Pda->content->SDK_DeRoleIcon);
        $role_color = trim($this->form('Client')->Pda->content->SDK_DeRoleColor);
        
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());        

        $this->tab_button->text = $actor_name !== '' ? $actor_name : $this->localization->get('GG_Name');
        $this->icon->image = new UXImage($actor_icon !== '' ? $actor_icon : 'res://.data/ui/icon_npc/actor.png');
         
        $this->community->text = $role_name != '' ? $role_name : $this->localization->get('DE_Community');
        $this->community->graphic = new UXImageView(new UXImage($role_icon != '' ? $role_icon : 'res://.data/ui/dialog/danila_emoji_role.png'));
        $this->community->textColor = $role_color != '' ? $role_color : '#cc8033';
        
        ($g = $this->community->graphic)->width = ($g->height = 16);
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
