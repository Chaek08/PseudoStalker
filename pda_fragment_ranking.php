<?php
namespace app\forms;

use std, gui, framework, app;
use action\Element; 


class pda_fragment_ranking extends AbstractForm
{
    function ResetUserInfo()
    {
        $this->alex_icon->hide();
        $this->kosta_icon->hide();
        $this->kosta_icon_->hide(); 
            
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
        $this->kosta_icon->show();
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
        $this->kosta_icon_->show();
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
        $this->alex_icon->show();
        $this->SetUserInfo();  
        
        $this->selected_status->show();      
        $this->selected_status->y = 96;                
    }
    function SetUserInfo()
    {
        if ($this->alex_icon->visible)
        {
            Element::setText($this->community, 'Спаммеры');
            Element::setText($this->rank, 'новичок');
            Element::setText($this->bio, 'Продам запчасти на айфо... ёпта. у нас биография!! Пьёт пиво, занимается ремонтой телефоной. Спит с выключенным светом, не боится бабайку на кухне');  
            Element::setText($this->relationship, 'враг');  
            $this->relationship->textColor = ('#cc3333');            
        }
        if ($this->kosta_icon_->visible)
        {
            Element::setText($this->community, 'Спаситель мира'); 
            Element::setText($this->rank, 'мастер');
            Element::setText($this->bio, 'Носки , недоро.. так.. у нас же биография! Репер, фитовал с моргенштерном. Записал дисс на вову из соседнего подъезда'); 
            Element::setText($this->relationship, 'друг'); 
            $this->relationship->textColor = ('#669966');                       
        }
        if ($this->kosta_icon->visible)
        {
            Element::setText($this->community, 'Спаситель мира');
            Element::setText($this->rank, 'мастер');
            $this->attitude->hide();
            $this->relationship->hide();
            Element::setText($this->bio, 'Хотите бесплатный кунили... ой.. у нас же биография!!! Терминатор. Может сьесть червяка с улыбкой на лице, его боятся все. Программист, изобрёл лампочку. Создал свой петангон');              
        }
    }
}
