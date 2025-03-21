<?php
namespace app\forms;

use std, gui, framework, app;
use app\forms\classes\Localization;

class dialog extends AbstractForm
{
    private $localization;

    public function __construct() 
    {
        parent::__construct();

        $this->localization = new Localization($language);
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
        if (SDK_Mode && $GLOBALS['AllSounds'])
        {   
            Media::open($this->form('maingame')->Editor->content->f_DialogEditor->content->Edit_VoiceStart->text, true, "voice_start");
        }
        else if (SDK_Mode)
        {
            return;
        }
        if ($GLOBALS['AllSounds'])//else
        {
            Media::open('res://.data/audio/voice/voice_start.mp3', true, "voice_start");
        }
    }
    function VoiceTalk_1()
    {
        if (SDK_Mode)
        {
            Media::open($this->form('maingame')->Editor->content->f_DialogEditor->content->Edit_VoiceTalk1->text, true, "voice_talk1");
        }
        else
        {
            Media::open('res://.data/audio/voice/voice_talk1.mp3', true, "voice_talk1");            
        }
    }
    function VoiceTalk_2()
    {
        if (SDK_Mode)
        {
            Media::open($this->form('maingame')->Editor->content->f_DialogEditor->content->Edit_VoiceTalk2->text, true, "voice_talk2");
        }
        else
        {    
            Media::open('res://.data/audio/voice/voice_talk2.mp3', true, "voice_talk2");   
        }   
    }    
    function VoiceTalk_3()
    {
        if (SDK_Mode)
        {
            Media::open($this->form('maingame')->Editor->content->f_DialogEditor->content->Edit_VoiceTalk3->text, true, "voice_talk3");
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
        $this->localization->setLanguage($this->form('maingame')->Options->content->Language_Switcher_Combobobx->value);
        if (SDK_Mode)
        {
            Element::setText($this->answer_desc, uiText($this->form('maingame')->Editor->content->f_DialogEditor->content->Edit_Actor_Desc_3));
        }
        else
        {          
            $this->answer_desc->text = $this->localization->get('Dialog_Actor_Desc3');
        }
        
        if ($GLOBALS['AllSounds'])
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
        $this->localization->setLanguage($this->form('maingame')->Options->content->Language_Switcher_Combobobx->value);
        if (SDK_Mode)
        {
            Element::setText($this->answer_desc, uiText($this->form('maingame')->Editor->content->f_DialogEditor->content->Edit_Final_Phase));
        }
        else    
        {
            $this->answer_desc->text = $this->localization->get('Dialog_Final_Phase');
        }
        
        if ($GLOBALS['AllSounds'])
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
        
        if (ToggleHudFeature) $this->form('maingame')->ToggleHud();
        
        if ($GLOBALS['AllSounds'])
        {
            $this->VoiceTalk_3();
            
            Media::pause('main_ambient');
        }
        if ($GLOBALS['FightSound'])
        {
            $this->form('maingame')->ReplayFightSong();
            
            $this->form('maingame')->ReplayBtn->show();
        }
        
        $this->form('maingame')->idle_static_actor->hide();
        $this->form('maingame')->idle_static_enemy->hide(); 
        $this->form('maingame')->fight_image->show();               
        $this->form('maingame')->dlg_btn->hide();
            
        $this->form('maingame')->Pda->content->Pda_Tasks->content->Step1_Complete();                       
    }
    function StartDialog()
    {
        $this->localization->setLanguage($this->form('maingame')->Options->content->Language_Switcher_Combobobx->value);
        $this->answer_1_new->show();
        if (SDK_Mode)
        {
            $this->answer_desc->text = $this->form('maingame')->Editor->content->f_DialogEditor->content->Edit_Actor_Desc_1->text;
        }
        else 
        {
            $this->answer_desc->text = $this->localization->get('Dialog_Actor_Desc1');
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
