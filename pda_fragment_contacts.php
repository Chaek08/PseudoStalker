<?php
namespace app\forms;

use std, gui, framework, app;
use php\gui\event\UXWindowEvent; 


class pda_fragment_contacts extends AbstractForm
{
    /**
     * @event selected_new.click-Left 
     */
    function CharacterClick(UXMouseEvent $e = null)
    {    
        $this->selected_new->opacity = 0.35;
        $this->bio->show();                     
    }
    /**
     * @event selected_new.click-2x 
     */
    function RedirectRaiting(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->Pda->content->RankingBtn();
        $this->form('maingame')->Pda->content->Pda_Ranking->content->EnemyInListBtn();        
    }    
    /**
     * @event frame.click-Left 
     */
    function HideCharacter(UXMouseEvent $e = null)
    {    
        if ($this->selected_new->opacity == 0.35)
        {
            $this->selected_new->opacity = 0;
            $this->bio->hide();            
        }          
    }
    function UpdateContacts()
    {
        if ($GLOBALS['EnemyFailed'])
        {
            $this->name->hide();
            $this->community->hide();
            $this->community_desc->hide();
            $this->rank->hide();
            $this->rank_desc->hide();
            $this->relationship->hide();
            $this->relationship_desc->hide();
            $this->reputation->hide();
            $this->reputation_desc->hide();
            $this->online_icon->hide();
            $this->icon->hide();
            $this->selected_new->hide();
            $this->selected_new->opacity = 0;         
        }
        else
        {
            $this->name->show();
            $this->community->show();
            $this->community_desc->show();
            $this->rank->show();
            $this->rank_desc->show();
            $this->relationship->show();
            $this->relationship_desc->show();
            $this->reputation->show();
            $this->reputation_desc->show();
            $this->online_icon->show();
            $this->icon->show();
            $this->selected_new->show();
        }
        
        if ($this->bio->visible) $this->bio->hide(); 
    }   
}
