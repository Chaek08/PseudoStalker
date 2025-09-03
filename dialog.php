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
    
    private function preferValue($value, string $fallbackKey = '', string $default = ''): string
    {
        $value = trim((string)$value);
    
        if ($value != '')
        {
            return $value;
        }
    
        if ($default != '')
        {
            return $default;
        }
    
        if ($fallbackKey != '')
        {
            return $this->localization->get($fallbackKey);
        }
    
        return '';
    }
    
    private function ensureDialogContainer(): void
    {
        if (!($this->Dialog_Kunteynir->content instanceof UXVBox))
        {
            $box = new UXVBox();
            $box->spacing = 6;
            $box->alignment = "TOP_LEFT";
            $this->Dialog_Kunteynir->content = $box;
        }
        $this->dialogContainer = $this->Dialog_Kunteynir->content;
    }   
    
    function addDialogMessage(string $name, string $color, string $iconPath, string $text): void
    {
        $this->ensureDialogContainer();
    
        $block = new UXVBox();
        $block->spacing = 5;
        $block->padding = 5;
        $block->useMaxWidth = true;
    
        $nameRow = new UXHBox();
        $nameRow->spacing = 6;
        $nameRow->alignment = 'CENTER_LEFT';
    
        $nameLabel = new UXLabel($name);
        $nameLabel->textColor = $color;
        $nameLabel->font->size = 25;
    
        $icon = new UXImageView(new UXImage($iconPath));
        $icon->width = $icon->height = 24;
    
        $nameRow->add($nameLabel);
        $nameRow->add($icon);
    
        $textLabel = new UXLabel($text);
        $textLabel->wrapText = true;
        $textLabel->useMaxWidth = true;
        $textLabel->font->size = 19;
        $textLabel->textColor = '#cccccc';
    
        $block->add($nameRow);
        $block->add($textLabel);
    
        $this->dialogContainer->add($block);
    
        uiLater(function () {
            $this->Dialog_Kunteynir->vvalue = 1.0;
        });
    } 
    
    function StartDialog()
    {
        $this->ClearDialog();
        
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
    
        $enemy_name = $this->preferValue($this->form('Client')->Pda->content->SDK_EnemyName, 'Enemy_Name');
        $pido_role_color = $this->preferValue($this->form('Client')->Pda->content->SDK_PidoRoleColor, '', '#16a4cd');
        $pido_role_icon = $this->preferValue($this->form('Client')->Pda->content->SDK_PidoRoleIcon, '', 'res://.data/ui/dialog/pidoras_role.png');
        $alex_desc_1 = $this->preferValue($this->SDK_AlexDesc1, 'Dialog_Goblin_Desc1');
        $actor_desc_1 = $this->preferValue($this->SDK_ActorDesc1, 'Dialog_Actor_Desc1');
    
        $this->addDialogMessage($enemy_name, $pido_role_color, $pido_role_icon, $alex_desc_1);
        $this->answer_desc->text = $actor_desc_1;
    }

    function ClearDialog(): void
    {
        $this->ensureDialogContainer();
        
        foreach ($this->dialogContainer->children->toArray() as $child)
        {
            $this->dialogContainer->remove($child);
        }
    }
       
    function UpdateData()
    {
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
    
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
    
        $this->community_enemy->text = $pido_role_name != '' ? $pido_role_name : $this->localization->get('Community_Pido');
        $pidoIcon = $pido_role_icon != '' ? $pido_role_icon : 'res://.data/ui/dialog/pidoras_role.png';
        $this->community_enemy->graphic = new UXImageView(new UXImage($pidoIcon));
        $pidoColor = $pido_role_color != '' ? $pido_role_color : '#16a4cd';
        $this->community_enemy->textColor = $pidoColor;
    
        $this->community_actor->text = $de_role_name != '' ? $de_role_name : $this->localization->get('DE_Community');
        $deIcon = $de_role_icon != '' ? $de_role_icon : 'res://.data/ui/dialog/danila_emoji_role.png';
        $this->community_actor->graphic = new UXImageView(new UXImage($deIcon));
        $deColor = $de_role_color != '' ? $de_role_color : '#ee991a';
        $this->community_actor->textColor = $deColor;
    
        $this->icon_gg->image = new UXImage($actor_icon !== '' ? $actor_icon : 'res://.data/ui/icon_npc/actor.png');
        $this->icon_enemy->image = new UXImage($enemy_icon !== '' ? $enemy_icon : 'res://.data/ui/icon_npc/goblindav.png');
    
        $this->gg_name->text = $actor_name != '' ? $actor_name : $this->localization->get('GG_Name');
        $this->gg_name->textColor = $deColor;
    
        $this->enemy_name->text = $enemy_name != '' ? $enemy_name : $this->localization->get('Enemy_Name');
        $this->enemy_name->textColor = $pidoColor;
    
        $this->answer_name->text = $actor_name != '' ? $actor_name : $this->localization->get('GG_Name');
        $this->answer_name->textColor = $deColor;
        $this->answer_name->graphic = new UXImageView(new UXImage($deIcon));

        $scale_map_16 = [$this->community_actor, $this->community_enemy];
        
        foreach ($scale_map_16 as $el)
        {
            if ($el->graphic)
            {
                $g = $el->graphic;
                $g->width = $g->height = 16;
            }
        }
        
        if ($this->answer_name->graphic)
        {
            $g = $this->answer_name->graphic;
            $g->width = $g->height = 24;
        }    
    }

    function StopVoice()
    {
        $channels = ['voice_start', 'voice_talk1', 'voice_talk2', 'voice_talk3'];
    
        foreach ($channels as $ch) {
            if (Media::isStatus('PLAYING', $ch)) {
                Media::stop($ch);
            }
        }
    }
    
    private function playVoiceAsync($fileName, $mediaId)
    {
        $languageCode = $this->localization->getCurrentLanguage();
        $soundPath = "./gamedata/sounds/{$languageCode}/dialog/{$fileName}.mp3";
    
        if (!file_exists($soundPath))
        {
            throw new \Exception("Sound file not found: $soundPath");
        }
    
        (new Thread(function() use ($soundPath, $mediaId) {
            Media::open($soundPath, true, $mediaId);
        }))->start();
    }
    
    private function playVoice(string $sdkPath, string $fileName, string $mediaId)
    {
        $path = trim($sdkPath);
    
        if ($GLOBALS['AllSounds'])
        {
            $this->StopVoice();
            
            if ($path != '')
            {
                $this->form('Client')->playSoundAsync($path, $mediaId);
            }
            else
            {
                $this->playVoiceAsync($fileName, $mediaId);
            }
        }
    }
    
    function VoicePlay(int $index)
    {
        $map = [
            0 => [$this->SDK_VoiceStart, "voice_start"],
            1 => [$this->SDK_VoiceTalk1, "voice_talk1"],
            2 => [$this->SDK_VoiceTalk2, "voice_talk2"],
            3 => [$this->SDK_VoiceTalk3, "voice_talk3"],
        ];
    
        if (isset($map[$index]))
        {
            [$sdk, $id] = $map[$index];
            $this->playVoice($sdk, $id, $id);
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
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
    
        $actor_name       = $this->preferValue($this->form('Client')->Pda->content->SDK_ActorName, 'GG_Name');
        $actor_role_icon  = $this->preferValue($this->form('Client')->Pda->content->SDK_DeRoleIcon, '', 'res://.data/ui/dialog/danila_emoji_role.png');
        $actor_role_color = $this->preferValue($this->form('Client')->Pda->content->SDK_DeRoleColor, '', '#ee991a');
    
        $enemy_name       = $this->preferValue($this->form('Client')->Pda->content->SDK_EnemyName, 'Enemy_Name');
        $enemy_role_icon  = $this->preferValue($this->form('Client')->Pda->content->SDK_PidoRoleIcon, '', 'res://.data/ui/dialog/pidoras_role.png');
        $enemy_role_color = $this->preferValue($this->form('Client')->Pda->content->SDK_PidoRoleColor, '', '#16a4cd');
    
        $actor_desc_1     = $this->preferValue($this->SDK_ActorDesc3, 'Dialog_Actor_Desc1');
        $actor_desc_3     = $this->preferValue($this->SDK_ActorDesc3, 'Dialog_Actor_Desc3');
        $goblin_desc_2    = $this->preferValue($this->SDK_AlexDesc2, 'Dialog_Goblin_Desc2');
    
        $this->answer_desc->text = $actor_desc_3;
    
        if ($GLOBALS['AllSounds'])
        {
            $this->StopVoice();
            $this->VoicePlay(1);
        }
    
        $this->addDialogMessage($actor_name, $actor_role_color, $actor_role_icon, $actor_desc_1);
        $this->addDialogMessage($enemy_name, $enemy_role_color, $enemy_role_icon, $goblin_desc_2);
    }
    
    function Talk_2()
    {
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
    
        $actor_name       = $this->preferValue($this->form('Client')->Pda->content->SDK_ActorName, 'GG_Name');
        $actor_role_icon  = $this->preferValue($this->form('Client')->Pda->content->SDK_DeRoleIcon, '', 'res://.data/ui/dialog/danila_emoji_role.png');
        $actor_role_color = $this->preferValue($this->form('Client')->Pda->content->SDK_DeRoleColor, '', '#ee991a');
    
        $enemy_name       = $this->preferValue($this->form('Client')->Pda->content->SDK_EnemyName, 'Enemy_Name');
        $enemy_role_icon  = $this->preferValue($this->form('Client')->Pda->content->SDK_PidoRoleIcon, '', 'res://.data/ui/dialog/pidoras_role.png');
        $enemy_role_color = $this->preferValue($this->form('Client')->Pda->content->SDK_PidoRoleColor, '', '#16a4cd');
    
        $final_phase   = $this->preferValue($this->SDK_FinalPhase, 'Dialog_Final_Phase');
        $actor_desc_3  = $this->preferValue($this->SDK_ActorDesc3, 'Dialog_Actor_Desc3');
        $goblin_desc_3 = $this->preferValue($this->SDK_AlexDesc3, 'Dialog_Goblin_Desc3');
    
        $this->answer_desc->text = $final_phase;
    
        if ($GLOBALS['AllSounds'])
        {
            $this->StopVoice();
            $this->VoicePlay(2);
        }
    
        $this->addDialogMessage($actor_name, $actor_role_color, $actor_role_icon, $actor_desc_3);
        $this->addDialogMessage($enemy_name, $enemy_role_color, $enemy_role_icon, $goblin_desc_3);
    }

    function Talk_3()
    {       
        $this->form('Client')->HideDialog();
        $this->form('Client')->MainGame->content->RenderHud(true);
        
        if ($GLOBALS['AllSounds'])
        {
            $this->VoicePlay(3);
        }
        if ($GLOBALS['FightSound'])
        {
            $this->form('Client')->MainGame->content->PlayFightSong();
        }
            
        $this->form('Client')->Pda->content->Pda_Tasks->content->Step1_Complete();

        $this->form('Client')->MainGame->content->GameActor->SetInteractive(true);
        
        $this->form('Client')->MainGame->content->item_vodka_0000->enabled = true;

        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
        $GLOBALS['discord']->setState($this->localization->get('RPC_Fight'));
        $GLOBALS['discord']->updateState();      
    }
}
