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
        $this->name_icon->hide();
            
        $this->community_desc->hide();
        $this->community->hide(); 
        $this->rank_desc->hide();        
        $this->rank->hide();
        $this->relationship->hide(); 
        $this->attitude->hide(); 
        $this->bio->hide();         
        $this->bio_new->hide();             
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
        $this->bio_new->show();           
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
        $this->actor_in_raiting->textColor = '#999999';
        $this->goblin_in_raiting->textColor = '#999999';   
        $this->valerok_in_raiting->textColor = '#999999';   
        $this->name_in_raiting->textColor = '#999999';                  
    }
    /**
     * @event actor_in_raiting.click-Left 
     */
    function ActorinListBtn(UXMouseEvent $e = null)
    {    
        $this->ResetUserInfo();
        $this->ShowUserInfo();
        $this->actor_icon->show();
        $this->SetUserInfo();    
        
        $this->ResetBtnColor();
        $this->actor_in_raiting->textColor = 'white'; 
    }
    /**
     * @event valerok_in_raiting.click-Left 
     */
    function EnemyinListBtn(UXMouseEvent $e = null)
    {    
        $this->ResetUserInfo();
        $this->ShowUserInfo();
        $this->valerok_icon->show();
        $this->SetUserInfo();  
        
        $this->ResetBtnColor();
        $this->valerok_in_raiting->textColor = 'white';                               
    }
    /**
     * @event goblin_in_raiting.click-Left 
     */
    function OtherinListBtn(UXMouseEvent $e = null)
    {    
        $this->ResetUserInfo();
        $this->ShowUserInfo();
        $this->goblindav_icon->show();
        $this->SetUserInfo();  
        
        $this->ResetBtnColor();
        $this->goblin_in_raiting->textColor = 'white';                      
    }
    /**
     * @event name_in_raiting.click-Left 
     */
    function NameInListBtn(UXMouseEvent $e = null)
    {
        $this->ResetUserInfo();
        $this->ShowUserInfo();
        $this->name_icon->show();
        $this->SetUserInfo();  
        
        $this->ResetBtnColor();
        $this->name_in_raiting->textColor = 'white';                      
    }
    function ResetRole()
    {
        Element::setText($this->community, '-');
        $this->community->textColor = 'white';    
        $this->community->graphic = new UXImageView(new UXImage('res://.data/ui/dialog/no_role.png'));         
    }
    function LadcegaRole()
    {
        Element::setText($this->community, 'LADCEGA');
        $this->community->textColor = '#e64d4d';    
        $this->community->graphic = new UXImageView(new UXImage('res://.data/ui/dialog/ladcega_role.png'));                    
    }    
    function DanilaEmojiRole()
    {
        Element::setText($this->community, 'Danila Emoji');    
        $this->community->textColor = '#cc8033';          
        $this->community->graphic = new UXImageView(new UXImage('res://.data/ui/dialog/danila_emoji_role.png'));            
    }
    function PidorasRole()
    {
        Element::setText($this->community, 'Пидорасы');  
        $this->community->textColor = '#16a4cd';         
        $this->community->graphic = new UXImageView(new UXImage('res://.data/ui/dialog/pidoras_role.png'));                
    }
    function SetUserInfo()
    {
        if ($this->goblindav_icon->visible)
        {
            $this->ResetRole();
            $this->PidorasRole();
            Element::setText($this->rank, 'ветеринар');
            Element::setText($this->bio, 'САМЫЙ ОТБИТЫЙ ПИДОРАС СЕРВЕРА DANILA EMOJI, ТЕРРОРИЗИРУЕТ УЧАСТНИКОВ, И ВООБЩЕ НАХУЙ, УРОД ЕБАНЫЙ');  
            Element::setText($this->relationship, 'враг');  
            $this->relationship->textColor = ('#cc3333');            
        }
        if ($this->valerok_icon->visible)
        {
            $this->ResetRole();        
            $this->LadcegaRole();
            Element::setText($this->rank, 'мастер');
            Element::setText($this->bio, 'Хозяин LADCEGA, попускает тупых огсровцев, лежит нож в гараже'); 
            Element::setText($this->relationship, 'друг'); 
            $this->relationship->textColor = ('#669966');                       
        }       
        if ($this->actor_icon->visible)
        {
            $this->ResetRole();        
            $this->DanilaEmojiRole();
            Element::setText($this->rank, 'мастер');
            $this->attitude->hide();
            $this->relationship->hide();
            Element::setText($this->bio, 'Самый опасный на районе, попустит абсолютно любого, и неважно, админ он, или нет...');              
        }
    }
}
