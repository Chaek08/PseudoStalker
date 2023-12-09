<?php
namespace app\forms;

use std, gui, framework, app;

// подсказка для дайлогов actor_label_1, actor_desc_1

class dialog extends AbstractForm
{
    /**
     * @event answer_1.click-Left 
     */
    function Talk_1(UXMouseEvent $e = null)
    {    
        $this->actor_desc_1->show();
        $this->actor_label_1->show();
        $this->alex_desc_2->show();
        $this->alex_label_2->show();
        Element::setText($this->answer_desc, "Ахуел твой муж, когда узнал что ты скоро станешь натуралом!!!");
        $this->answer_1->hide();          
        $this->answer_2->show();     
    }

    /**
     * @event answer_2.click-Left 
     */
    function Talk_2(UXMouseEvent $e = null)
    {
        $this->actor_desc_3->show();
        $this->actor_label_3->show();
        $this->alex_desc_3->show();
        $this->alex_label_3->show();
        Element::setText($this->answer_desc, "Фу изврощенес.. погнали драться!!!");
        $this->answer_2->hide();        
        $this->answer_3->show();              
    }

    /**
     * @event answer_3.click-Left 
     */
    function Talk_3(UXMouseEvent $e = null)
    {                
        Media::open('res://.data/audio/fight/vibeman.wav', true, "fight_sound");     
        $this->form('maingame')->idle_static_actor->hide();
        $this->form('maingame')->idle_static_enemy->hide(); 
        $this->form('maingame')->fight_label->show();               
        //очищаем игру
        $this->form('maingame')->dlg_btn->hide();
        $this->form('maingame')->HideDialog();                 
        $this->answer_3->hide();            
    }
    function ClearDialog()
    {
        $this->actor_desc_1->hide();
        $this->actor_label_1->hide();
        $this->alex_desc_2->hide();
        $this->alex_label_2->hide();    
        $this->actor_desc_3->hide();
        $this->actor_label_3->hide();
        $this->alex_desc_3->hide();
        $this->alex_label_3->hide();        
    }
}
