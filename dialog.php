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
    
    private function playVoice($fileName, $mediaId)
    {
        $languageCode = $this->localization->getCurrentLanguage();

        $soundPath = "./gamedata/sounds/{$languageCode}/dialog/{$fileName}.mp3";

        if (file_exists($soundPath))
        {
            Media::open($soundPath, true, $mediaId);
        } 
        else
        {
            throw new \Exception("Sound file not found: $soundPath");
        }
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
        if ($GLOBALS['AllSounds'])
        {   
            if (SDK_Mode)
            {
                Media::open($this->form('maingame')->Editor->content->f_DialogEditor->content->Edit_VoiceStart->text, true, "voice_start");
            }
            else 
            {
                $this->playVoice("voice_start", "voice_start");
            }
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
            $this->playVoice("voice_talk1", "voice_talk1");
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
            $this->playVoice("voice_talk2", "voice_talk2");
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
            $this->playVoice("voice_talk3", "voice_talk3");
        }   
    }    
    /**
     * @event answer_1_new.click-Left 
     */
    function Talk_1(UXMouseEvent $e = null)
    {    
        $this->localization->setLanguage($this->form('maingame')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value);
        if (SDK_Mode)
        {
            $this->answer_desc->text = $this->form('maingame')->Editor->content->f_DialogEditor->content->Edit_Actor_Desc_3->text ?:
                                       $this->form('maingame')->Editor->content->f_DialogEditor->content->Edit_Actor_Desc_3->promptText;

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
        $this->localization->setLanguage($this->form('maingame')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value);
        if (SDK_Mode)
        {
            $this->answer_desc->text = $this->form('maingame')->Editor->content->f_DialogEditor->content->Edit_Final_Phase->text ?:
                                       $this->form('maingame')->Editor->content->f_DialogEditor->content->Edit_Final_Phase->promptText;
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
        if (!$GLOBALS['HudVisible']) $this->form('maingame')->ToggleHud();
        
        if ($GLOBALS['AllSounds'])
        {
            $this->VoiceTalk_3();
        }
        if ($GLOBALS['FightSound'])
        {
            $this->form('maingame')->PlayFightSong();
        }
        
        $this->form('maingame')->idle_static_actor->hide();
        $this->form('maingame')->idle_static_enemy->hide(); 
        $this->form('maingame')->fight_image->show();               
        $this->form('maingame')->Talk_Label->hide();
            
        $this->form('maingame')->Pda->content->Pda_Tasks->content->Step1_Complete();                       
    }
    function StartDialog()
    {
        $this->localization->setLanguage($this->form('maingame')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value);
        $this->answer_1_new->show();
        if (SDK_Mode)
        {
            $this->answer_desc->text = $this->form('maingame')->Editor->content->f_DialogEditor->content->Edit_Actor_Desc_1->text ?:
                                       $this->form('maingame')->Editor->content->f_DialogEditor->content->Edit_Actor_Desc_1->promptText;            
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
