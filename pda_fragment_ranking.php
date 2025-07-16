<?php
namespace app\forms;

use php\gui\UXImage;
use php\gui\UXImageView;
use std, gui, framework, app;
use action\Element; 
use app\forms\classes\Localization;

class pda_fragment_ranking extends AbstractForm
{
    private $localization;

    public function __construct() 
    {
        parent::__construct();

        $this->localization = new Localization($language);
        
        $GLOBALS['SelectedActor'] = false;
        $GLOBALS['SelectedEnemy'] = false;
        $GLOBALS['SelectedValera'] = false;
        
        $groups = [
            'actor_in_raiting' => [
                'actor_in_raiting_pos',
                'actor_in_raiting_name',
                'actor_in_raiting_rank'
            ],
            'valerok_in_raiting' => [
                'valerok_in_raiting_pos',
                'valerok_in_raiting_name',
                'valerok_in_raiting_rank'
            ],
            'goblindav_in_raiting' => [
                'goblindav_in_raiting_pos',
                'goblindav_in_raiting_name',
                'goblindav_in_raiting_rank'
            ]
        ];

        $this->activeRatingGroup = null;

        foreach ($groups as $groupName => $labels)
        {
            $group = $this->{$groupName};

            $group->on("mouseEnter", function($e) use ($labels) {
                foreach ($labels as $labelName)
                {
                    $label = $this->{$labelName};
                    if ($label->textColor != "#cccccc")
                    {
                        $label->textColor = "#ffffff";
                    }
                }
            });

            $group->on("mouseExit", function($e) use ($labels) {
                foreach ($labels as $labelName) {
                    $label = $this->{$labelName};
                    if ($label->textColor != "#cccccc")
                    {    
                        $label->textColor = "#999999";
                    }
                }
            });

            $group->on("mouseDown", function($e) use ($groupName) {
                $this->activeRatingGroup = $groupName;
            });
        }

        $this->on("mouseUp", function($e) use ($groups) {
            if ($this->activeRatingGroup != null)
            {
                $groupName = $this->activeRatingGroup;
                $group = $this->{$groupName};

                if ($group->hover)
                {
                    $this->ResetBtnColor(); // вот здесь вызываем

                    foreach ($groups[$groupName] as $labelName)
                    {
                        $this->{$labelName}->textColor = "#cccccc";
                    }

                    switch ($groupName)
                    {
                        case 'actor_in_raiting':
                            $this->ActorInListBtn();
                            break;
                        case 'valerok_in_raiting':
                            $this->ValerokInListBtn();
                            break;
                        case 'goblindav_in_raiting':
                            $this->EnemyInListBtn();
                            break;
                    }
                }

                $this->activeRatingGroup = null;
            }
        });
    }
    function UpdateData()
    {
        $this->localization->setLanguage($this->form('Client')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value);
    
        $actor_in_raiting = trim($this->form('Client')->Pda->content->SDK_ActorName);
        $enemy_in_raiting = trim($this->form('Client')->Pda->content->SDK_EnemyName);
        $valerok_in_raiting = trim($this->form('Client')->Pda->content->SDK_ValerokName);
        
        $this->actor_in_raiting_name->text = $actor_in_raiting != '' ? $actor_in_raiting : $this->localization->get('GG_Name');
        $this->goblindav_in_raiting_name->text = $enemy_in_raiting != '' ? $enemy_in_raiting : $this->localization->get('Enemy_Name');
        $this->valerok_in_raiting_name->text = $valerok_in_raiting != '' ? $valerok_in_raiting : $this->localization->get('Ranking_Valerok');
        
        ($g = $this->community->graphic)->width = ($g->height = 16);
    }
    
    function ResetUserInfo()
    {
        $GLOBALS['SelectedActor'] = false;
        $GLOBALS['SelectedEnemy'] = false;
        $GLOBALS['SelectedValera'] = false;
            
        $this->tab_detail->text = null;
        $this->community_desc->hide();
        $this->community->hide(); 
        $this->rank_desc->hide();        
        $this->rank->hide();
        $this->relationship->hide(); 
        $this->attitude->hide(); 
        $this->bio->hide();         
        $this->separator->hide(); 
        $this->user_icon->hide();       
        $this->user_icon->image = new UXImage('res://.data/ui/icon_npc/no_icon.png');     
        
        if ($this->death_filter->visible) $this->death_filter->hide();
    }
    function ShowUserInfo()
    {
        $this->localization->setLanguage($this->form('Client')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value);
        
        $this->tab_detail->text = $this->localization->get('TabDetail');
        $this->community_desc->show();
        $this->community->show(); 
        $this->rank_desc->show();        
        $this->rank->show();
        $this->relationship->show(); 
        $this->attitude->show(); 
        $this->bio->show();         
        $this->separator->show(); 
        $this->user_icon->show();          
    }
    /**
     * @event frame_hide.click-Left 
     */
    function HideUserInfo(UXMouseEvent $e = null)
    {    
        $this->ResetUserInfo();
        $this->ResetBtnColor();
    }
    function ResetBtnColor()
    {
        $this->actor_in_raiting_pos->textColor = '#999999';
        $this->actor_in_raiting_name->textColor = '#999999';
        $this->actor_in_raiting_rank->textColor = '#999999';
        
        $this->valerok_in_raiting_pos->textColor = '#999999';
        $this->valerok_in_raiting_name->textColor = '#999999';
        $this->valerok_in_raiting_rank->textColor = '#999999';
        
        $this->goblindav_in_raiting_pos->textColor = '#999999';
        $this->goblindav_in_raiting_name->textColor = '#999999';
        $this->goblindav_in_raiting_rank->textColor = '#999999';
    }
    function DeathFilter() // Cake-crypto
    { 
        if ($this->form('Client')->Pda->content->Pda_Statistic->visible)
        {
            $GLOBALS['ActorFailed'] ? $this->form('Client')->Pda->content->Pda_Statistic->content->death_filter->show() : $this->form('Client')->Pda->content->Pda_Statistic->content->death_filter->hide();           
        }
        
        if ($GLOBALS['SelectedActor']) //Проверяем, выбран ли сейчас нужный user
        {
            $GLOBALS['ActorFailed'] ? $this->death_filter->show() : $this->death_filter->hide();
        }
        if ($GLOBALS['SelectedEnemy']) //Проверяем, выбран ли сейчас нужный user
        {
            $GLOBALS['EnemyFailed'] ? $this->death_filter->show() : $this->death_filter->hide(); //Проверяем, мёртв ли противник, чтобы в дальнейшем прописать ему DeathFilter
        }
        if ($GLOBALS['SelectedValera']) //Проверяем, выбран ли сейчас нужный user
        {
            $this->death_filter->hide();
        }
    }
    /**
     * @event actor_in_raiting.click-Left 
     */
    function ActorInListBtn(UXMouseEvent $e = null)
    {    
        $this->ResetUserInfo();
        $this->ShowUserInfo();
        
        $GLOBALS['SelectedActor'] = true;
        $this->SetUserInfo();
    }
    /**
     * @event valerok_in_raiting.click-Left 
     */
    function ValerokInListBtn(UXMouseEvent $e = null)
    {    
        $this->ResetUserInfo();
        $this->ShowUserInfo();
        
        $GLOBALS['SelectedValera'] = true;
        $this->SetUserInfo();
    }
    /**
     * @event goblindav_in_raiting.click-Left 
     */
    function EnemyInListBtn(UXMouseEvent $e = null)
    {    
        $this->ResetUserInfo();
        $this->ShowUserInfo();
        
        $GLOBALS['SelectedEnemy'] = true;
        $this->SetUserInfo();
    }
    /**
     * @event user_icon.click-2x 
     */
    function Redirect(UXMouseEvent $e = null)
    {    
        if ($GLOBALS['SelectedActor']) 
        {
            $this->form('Client')->Pda->content->UpdateBtnColor();
            $this->form('Client')->Pda->content->stat_label->textColor = '#d59b30';
            
            $this->form('Client')->Pda->content->StatisticBtn(); 
        }
        if ($this->form('Client')->Pda->content->Pda_Contacts->content->icon->visible)
        {
            if ($GLOBALS['EnemyFailed'])
            {
                return;
            }
            if ($GLOBALS['SelectedEnemy'])
            {
                $this->form('Client')->Pda->content->UpdateBtnColor();
                $this->form('Client')->Pda->content->contacts_label->textColor = '#d59b30';
            
                $this->form('Client')->Pda->content->ContactsBtn();
                $this->form('Client')->Pda->content->Pda_Contacts->content->CharacterClick(); 
            }                     
        } 
    }
    function ResetRole()
    {
        Element::setText($this->community, '-');
        $this->community->textColor = 'white';    
        $this->community->graphic = new UXImageView(new UXImage('res://.data/ui/dialog/no_role.png'));         
    }
    function LadcegaRole()
    {
        $name = trim($this->form('Client')->Pda->content->SDK_LaRoleName);
        $icon = trim($this->form('Client')->Pda->content->SDK_LaRoleIcon);
        $color = trim($this->form('Client')->Pda->content->SDK_LaRoleColor);
        
        $this->community->text = $name !== '' ? $name : $this->localization->get('LA_Community');
        $this->community->graphic = $icon !== ''
            ? new UXImageView(new UXImage($icon))
            : new UXImageView(new UXImage('res://.data/ui/dialog/ladcega_role.png'));
        $this->community->textColor = $color !== '' ? $color : '#e64d4d';
        
        ($g = $this->community->graphic)->width = ($g->height = 16);
    }    
    function DanilaEmojiRole()
    {
        $name = trim($this->form('Client')->Pda->content->SDK_DeRoleName);
        $icon = trim($this->form('Client')->Pda->content->SDK_DeRoleIcon);
        $color = trim($this->form('Client')->Pda->content->SDK_DeRoleColor);
        
        $this->community->text = $name !== '' ? $name : $this->localization->get('DE_Community');
        $this->community->graphic = $icon !== ''
            ? new UXImageView(new UXImage($icon))
            : new UXImageView(new UXImage('res://.data/ui/dialog/danila_emoji_role.png'));
        $this->community->textColor = $color !== '' ? $color : '#cc8033';
        
        ($g = $this->community->graphic)->width = ($g->height = 16);
    }
    function PidorasRole()
    {
        $name = trim($this->form('Client')->Pda->content->SDK_PidoRoleName);
        $icon = trim($this->form('Client')->Pda->content->SDK_PidoRoleIcon);
        $color = trim($this->form('Client')->Pda->content->SDK_PidoRoleColor);
        
        $this->community->text = $name !== '' ? $name : $this->localization->get('Community_Pido');
        $this->community->graphic = $icon !== ''
            ? new UXImageView(new UXImage($icon))
            : new UXImageView(new UXImage('res://.data/ui/dialog/pidoras_role.png'));
        $this->community->textColor = $color !== '' ? $color : '#16a4cd';
        
        ($g = $this->community->graphic)->width = ($g->height = 16);
    }
    function SetUserInfo()
    {
        $this->localization->setLanguage($this->form('Client')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value);
        
        if ($GLOBALS['SelectedEnemy'])
        {
            $this->ResetRole();
            $this->PidorasRole();
            $this->DeathFilter();
            
            $this->rank->text = $this->localization->get('Rank_Veterinarian');
            $this->relationship->text = $this->localization->get('Relationship_Enemy');
            $this->relationship->textColor = ('#cc3333');
            
            $icon_path = trim($this->form('Client')->Pda->content->SDK_EnemyIcon);
            $bio_path = trim($this->form('Client')->Pda->content->SDK_EnemyBio);
            
            $this->user_icon->image = new UXImage($icon_path != '' ? $icon_path : 'res://.data/ui/icon_npc/goblindav.png');
            $this->bio->text = $bio_path != '' ? $bio_path : $this->localization->get('GoblindaV_Bio');
        }
        if ($GLOBALS['SelectedValera'])
        {
            $this->ResetRole();
            $this->LadcegaRole();
            $this->DeathFilter();
            
            $this->rank->text = $this->localization->get('Rank_Master');
            $this->relationship->text = $this->localization->get('Relationship_Friend');
            $this->relationship->textColor = ('#669966');
            
            $icon_path = trim($this->form('Client')->Pda->content->SDK_ValerokIcon);
            $bio_path = trim($this->form('Client')->Pda->content->SDK_ValerokBio);
            
            $this->user_icon->image = new UXImage($icon_path != '' ? $icon_path : 'res://.data/ui/icon_npc/valerok.png');
            $this->bio->text = $bio_path != '' ? $bio_path : $this->localization->get('Valerok_Bio');
        }       
        if ($GLOBALS['SelectedActor'])
        {
            $this->ResetRole();        
            $this->DanilaEmojiRole();          
            $this->DeathFilter();
            $this->rank->text = $this->localization->get('Rank_Master');
            $this->attitude->hide();
            $this->relationship->hide();
            
            $icon_path = trim($this->form('Client')->Pda->content->SDK_ActorIcon);
            $bio_path = trim($this->form('Client')->Pda->content->SDK_ActorBio);
            
            $this->user_icon->image = new UXImage($icon_path != '' ? $icon_path : 'res://.data/ui/icon_npc/actor.png');
            $this->bio->text = $bio_path != '' ? $bio_path : $this->localization->get('Actor_Bio');
        }       
    }
}
