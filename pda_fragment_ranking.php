<?php
namespace app\forms;

use std, gui, framework, app;
use action\Element; 


class pda_fragment_ranking extends AbstractForm
{
    function ResetUserInfo()
    {
        $this->user_actor->hide();
        $this->user_valerok->hide();
        $this->user_goblindav->hide();
            
        $this->community_desc->hide();
        $this->community->hide(); 
        $this->rank_desc->hide();        
        $this->rank->hide();
        $this->relationship->hide(); 
        $this->attitude->hide(); 
        $this->bio->hide();         
        $this->bio_new->hide(); 
        $this->user_icon->hide();       
        $this->user_icon->image = new UXImage('res://.data/ui/icon_npc/no_icon.png');     
        if ($this->death_filter->visible) {$this->death_filter->hide();}
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
        $this->actor_in_raiting->textColor = '#999999';
        $this->goblin_in_raiting->textColor = '#999999';   
        $this->valerok_in_raiting->textColor = '#999999';                    
    }
    function DeathFilter() // Cake-crypto
    {
        if ($this->user_actor->visible) //Проверяем, выбран ли сейчас нужный user
        {
            if ($this->form('maingame')->skull_actor->visible) //Проверяем, мёртв ли актёр, чтобы в дальнейшем прописать ему DeathFilter
            {
                $this->death_filter->show();
                $this->form('maingame')->fragment_pda->content->fragment_stat->content->death_filter->show();
            }
            else
            {
                $this->death_filter->hide();
                $this->form('maingame')->fragment_pda->content->fragment_stat->content->death_filter->hide();            
            }
        }
        if ($this->user_goblindav->visible) //Проверяем, выбран ли сейчас нужный user
        {
            if ($this->form('maingame')->skull_enemy->visible) //Проверяем, мёртв ли противник, чтобы в дальнейшем прописать ему DeathFilter
            {
                $this->death_filter->show();
            }
            else
            {
                $this->death_filter->hide();
            }
        }
        if ($this->user_valerok->visible) //Проверяем, выбран ли сейчас нужный user
        {
            $this->death_filter->hide();
        }
    }
    /**
     * @event actor_in_raiting.click-Left 
     */
    function ActorinListBtn(UXMouseEvent $e = null)
    {    
        $this->ResetUserInfo();
        $this->ShowUserInfo();
        $this->user_actor->show();
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
        $this->user_valerok->show();
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
        $this->user_goblindav->show();
        $this->SetUserInfo();  
        
        $this->ResetBtnColor();
        $this->goblin_in_raiting->textColor = 'white';                      
    }
    /**
     * @event user_icon.click-2x 
     */
    function Redirect(UXMouseEvent $e = null)
    {    
        if ($this->user_actor->visible)
        {
            $this->form('maingame')->fragment_pda->content->StatisticBtn();
        }
        if ($this->form('maingame')->fragment_pda->content->fragment_contacts->content->icon->visible)
        {
            if ($this->form('maingame')->skull_enemy->visible)
            {
                return;
            }
            if ($this->user_goblindav->visible)
            {
                $this->form('maingame')->fragment_pda->content->ContactsBtn();
                $this->form('maingame')->fragment_pda->content->fragment_contacts->content->CharacterClick(); 
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
    function EblanRole()
    {
        Element::setText($this->community, 'Гандоны');  
        $this->community->textColor = '#990000';         
        $this->community->graphic = new UXImageView(new UXImage('res://.data/ui/dialog/ebln.png'));         
    }
    function SetUserInfo()
    {
        if ($this->user_goblindav->visible)
        {
            $this->ResetRole();
            $this->PidorasRole();
            $this->user_icon->image = new UXImage('res://.data/ui/icon_npc/goblindav.png');
            $this->DeathFilter();
            Element::setText($this->rank, 'ветеринар');
            Element::setText($this->bio, 'САМЫЙ ОТБИТЫЙ ПИДОРАС СЕРВЕРА DANILA EMOJI, ТЕРРОРИЗИРУЕТ УЧАСТНИКОВ, И ВООБЩЕ НАХУЙ, УРОД ЕБАНЫЙ');  
            Element::setText($this->relationship, 'враг');  
            $this->relationship->textColor = ('#cc3333');            
        }
        if ($this->user_valerok->visible)
        {
            $this->ResetRole();        
            $this->LadcegaRole();
            $this->user_icon->image = new UXImage('res://.data/ui/icon_npc/valerok.png');    
            $this->DeathFilter();
            Element::setText($this->rank, 'мастер');
            Element::setText($this->bio, 'Хозяин LADCEGA, попускает тупых огсровцев, В его гараже всегда лежит нож, готовый помочь в любых делах.'); 
            Element::setText($this->relationship, 'друг'); 
            $this->relationship->textColor = ('#669966');                       
        }       
        if ($this->user_actor->visible)
        {
            $this->ResetRole();        
            $this->DanilaEmojiRole();          
            $this->user_icon->image = new UXImage('res://.data/ui/icon_npc/actor.png');  
            $this->DeathFilter();
            Element::setText($this->rank, 'мастер');
            Element::setText($this->bio, 'Известен как самый бескомпромиссный в районе, этот человек дает пиздюлей каждому, без разницы - будь то админ или нет.');            
            $this->attitude->hide();
            $this->relationship->hide();              
        }       
    }
}
