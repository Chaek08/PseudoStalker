<?php
namespace app\forms;

use std, gui, framework, app;
use php\gui\event\UXWindowEvent; 


class pda_fragment_contacts extends AbstractForm
{
    /**
     * @event show 
     */
    function InitPdaContacts(UXWindowEvent $e = null)
    {    
        $this->online_icon->opacity = 100;
        $this->name->opacity = 100;
        $this->selected_new->opacity = 0;
        
        $this->rank_desc->opacity = 100;
        $this->community_desc->opacity = 100;                
        $this->relationship_desc->opacity = 100;
        $this->reputation_desc->opacity = 100;
        
        $this->rank->opacity = 100;
        $this->community->opacity = 100;
        $this->relationship->opacity = 100;
        $this->reputation->opacity = 100;
    }
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
        $this->form('maingame')->fragment_pda->content->RankingBtn();
        $this->form('maingame')->fragment_pda->content->fragment_ranking->content->OtherinListBtn();        
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
    function AddEnemyContacts()
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
        $this->alex_frame->show();
        $this->icon->show();
        if($this->bio->visible)
        {
            $this->bio->hide();
        }           
    }
    function DeleteEnemyContacts()
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
        $this->alex_frame->hide();
        $this->icon->hide();
        $this->selected_new->opacity = 0;
        if($this->bio->visible)
        {
            $this->bio->hide();
        }             
    }    
}
