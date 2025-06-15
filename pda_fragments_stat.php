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
    
    function UpdateData()
    {
        $this->localization->setLanguage($this->form('maingame')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value);    
    
        $actor_name = trim($this->form('maingame')->Pda->content->SDK_ActorName);
        $actor_icon = trim($this->form('maingame')->Pda->content->SDK_ActorIcon);
        
        $role_name = trim($this->form('maingame')->Pda->content->SDK_DeRoleName);
        $role_icon = trim($this->form('maingame')->Pda->content->SDK_DeRoleIcon);
        $role_color = trim($this->form('maingame')->Pda->content->SDK_DeRoleColor);

        $this->tab_button->text = $actor_name !== '' ? $actor_name : $this->localization->get('GG_Name');
        $this->icon->image = new UXImage($actor_icon !== '' ? $actor_icon : 'res://.data/ui/icon_npc/actor.png');
         
        $this->community->text = $role_name != '' ? $role_name : $this->localization->get('DE_Community');
        $this->community->graphic = new UXImageView(new UXImage($role_icon != '' ? $role_icon : 'res://.data/ui/dialog/danila_emoji_role.png'));
        $this->community->textColor = $role_color != '' ? $role_color : '#cc8033';
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
    function UpdateFinalLabel()
    {
        $this->localization->setLanguage($this->form('maingame')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value);
        
        $this->tab_final->hide();
        $this->final_label->hide();
        $this->final_label->text = null;
        
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
