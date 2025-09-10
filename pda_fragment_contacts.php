<?php
namespace app\forms;

use app\forms\classes\UI\UIRoles;
use php\gui\UXImage;
use std, gui, framework, app;
use php\gui\event\UXWindowEvent; 


class pda_fragment_contacts extends AbstractForm
{
    private $localization;
    
    private $enemyCharacterInfo;     

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
        
        $this->enemyCharacterInfo = new UICharacterInfo($this, $this->localization, $this->icon, $this->rank, $this->relationship, $this->community, $this->bio, $this->name, $this->reputation);
        $this->enemyCharacterInfo->setEnemy();
    }    
    /**
     * @event selected_new.click-2x 
     */
    function RedirectRaiting(UXMouseEvent $e = null)
    {    
        $this->form('Client')->Pda->content->UpdateBtnColor();
        $this->form('Client')->Pda->content->ranks_label->textColor = '#d59b30';    
    
        $this->form('Client')->Pda->content->RankingBtn();
        
        $this->form('Client')->Pda->content->Pda_Ranking->content->ratingHueta->clickEntry(
            $this->form('Client')->Pda->content->Pda_Ranking->content->enemyCharacterInfo->name
        );
               
        $this->form('Client')->Pda->content->Pda_Ranking->content->EnemyInListBtn();        
    }
    function setCharacterSelected($selected)
    {
        $this->selected_new->opacity = $selected ? 0.40 : 0;
               
        if ($selected)
        {
            $this->bio->show();
            
            $this->localization->setLanguage($this->getCurrentLanguageFromUI());
                        
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
