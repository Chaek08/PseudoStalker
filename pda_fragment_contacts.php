<?php
namespace app\forms;

use php\gui\UXImage;
use std, gui, framework, app;
use php\gui\event\UXWindowEvent; 


class pda_fragment_contacts extends AbstractForm
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
    
        $name = trim($this->form('maingame')->Pda->content->SDK_EnemyName);
        $icon = trim($this->form('maingame')->Pda->content->SDK_EnemyIcon);
        $bio = trim($this->form('maingame')->Pda->content->SDK_EnemyBio);
        
        $role_name = trim($this->form('maingame')->Pda->content->SDK_PidoRoleName);
        $role_icon = trim($this->form('maingame')->Pda->content->SDK_PidoRoleIcon);
        $role_color = trim($this->form('maingame')->Pda->content->SDK_PidoRoleColor);        
        
        $this->name->text = $name !== '' ? $name : $this->localization->get('Enemy_Name');
        $this->icon->image = new UXImage($icon !== '' ? $icon : 'res://.data/ui/icon_npc/goblindav.png');
        $this->bio->text = $bio !== '' ? $bio : $this->localization->get('GoblindaV_Bio');
        
        $this->community->text = $role_name != '' ? $role_name : $this->localization->get('Community_Pido');
        $this->community->graphic = new UXImageView(new UXImage($role_icon != '' ? $role_icon : 'res://.data/ui/dialog/dialog_wnd/pidoras_roleicon.png'));
        $this->community->textColor = $role_color != '' ? $role_color : '#16a4cd';        
    }    
    /**
     * @event selected_new.click-2x 
     */
    function RedirectRaiting(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->Pda->content->RankingBtn();
        $this->form('maingame')->Pda->content->Pda_Ranking->content->EnemyInListBtn();        
    }    

    
    function setCharacterSelected($selected)
    {
        $this->selected_new->opacity = $selected ? 0.35 : 0;

        if ($selected)
        {
            $this->bio->show();
            $this->tab_detail->text = $this->localization->get('TabBio');
        }
        else
        {
            $this->bio->hide();
            $this->tab_detail->text = null;
        }
    }
    /**
     * @event selected_new.click-Left 
     */
    function CharacterClick(UXMouseEvent $e = null)
    {    
        $this->localization->setLanguage($this->form('maingame')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value);    
    
        $this->setCharacterSelected(true);
    }
    /**
     * @event frame.click-Left 
     */
    function HideCharacter(UXMouseEvent $e = null)
    {    
        $this->setCharacterSelected(false);         
    }      
    function UpdateContacts()
    {
        $elements = [
            $this->name,
            $this->community,
            $this->community_desc,
            $this->rank,
            $this->rank_desc,
            $this->relationship,
            $this->relationship_desc,
            $this->reputation,
            $this->reputation_desc,
            $this->online_icon,
            $this->icon,
            $this->selected_new
        ];

        foreach ($elements as $el)
        {
            if ($GLOBALS['EnemyFailed'])
            {
                $el->hide();
            }
            else
            {
                $el->show();
            }
        }

        $this->selected_new->opacity = 0;

        if ($this->bio->visible) 
        {
            $this->bio->hide();
            $this->tab_detail->text = null;
        } 
    }   
}
