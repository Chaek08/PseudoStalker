<?php
namespace app\forms;

use app\forms\classes\UI\UICharacterInfo;
use Throwable;
use php\gui\UXImageView;
use php\gui\UXImage;
use std, gui, framework, app;
use app\forms\classes\Localization;
use php\gui\event\UXMouseEvent; 

class dialog extends AbstractForm
{
    private $localization;
    
    private $actorCharacterInfo;
    private $enemyCharacterInfo;    
    
    public $answerStep = 0;
    
    public function __construct() 
    {
        parent::__construct();

        $this->localization = new Localization($language);
        
        uiLater(function(){
            $this->actorCharacterInfo = new UICharacterInfo($this, $this->localization, $this->icon_gg, $this->rank_actor, null, $this->community_actor, null, $this->gg_name);
            $this->actorCharacterInfo->setActor();
            
            $this->enemyCharacterInfo = new UICharacterInfo($this, $this->localization, $this->icon_enemy, $this->rank_enemy, null, $this->community_enemy, null, $this->enemy_name);
            $this->enemyCharacterInfo->setEnemy();    
        });         
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
            $box->spacing   = 6;
            $box->alignment = 'TOP_LEFT';
            $box->fillWidth = true;
            $box->useMaxWidth = true;
            $this->Dialog_Kunteynir->content = $box;
    
            $this->Dialog_Kunteynir->fitToWidth = true;
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
    
        (new Thread(function () use ($block) { //ёбанные в жопу потоки, ёбанный в жопу скролл, дима зайцев гондурас
            for ($i = 0; $i < 3; $i++)
            {
                usleep(50000);
                uiLater(function () use ($block) {
                    $this->Dialog_Kunteynir->scrollToNode($block);
                    $this->Dialog_Kunteynir->vvalue = 1;
                });
            }
        }))->start();
    }
    
    private $lastPhraseIndex = 0;
    
    private function getRandomPhrase(): string
    {
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
    
        $max = 56;
    
        do
        {
            $index = rand(1, $max);
        }
        while ($index == $this->lastPhraseIndex);
    
        $this->lastPhraseIndex = $index;
    
        return $this->localization->get("Dialog_Random_Phrase_{$index}");
    }
    
    function StartDialog()
    {
        $this->ClearDialog();
    
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
        
        $roles = new UIRoles($this, $this->localization);
        $actorRoleData = $roles->danilaEmoji($this->community_actor);
        $enemyRoleData = $roles->pidoras($this->community_enemy);        
        
        $this->dialogSteps = [];
        $rounds = rand(5, 25);
        $voiceIndex = 0;
    
        for ($r = 0; $r < $rounds; $r++)
        {
            $this->dialogSteps[] = [
                'speaker' => 'enemy',
                'name'    => $this->enemyCharacterInfo->name->text,
                'icon'    => $enemyRoleData['icon'],
                'color'   => $enemyRoleData['color'],
            ];
            
            $this->dialogSteps[] = [
                'speaker' => 'player',
                'name'    => $this->actorCharacterInfo->name->text,
                'icon'    => $actorRoleData['icon'],
                'color'   => $actorRoleData['color'],
            ];
        }
    
        $this->dialogSteps[] = ['speaker' => 'final'];
    
        $this->answerStep = 0;
        $this->advanceToNextPlayer();
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
        
        $this->actorCharacterInfo->setActor();
        $this->enemyCharacterInfo->setEnemy();
    
        $actorRoleData = (new UIRoles($this, $this->localization))->danilaEmoji($this->community_actor);
        $enemyRoleData = (new UIRoles($this, $this->localization))->pidoras($this->community_enemy);
        
        $this->gg_name->textColor = $actorRoleData['color']; 
        $this->enemy_name->textColor = $enemyRoleData['color'];    
    
        $this->answer_name->text = $this->gg_name->text;
        $this->answer_name->textColor = $actorRoleData['color'];
        $this->answer_name->graphic = new UXImageView(new UXImage($actorRoleData['icon']));
    
        foreach ([$this->community_actor, $this->community_enemy] as $el)
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
    
        foreach ($channels as $ch)
        {
            if (Media::isStatus('PLAYING', $ch))
            {
                Media::stop($ch);
            }
        }
    }
    
    private function playVoiceAsync($fileName, $mediaId)
    {
        $languageCode = $this->localization->getCurrentLanguage();
        $soundPath = "./gamedata/sounds/voice/{$languageCode}/dialog/{$fileName}.mp3";
    
        if (!file_exists($soundPath))
        {
            Debug::fatal("Sound file not found: $soundPath", __FILE__, __LINE__);
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
        if (!isset($this->dialogSteps[$this->answerStep]) || $this->dialogSteps[$this->answerStep]['speaker'] !== 'player')
        {
            $this->advanceToNextPlayer();
            if (!isset($this->dialogSteps[$this->answerStep]) || $this->dialogSteps[$this->answerStep]['speaker'] !== 'player')
            {
                return;
            }
        }
    
        $step = $this->dialogSteps[$this->answerStep];
    
        $playerPhrase = $this->answer_desc->text;
        $this->addDialogMessage($step['name'], $step['color'], $step['icon'], $playerPhrase);
        
        $this->answerStep++;
        $this->advanceToNextPlayer();
    }
        
    private function advanceToNextPlayer(): void
    {
        while (isset($this->dialogSteps[$this->answerStep]))
        {
            $step = $this->dialogSteps[$this->answerStep];
    
            if ($step['speaker'] == 'enemy')
            {
            /*
                if ($GLOBALS['AllSounds'] && isset($step['voice']))
                {
                    $this->StopVoice();
                    $this->VoicePlay($step['voice']);
                }
            */
                $this->addDialogMessage($step['name'], $step['color'], $step['icon'], $this->getRandomPhrase());
                $this->answerStep++;
                continue;
            }
    
            if ($step['speaker'] == 'player')
            {
                $this->answer_desc->text = $this->getRandomPhrase();
                return;
            }
    
            if ($step['speaker'] == 'final')
            {
                $this->Talk_Final();
                $this->answerStep = 0;
                return;
            }
        }
    }

    function Talk_Final()
    {       
        $this->form('Client')->HideDialog();
        $this->form('Client')->MainGame->content->RenderHud(true);
        /*
        if ($GLOBALS['AllSounds'])
        {
            $this->VoicePlay(3);
        }
        */
        $this->form('Client')->MainGame->content->PlayFightSong();
            
        $this->actorCharacterInfo->addRank(45);
            
        $this->form('Client')->Pda->content->Pda_Tasks->content->Step1_Complete();

        $this->form('Client')->MainGame->content->GameActor->SetInteractive(true);
        $this->form('Client')->MainGame->content->GameEnemy->SetInteractive(true);
                
        $this->form('Client')->MainGame->content->ItemVodka->enable();

        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
        
        if ($this->form('Client')->ltx->r_bool('discord_rpc'))
        {        
            $GLOBALS['discord']->setState($this->localization->get('RPC_Fight'));
            $GLOBALS['discord']->updateState();      
        }
    }
}
