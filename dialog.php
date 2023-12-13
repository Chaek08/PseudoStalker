<?php
namespace app\forms;

use std, gui, framework, app;

// подсказка для дайлогов actor_label_1, actor_desc_1

class dialog extends AbstractForm
{
    /**
     * @event answer_1_new.click-Left 
     */
    function Talk_1(UXMouseEvent $e = null)
    {    
        $this->actor_desc_1->show();
        $this->actor_label_1->show();
        $this->alex_desc_2->show();
        $this->alex_label_2->show();
        Element::setText($this->answer_desc, "Ахуел твой муж, когда узнал что ты скоро станешь натуралом!!!");
        $this->ResetAnswerVisible();          
        $this->answer_2_new->show();     
    }

    /**
     * @event answer_2_new.click-Left 
     */
    function Talk_2(UXMouseEvent $e = null)
    {
        $this->actor_desc_3->show();
        $this->actor_label_3->show();
        $this->alex_desc_3->show();
        $this->alex_label_3->show();
        Element::setText($this->answer_desc, "Фу изврощенес.. погнали драться!!!");
        $this->ResetAnswerVisible();       
        $this->answer_3_new->show();              
    }

    /**
     * @event answer_3_new.click-Left 
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
        $this->ResetAnswerVisible();           
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
    //заебали блять ебаные диалоги, тупые кнопки 
    //исправим эту проблему, раз и навсегда
    function ResetAnswerVisible()
    {
        $this->answer_1_new->hide();
        $this->answer_2_new->hide();
        $this->answer_3_new->hide();
    }
}
