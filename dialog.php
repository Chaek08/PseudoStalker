<?php
namespace app\forms;

use php\gui\UXImageView;
use php\gui\UXImage;
use std, gui, framework, app;
use app\forms\classes\Localization;
use php\gui\event\UXMouseEvent; 

class dialog extends AbstractForm
{
    private $localization;
    
    public $answerStep = 0;    
    
    public $SDK_VoiceStart;
    public $SDK_VoiceTalk1;
    public $SDK_VoiceTalk2;
    public $SDK_VoiceTalk3;
    
    public $SDK_AlexDesc1;
    public $SDK_AlexDesc2;
    public $SDK_AlexDesc3;    
    public $SDK_ActorDesc1;
    public $SDK_ActorDesc3;
    public $SDK_FinalPhase;
    
    public function __construct() 
    {
        parent::__construct();

        $this->localization = new Localization($language);
    }
    function getCurrentLanguageFromUI()
    {
        return $this->form('Client')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value;
    }
    function UpdateData()
    {
        $actor_icon = trim($this->form('Client')->Pda->content->SDK_ActorIcon);
        $actor_name = trim($this->form('Client')->Pda->content->SDK_ActorName);
        $enemy_icon = trim($this->form('Client')->Pda->content->SDK_EnemyIcon);
        $enemy_name = trim($this->form('Client')->Pda->content->SDK_EnemyName);
        
        $pido_role_name = trim($this->form('Client')->Pda->content->SDK_PidoRoleName);
        $pido_role_color = trim($this->form('Client')->Pda->content->SDK_PidoRoleColor);
        $pido_role_icon = trim($this->form('Client')->Pda->content->SDK_PidoRoleIcon);
        $de_role_name = trim($this->form('Client')->Pda->content->SDK_DeRoleName);
        $de_role_color = trim($this->form('Client')->Pda->content->SDK_DeRoleColor);
        $de_role_icon = trim($this->form('Client')->Pda->content->SDK_DeRoleIcon);
        
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
        
        $this->community_enemy->text = $pido_role_name != '' ? $pido_role_name : $this->localization->get('Community_Pido');

        $pidoIcon = $pido_role_icon != '' ? $pido_role_icon : 'res://.data/ui/dialog/pidoras_role.png';
        $this->community_enemy->graphic = new UXImageView(new UXImage($pidoIcon));
        $this->alex_label_1->graphic = new UXImageView(new UXImage($pidoIcon));
        $this->alex_label_2->graphic = new UXImageView(new UXImage($pidoIcon));
        $this->alex_label_3->graphic = new UXImageView(new UXImage($pidoIcon));

        $pidoColor = $pido_role_color != '' ? $pido_role_color : '#16a4cd';
        $this->enemy_name->textColor = $pidoColor;
        $this->community_enemy->textColor = $pidoColor;
        $this->alex_label_1->textColor = $pidoColor;
        $this->alex_label_2->textColor = $pidoColor;
        $this->alex_label_3->textColor = $pidoColor;

        $this->community_actor->text = $de_role_name != '' ? $de_role_name : $this->localization->get('DE_Community');

        $deIcon = $de_role_icon != '' ? $de_role_icon : 'res://.data/ui/dialog/danila_emoji_role.png';
        $this->community_actor->graphic = new UXImageView(new UXImage($deIcon));
        $this->actor_label_1->graphic = new UXImageView(new UXImage($deIcon));
        $this->actor_label_3->graphic = new UXImageView(new UXImage($deIcon));
        $this->answer_name->graphic = new UXImageView(new UXImage($deIcon));

        $deColor = $de_role_color != '' ? $de_role_color : '#ee991a';
        $this->gg_name->textColor = $deColor;
        $this->community_actor->textColor = $deColor;
        $this->actor_label_1->textColor = $deColor;
        $this->actor_label_3->textColor = $deColor;
        $this->answer_name->textColor = $deColor;
        
        $this->icon_gg->image = new UXImage($actor_icon !== '' ? $actor_icon : 'res://.data/ui/icon_npc/actor.png');
        $this->icon_enemy->image = new UXImage($enemy_icon !== '' ? $enemy_icon : 'res://.data/ui/icon_npc/goblindav.png');
        
        if ($actor_name != '')
        {
            $this->gg_name->text = $actor_name;
            $this->actor_label_1->text = $actor_name;
            $this->actor_label_3->text = $actor_name;
            $this->answer_name->text = $actor_name;
        }
        else
        {
            $this->gg_name->text = $this->localization->get('GG_Name');
            $this->actor_label_1->text = $this->localization->get('GG_Name');
            $this->actor_label_3->text = $this->localization->get('GG_Name');
            $this->answer_name->text = $this->localization->get('GG_Name');
        }
        if ($enemy_name != '')
        {
            $this->enemy_name->text = $enemy_name;
            $this->alex_label_1->text = $enemy_name;
            $this->alex_label_2->text = $enemy_name;
            $this->alex_label_3->text = $enemy_name;
        }
        else
        {
            $this->enemy_name->text = $this->localization->get('Enemy_Name');
            $this->alex_label_1->text = $this->localization->get('Enemy_Name');
            $this->alex_label_2->text = $this->localization->get('Enemy_Name');
            $this->alex_label_3->text = $this->localization->get('Enemy_Name');
        }      
        
        $alex_desc_1 = trim($this->SDK_AlexDesc1);
        $actor_desc_1 = trim($this->SDK_ActorDesc1);
        $alex_desc_2 = trim($this->SDK_AlexDesc2);
        $actor_desc_3 = trim($this->SDK_ActorDesc3);
        $alex_desc_3 = trim($this->SDK_AlexDesc3);
        
        $this->alex_desc_1->text = $alex_desc_1 != '' ? $alex_desc_1 : $this->localization->get('Dialog_Goblin_Desc1');
        $this->actor_desc_1->text = $actor_desc_1 != '' ? $actor_desc_1 : $this->localization->get('Dialog_Actor_Desc1');
        $this->alex_desc_2->text = $alex_desc_2 != '' ? $alex_desc_2 : $this->localization->get('Dialog_Goblin_Desc2');
        $this->actor_desc_3->text = $actor_desc_3 != '' ? $actor_desc_3 : $this->localization->get('Dialog_Actor_Desc3');
        $this->alex_desc_3->text = $alex_desc_3 != '' ? $alex_desc_3 : $this->localization->get('Dialog_Goblin_Desc3');
        
        $scale_map = [
            16 => [$this->community_actor, $this->community_enemy],
            24 => [
                $this->alex_label_1, $this->alex_label_2, $this->alex_label_3,
                $this->actor_label_1, $this->actor_label_3, $this->answer_name
            ]
        ];

        foreach ($scale_map as $size => $elements)
        {
            foreach ($elements as $el)
            {
                $g = $el->graphic;
                $g->width = $g->height = $size;
            }
        }       
    }
    
    private function playVoiceAsync($fileName, $mediaId)
    {
        $languageCode = $this->localization->getCurrentLanguage();
        $soundPath = "./gamedata/sounds/{$languageCode}/dialog/{$fileName}.mp3";
    
        if (!file_exists($soundPath)) {
            throw new \Exception("Sound file not found: $soundPath");
        }
    
        (new Thread(function() use ($soundPath, $mediaId) {
            Media::open($soundPath, true, $mediaId);
        }))->start();
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
            $path = trim($this->SDK_VoiceStart);
        
            if ($path != '')
            {
                $this->form('Client')->playSoundAsync($this->SDK_VoiceStart, "voice_start");
            }
            else 
            {
                $this->playVoiceAsync("voice_start", "voice_start");
            }
        }
    }
    function VoiceTalk_1()
    {
        $path = trim($this->SDK_VoiceTalk1);
        
        if ($path != '')
        {
            $this->form('Client')->playSoundAsync($this->SDK_VoiceTalk1, "voice_talk1");
        }
        else
        {
            $this->playVoiceAsync("voice_talk1", "voice_talk1");
        }
    }
    function VoiceTalk_2()
    {
        $path = trim($this->SDK_VoiceTalk2);
        
        if ($path != '')
        {
            $this->form('Client')->playSoundAsync($this->SDK_VoiceTalk2, "voice_talk2");
        }
        else
        {    
            $this->playVoiceAsync("voice_talk2", "voice_talk2");
        }   
    }    
    function VoiceTalk_3()
    {
        $path = trim($this->SDK_VoiceTalk3);
        
        if ($path != '')
        {
            Media::open($this->SDK_VoiceTalk3, true, "voice_talk3");
        }
        else
        {    
            $this->playVoiceAsync("voice_talk3", "voice_talk3");
        }   
    }    
    
    /**
     * @event answer_desc.click-Left 
     */
    function EnterAnswer(UXMouseEvent $e = null)
    {    
        $this->answerStep++;

        if ($this->answerStep == 1)
        {
            $this->Talk_1();
        }
        elseif ($this->answerStep == 2)
        {
            $this->Talk_2();
        }
        elseif ($this->answerStep == 3)
        {
            $this->Talk_3();
            
            $this->answerStep = 0;
        }        
    }    

    function Talk_1()
    {    
        $path = trim($this->SDK_ActorDesc3);
        
        if ($path != '')
        {
            $this->answer_desc->text = $this->SDK_ActorDesc3;

        }
        else
        {          
            $this->localization->setLanguage($this->getCurrentLanguageFromUI());
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
    }

    function Talk_2()
    {
        $path = trim($this->SDK_FinalPhase);
        
        if ($path != '')
        {
            $this->answer_desc->text = $this->SDK_FinalPhase;
        }
        else    
        {
            $this->localization->setLanguage($this->getCurrentLanguageFromUI());
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
    }

    function Talk_3()
    {       
        $this->form('Client')->HideDialog();
        $this->form('Client')->MainGame->content->RenderHud(true);
        
        if ($GLOBALS['AllSounds'])
        {
            $this->VoiceTalk_3();
        }
        if ($GLOBALS['FightSound'])
        {
            $this->form('Client')->MainGame->content->PlayFightSong();
        }
        
        $this->form('Client')->MainGame->content->idle_static_actor->hide();
        $this->form('Client')->MainGame->content->idle_static_enemy->hide(); 
            
        $this->form('Client')->Pda->content->Pda_Tasks->content->Step1_Complete();
        
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
        $GLOBALS['discord']->setState($this->localization->get('RPC_Fight'));
        $GLOBALS['discord']->updateState();      
    }

    function StartDialog()
    {
        $path = trim($this->SDK_ActorDesc1);
        
        if ($path != '')
        {
            $this->answer_desc->text = $this->SDK_ActorDesc1;
        }
        else 
        {
            $this->localization->setLanguage($this->getCurrentLanguageFromUI());
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
}
