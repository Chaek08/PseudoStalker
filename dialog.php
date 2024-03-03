<?php
namespace app\forms;

use std, gui, framework, app;

class dialog extends AbstractForm
{
    /**
     * @event show 
     */
    function ShowDialogWnd(UXWindowEvent $e = null)
    {    
        $this->StartDialog();
    }
    function StopVoice()
    {
        if ($this->form('maingame')->fragment_opt->content->sound->visible)
        {
            Media::stop('voice_start');
            Media::stop('voice_talk1');
            Media::stop('voice_talk2');
            Media::stop('voice_talk3');    
        }          
    }
    function VoiceStart()
    {
        if ($this->form('maingame')->fragment_opt->content->sound->visible)
        {
            Media::open('res://.data/audio/voice/voice_start.mp3', true, "voice_start");
        }    
    }
    function VoiceTalk_1()
    {
        if ($this->form('maingame')->fragment_opt->content->sound->visible)
        {
            Media::open('res://.data/audio/voice/voice_talk1.mp3', true, "voice_talk1");
        }           
    }
    function VoiceTalk_2()
    {
        if ($this->form('maingame')->fragment_opt->content->sound->visible)
        {
            Media::open('res://.data/audio/voice/voice_talk2.mp3', true, "voice_talk2");
        }           
    }    
    function VoiceTalk_3()
    {
        if ($this->form('maingame')->fragment_opt->content->sound->visible)
        {
            Media::open('res://.data/audio/voice/voice_talk3.mp3', true, "voice_talk3");
        }           
    }    
    /**
     * @event answer_1_new.click-Left 
     */
    function Talk_1(UXMouseEvent $e = null)
    {    
        Element::setText($this->answer_desc, "Ахуел твой муж, когда узнал что ты скоро станешь натуралом!!!");
        $this->StopVoice();
        $this->VoiceTalk_1();    
            
        $this->actor_desc_1->show();
        $this->actor_label_1->show();
        $this->alex_desc_2->show();
        $this->alex_label_2->show();
        $this->ResetAnswerVisible();          
        $this->answer_2_new->show();     
    }
    /**
     * @event answer_2_new.click-Left 
     */
    function Talk_2(UXMouseEvent $e = null)
    {
        Element::setText($this->answer_desc, "Фу изврощенес.. погнали драться!!!");
        $this->StopVoice();        
        $this->VoiceTalk_2();
            
        $this->actor_desc_3->show();
        $this->actor_label_3->show();
        $this->alex_desc_3->show();
        $this->alex_label_3->show();
        $this->ResetAnswerVisible();       
        $this->answer_3_new->show();              
    }
    /**
     * @event answer_3_new.click-Left 
     */
    function Talk_3(UXMouseEvent $e = null)
    {       
        $this->StopVoice();    
        $this->VoiceTalk_3();         
        if ($this->form('maingame')->fragment_opt->content->sound->visible)
        {
            Media::open('res://.data/audio/fight/new_track.mp3', true, "fight_sound");
            Media::pause('main_ambient');
        }    
        $this->form('maingame')->idle_static_actor->hide();
        $this->form('maingame')->idle_static_enemy->hide(); 
        $this->form('maingame')->fight_label->show();               
        $this->form('maingame')->dlg_btn->hide();
        $this->form('maingame')->HideDialog();    
        $this->form('maingame')->fragment_pda->content->fragment_tasks->content->Step1_Complete();             
        $this->ResetAnswerVisible();           
    }
    function StartDialog()
    {
        $this->answer_1_new->show(); 
        $this->answer_desc->text = "Даю тебе зелье натурала!";    
        $this->ClearDialog();        
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
    function ResetAnswerVisible()
    {
        $this->answer_1_new->hide();
        $this->answer_2_new->hide();
        $this->answer_3_new->hide();
    }
}
