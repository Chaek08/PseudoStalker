<?php
namespace app\forms;

use std, gui, framework, app;

class dialog extends AbstractForm
{
    /**
     * @event show 
     */
    function InitDialogWnd(UXWindowEvent $e = null)
    {    
        
    }
    function StopVoice()
    {
        Media::stop('voice_start');
        Media::stop('voice_talk1');
        Media::stop('voice_talk2');
        Media::stop('voice_talk3');                   
    }
    function VoiceStart()
    {
        if ($this->form('maingame')->SDK_Mode->visible)
        {
            Media::open($this->form('sdk_main')->f_DialogEditor->content->Edit_VoiceStart->text, true, "voice_start");
        }
        else
        {
            Media::open('res://.data/audio/voice/voice_start.mp3', true, "voice_start");            
        }
    }
    function VoiceTalk_1()
    {
        if ($this->form('maingame')->SDK_Mode->visible)
        {
            Media::open($this->form('sdk_main')->f_DialogEditor->content->Edit_VoiceTalk1->text, true, "voice_talk1");
        }
        else
        {
            Media::open('res://.data/audio/voice/voice_talk1.mp3', true, "voice_talk1");            
        }
    }
    function VoiceTalk_2()
    {
        if ($this->form('maingame')->SDK_Mode->visible)
        {
            Media::open($this->form('sdk_main')->f_DialogEditor->content->Edit_VoiceTalk2->text, true, "voice_talk2");
        }
        else
        {    
            Media::open('res://.data/audio/voice/voice_talk2.mp3', true, "voice_talk2");   
        }   
    }    
    function VoiceTalk_3()
    {
        if ($this->form('maingame')->SDK_Mode->visible)
        {
            Media::open($this->form('sdk_main')->f_DialogEditor->content->Edit_VoiceTalk3->text, true, "voice_talk3");
        }
        else
        {    
            Media::open('res://.data/audio/voice/voice_talk3.mp3', true, "voice_talk3");     
        }   
    }    
    /**
     * @event answer_1_new.click-Left 
     */
    function Talk_1(UXMouseEvent $e = null)
    {    
        if ($this->form('maingame')->SDK_Mode->visible)
        {
            Element::setText($this->answer_desc, uiText($this->form('sdk_main')->f_DialogEditor->content->Edit_Actor_Desc_3->text));
        }
        else
        {
            Element::setText($this->answer_desc, "иди нахуй заднипривадный геюган");            
        }
        
        if ($this->form('maingame')->fragment_opt->content->all_sounds->visible)
        {
            $this->StopVoice();
            $this->VoiceTalk_1();              
        }  
            
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
        if ($this->form('maingame')->SDK_Mode->visible)
        {
            Element::setText($this->answer_desc, uiText($this->form('sdk_main')->f_DialogEditor->content->Edit_Final_Phase->text));
        }
        else    
        {
            Element::setText($this->answer_desc, "фу изврощенис.. пагнали дратся!!!");            
        }
        
        if ($this->form('maingame')->fragment_opt->content->all_sounds->visible)
        {        
           $this->StopVoice();        
           $this->VoiceTalk_2();
        }
            
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
        $this->form('maingame')->HideDialog();          
        if ($this->form('maingame')->fragment_opt->content->all_sounds->visible)
        {
            $this->VoiceTalk_3();
            if ($this->form('maingame')->fragment_opt->content->mute_fight_sound->visible) {} else
            {
                Media::open('res://.data/audio/fight/fight_sound.mp3', true, "fight_sound");
                Media::pause('main_ambient');                
            }     
        }
        $this->form('maingame')->idle_static_actor->hide();
        $this->form('maingame')->idle_static_enemy->hide(); 
        $this->form('maingame')->fight_image->show();               
        $this->form('maingame')->dlg_btn->hide();    
        $this->form('maingame')->fragment_pda->content->fragment_tasks->content->Step1_Complete();                       
    }
    function StartDialog()
    {
        $this->answer_desc->opacity = 100;
        $this->answer_1_new->show();
        if ($this->form('maingame')->SDK_Mode->visible)
        {
            $this->answer_desc->text = $this->form('sdk_main')->f_DialogEditor->content->Edit_Actor_Desc_1->text;
        }
        else 
        {
            $this->answer_desc->text = "ахуел гей нефар";
        }
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
