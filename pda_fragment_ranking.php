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
        $this->reyn_icon->hide();
            
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

    /**
     * @event reyn_btn.click-Left 
     */
    function ReynInListBtn(UXMouseEvent $e = null)
    {
        $this->ResetUserInfo();
        $this->ShowUserInfo();
        $this->reyn_icon->show();
        $this->SetUserInfo();  
        
        $this->selected_status->show();      
        $this->selected_status->y = 128;                
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
    function NacistRole()
    {
        Element::setText($this->community, 'Нацисты');  
        $this->community->textColor = '#8a2bff';         
        $this->community->graphic = new UXImageView(new UXImage('res://.data/ui/dialog/nacik_role.png'));               
    }
    function SetUserInfo()
    {
        if ($this->goblindav_icon->visible)
        {
            $this->ResetRole();
            $this->PidorasRole();
            Element::setText($this->rank, 'новичок');
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
        if ($this->reyn_icon->visible)
        {
            $this->ResetRole();        
            $this->NacistRole();
            Element::setText($this->rank, 'ветеран');
            Element::setText($this->bio, 'Легендарный вредитель на сталкерских Дискорд серверах, особо опасен. Получил свое звание из-за гадств и забанен на многих серверах.'); 
            Element::setText($this->relationship, 'нейтрал'); 
            $this->relationship->textColor = ('#b3b31a');                       
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
