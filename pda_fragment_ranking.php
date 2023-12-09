<?php
namespace app\forms;

use std, gui, framework, app;
use action\Element; 


class pda_fragment_ranking extends AbstractForm
{
    /**
     * @event alex_frame.click-Left 
     */
    function AlexRaiting(UXMouseEvent $e = null)
    {                 
        $this->alex_icon->show();        
        $this->community_desc->show();
        $this->community->show(); //community
        $this->rank_desc->show();        
        $this->rank->show();  //rank
        $this->alex_relationship_desc->show(); 
        $this->attitude_alex->show(); //relationship
        $this->bio->show(); //biography   
        $this->selected->show();         
        $this->kosta_icon_->hide();
        $this->kosta_icon->hide();          

        $this->selected->x = 8;
        $this->selected->y = 72; 
   
        $this->alex_relationship_desc->textColor = ('#cc3333');

        Element::setText($this->community, 'Спаммеры'); //community
        Element::setText($this->rank, 'новичок'); //rank
        Element::setText($this->bio, 'Продам запчасти на айфо... ёпта. у нас биография!! Пьёт пиво, занимается ремонтой телефоной. Спит с выключенным светом, не боится бабайку на кухне');  //bio  
        Element::setText($this->alex_relationship_desc, 'враг'); //bio                   
    }
    /**
     * @event kosta_frame.click-Left 
     */
    function ActorRaiting(UXMouseEvent $e = null)
    {
        //Icon
        $this->kosta_icon->show();
        $this->kosta_icon_->hide();
        $this->alex_icon->hide();
        // Relation label 
        $this->alex_relationship_desc->hide();
        $this->attitude_alex->hide();
        //Selected icon
        $this->selected->show();
        //Position in selected icon 
        $this->selected->x = 8;
        $this->selected->y = 24;
               
        //Character info
        $this->community->show(); //community
        $this->community_desc->show(); 
        $this->rank_desc->show();
        $this->rank->show(); //rank                          
        $this->bio->show();  //biography      
                               
        Element::setText($this->community, 'Спаситель мира'); //community
        Element::setText($this->rank, 'мастер'); //rank
        Element::setText($this->bio, 'Хотите бесплатный кунили... ой.. у нас же биография!!! Терминатор. Может сьесть червяка с улыбкой на лице, его боятся все. Программист, изобрёл лампочку. Создал свой петангон'); //bio          
    }

    /**
     * @event frame_01.click-Left 
     */
    function ClearInfo(UXMouseEvent $e = null)
    {
        $this->selected->hide();
        $this->bio->hide();   
        //01.02.2023
        $this->alex_icon->hide();
        $this->kosta_icon->hide(); 
        $this->kosta_icon_->hide();      
        $this->community_desc->hide();
        $this->community->hide(); //community
        $this->rank_desc->hide();        
        $this->rank->hide();  //rank
        $this->alex_relationship_desc->hide();        
        $this->attitude_alex->hide(); //relationship                
    }

    /**
     * @event kosta_frame_.click-Left 
     */
    function KostaRaiting(UXMouseEvent $e = null)
    {  
        $this->kosta_icon_->show();   
        $this->selected->show();
        $this->alex_relationship_desc->show();        
        $this->attitude_alex->show(); //relationship 
        $this->community->show(); //community
        $this->community_desc->show(); 
        $this->rank_desc->show();
        $this->rank->show(); //rank     
        $this->bio->show();  //biography 
        $this->selected->show();        

        //Position in selected icon 
        $this->selected->x = 8;
        $this->selected->y = 48;

        $this->alex_relationship_desc->textColor = ('#00ff11');

        Element::setText($this->community, 'Спаситель мира'); //community
        Element::setText($this->rank, 'мастер'); //rank
        Element::setText($this->bio, 'Носки , недоро.. так.. у нас же биография! Репер, фитовал с моргенштерном. Записал дисс на вову из соседнего подъезда'); //bio 
        Element::setText($this->alex_relationship_desc, 'друг'); //bio   
    }

}
