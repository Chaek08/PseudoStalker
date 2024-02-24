<?php
namespace app\forms;

use std, gui, framework, app;
use action\Element; 


class pda_fragment_ranking extends AbstractForm
{
    function ResetUserInfo()
    {
        $this->goblindav_icon->hide();
        $this->actor_icon->hide();
        $this->valerok_icon->hide(); 
            
        $this->community_desc->hide();
        $this->community->hide(); 
        $this->rank_desc->hide();        
        $this->rank->hide();
        $this->relationship->hide(); 
        $this->attitude->hide(); 
        $this->bio->hide();         
        $this->separator->hide();           
    }
    function ShowUserInfo()
    {
        $this->community_desc->show();
        $this->community->show(); 
        $this->rank_desc->show();        
        $this->rank->show();
        $this->relationship->show(); 
        $this->attitude->show(); 
        $this->bio->show();         
        $this->separator->show();           
    }
    /**
     * @event frame_hide.click-Left 
     */
    function HideUserInfo(UXMouseEvent $e = null)
    {    
        $this->ResetUserInfo();
        $this->selected_status->hide();
    }
    /**
     * @event vova_btn.click-Left 
     */
    function ActorinListBtn(UXMouseEvent $e = null)
    {    
        $this->ResetUserInfo();
        $this->ShowUserInfo();
        $this->actor_icon->show();
        $this->SetUserInfo();    
        
        $this->selected_status->show();      
        $this->selected_status->y = 32;      
    }
    /**
     * @event kosta_btn.click-Left 
     */
    function EnemyinListBtn(UXMouseEvent $e = null)
    {    
        $this->ResetUserInfo();
        $this->ShowUserInfo();
        $this->valerok_icon->show();
        $this->SetUserInfo();  
        
        $this->selected_status->show();      
        $this->selected_status->y = 64;                       
    }
    /**
     * @event alex_btn.click-Left 
     */
    function OtherinListBtn(UXMouseEvent $e = null)
    {    
        $this->ResetUserInfo();
        $this->ShowUserInfo();
        $this->goblindav_icon->show();
        $this->SetUserInfo();  
        
        $this->selected_status->show();      
        $this->selected_status->y = 96;                
    }
    function SetUserInfo()
    {
        if ($this->goblindav_icon->visible)
        {
            Element::setText($this->community, 'Пидорасы');
            Element::setText($this->rank, 'новичок');
            Element::setText($this->bio, 'САМЫЙ ОТБИТЫЙ ПИДОРАС СЕРВЕРА DANILA EMOJI, ТЕРРОРИЗИРУЕТ УЧАСТНИКОВ, И ВООБЩЕ НАХУЙ, УРОД ЕБАНЫЙ');  
            Element::setText($this->relationship, 'враг');  
            $this->relationship->textColor = ('#cc3333');            
        }
        if ($this->valerok_icon->visible)
        {
            Element::setText($this->community, 'LADCEGA'); 
            Element::setText($this->rank, 'мастер');
            Element::setText($this->bio, 'Хозяин LADCEGA, попускает тупых огсровцев, лежит нож в гараже'); 
            Element::setText($this->relationship, 'друг'); 
            $this->relationship->textColor = ('#669966');                       
        }
        if ($this->actor_icon->visible)
        {
            Element::setText($this->community, 'Danila Emoji');
            Element::setText($this->rank, 'мастер');
            $this->attitude->hide();
            $this->relationship->hide();
            Element::setText($this->bio, 'Самый опасный на районе, попустит абсолютно любого, и неважно, админ он, или нет...');              
        }
    }
}
