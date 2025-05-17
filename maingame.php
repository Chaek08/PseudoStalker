<?php
namespace app\forms;

use php\concurrent\Future;
use std, gui, framework, app;
use action\Geometry;
use script\MediaPlayerScript;
use php\gui\event\UXKeyEvent; 
use php\gui\event\UXMouseEvent; 
use php\framework\Logger;
use app\forms\classes\Localization;

class maingame extends AbstractForm
{
    private $localization;
    private $currentCycle = '';
    
    /**
     * @event show 
     */
    function InitClient(UXWindowEvent $e = null)
    {
        define('VersionID', 'v1.3 (rc2)');
        define('client_version', '3');
        define('Debug_Build', true);
        define('SDK_Mode', false);

        $GLOBALS['AllSounds']  = true;
        $GLOBALS['MenuSound']  = true;
        $GLOBALS['FightSound'] = true;
        $GLOBALS['HudVisible'] = true;
        
        $this->localization = new Localization($language);        

        $this->GetVersion();

        $this->MainMenu->content->InitMainMenu();

        if (SDK_Mode)
        {
            $this->MainMenu->content->opensdk_btn->show();
        }
        else
        {
            $this->Editor->free();
            $this->MainMenu->content->opensdk_btn->free();
        }

        $this->MainMenu->content->Options->content->InitOptions();

        $this->currentCycle = '';
        $this->UpdateEnvironment();
        
        $this->InitUserLTX();
    }
    
    public $ltx = [];
    public $ltxInitialized = false;
    function InitUserLTX()
    {
        $path = 'user.ltx';

        $default = [
            'language' => 'rus',
            'r_shadows' => 'on',
            'r_version' => 'on'
        ];

        if (!file_exists($path))
        {
            $this->SaveUserLTX($path, $default);
            $this->ltx = $default;
        }
        else
        {
            $config = [];

            $lines = file($path);
            foreach ($lines as $line)
            {
                $parts = explode(' ', trim($line));
                if (count($parts) >= 2)
                {
                    $key = $parts[0];
                    $value = $parts[1];
                    $config[$key] = $value;
                }
            }

            $allKeysExist = true;
            foreach (array_keys($default) as $key)
            {
                if (!isset($config[$key]) || $config[$key] == '')
                {
                    $allKeysExist = false;
                    break;
                }
            }

            if (!$allKeysExist)
            {
                foreach ($default as $key => $value)
                {
                    if (!isset($config[$key]) || $config[$key] == '')
                    {
                        $config[$key] = $value;
                    }
                }
                $this->SaveUserLTX($path, $config);
            }

            $this->ltx = $config;
        }

        $this->ltxInitialized = true;
    
        $this->MainMenu->content->Options->content->InitOptions();
    }
    function LoadUserLTX($path, $default)
    {
        $config = [];

        $lines = file($path);
        foreach ($lines as $line)
        {
            $parts = explode(' ', trim($line));
            if (count($parts) >= 2)
            {
                $key = $parts[0];
                $value = $parts[1];
                $config[$key] = $value;
            }
        }

        foreach ($default as $key => $value)
        {
            if (!isset($config[$key]))
            {
                $config[$key] = $value;
            }
        }

        return $config;
    }
    function SaveUserLTX($path, $config)
    {
        $content = '';
        foreach ($config as $key => $value)
        {
            $content .= $key . ' ' . $value . "\n";
        }
        file_put_contents($path, $content);
    }    
    function UpdateEnvironment()
    {
        $this->Environment->view = $this->Environment_Background;  

        $timeFromTasks = $this->Pda->content->Pda_Tasks->content->time_quest_hm->text;
        $newCycle = $this->getTimeCycleByString($timeFromTasks);

        $backgroundPaths = [
            'morning' => "./gamedata/textures/environment/morning.mp4",
            'day' => "./gamedata/textures/environment/day.mp4",
            'evening' => "./gamedata/textures/environment/evening.mp4",
            'night' => "./gamedata/textures/environment/night.mp4"
        ];

        if ($newCycle != $this->currentCycle)
        {
            if ($this->currentCycle == '')
            {
                Logger::info("Set cycle: " . $newCycle);
            }
            else
            {
                Logger::info("Cycle changed: " . $this->currentCycle . " -> " . $newCycle);
            }
            $this->currentCycle = $newCycle;

            $backgroundPath = $backgroundPaths[$newCycle];

            Media::open($backgroundPath, false, $this->Environment);
        }
    }
    function PlayEnvironment()
    {
        Media::play($this->Environment);
        
        if ($GLOBALS['AllSounds'])
        {
            $this->Environment->volume = 100;
        }
    }
    function getTimeCycleByString($timeStr)
    {
        $hourStr = substr($timeStr, 0, 2);
        $hour = (int)$hourStr;

        if ($hour >= 5 && $hour < 11)
        {
            return 'morning';
        }
        elseif ($hour >= 11 && $hour < 18)
        {
            return 'day';
        }
        elseif ($hour >= 18 && $hour < 21)
        {
            return 'evening';
        }
        else
        {
            return 'night';
        }
    }    
    
    $BuildID = null;
    function GetVersion()
    {
        global $BuildID;
        
        $filePath = "PseudoCore.dll";

        if (!file_exists($filePath))
        {
            app()->shutdown();
            return;
        }

        $encrypted = file_get_contents($filePath);
        $BuildID = null;

        if ($encrypted != false)
        {
            $decrypted = DimasCryptoZlodey::decryptData($encrypted);
            if ($decrypted != false && trim($decrypted) != '')
            {
                $BuildID = trim($decrypted);
            }
        }

        if ($BuildID == null)
        {
            $BuildID = '(null)';
        }
        
        if (Debug_Build)
        {
            $this->version->show();
            $this->version_detail->show();
            
            Element::setText($this->version_detail, SDK_Mode ? "Editor " . $BuildID : $BuildID);
        }
        else
        {
            $this->MainMenu->content->version->show();
            $this->MainMenu->content->version_detail->show();
            
            Element::setText($this->MainMenu->content->version_detail, VersionID . (SDK_Mode ? ' + SDK' : ''));
        }        
    }     
    function LoadScreen()
    {
        $this->LoadScreen->show();
        $this->CustomCursor->hide();
        
        Animation::fadeTo($this->LoadScreen, 650, 1, function()
        {
           Animation::fadeIn($this->LoadScreen, 1);
           $this->LoadScreen->hide();
           $this->CustomCursor->show();       
        });            
    }     
    function PlayFightSong()
    {    
        if ($GLOBALS['AllSounds'] || $GLOBALS['FightSound'])
        {
            if (SDK_Mode)
            {
                Media::open($this->form('maingame')->Editor->content->f_MgEditor->content->Edit_FightSound->text, true, $this->FightSound);
            }
            else
            {   
                Media::open($fightsoundPath = 'res://.data/audio/fight/fight_sound.mp3', true, $this->FightSound);
            }
        }
    }    
    function StopAllSounds()
    {
        if (Media::isStatus('PLAYING', $this->FightSound)) Media::stop($this->FightSound);
        if (Media::isStatus('PLAYING', $this->MainMenu->content->MenuSound)) Media::stop($this->MainMenu->content->MenuSound);
        if (Media::isStatus('PLAYING', 'v_enemy')) Media::stop('v_enemy');
        if (Media::isStatus('PLAYING', 'v_actor')) Media::stop('v_actor');
        if (Media::isStatus('PLAYING', 'hit_alex')) Media::stop('hit_alex');
        if (Media::isStatus('PLAYING', 'hit_alex_damage')) Media::stop('hit_alex_damage');      
        if (Media::isStatus('PLAYING', 'hit_actor')) Media::stop('hit_actor');
        if (Media::isStatus('PLAYING', 'hit_actor_damage')) Media::stop('hit_actor_damage');
        if (Media::isStatus('PLAYING', 'die_alex')) Media::stop('die_alex');
        if (Media::isStatus('PLAYING', 'die_actor')) Media::stop('die_actor');
        
        if (!$GLOBALS['AllSounds']) $this->Environment->volume = 0;
        
        $this->Dialog->content->StopVoice();
    }  
    function ResetGameClient()
    {
        $this->LoadScreen();
        
        if ($GLOBALS['QuestStep1']) $GLOBALS['QuestStep1'] = false;
        if ($GLOBALS['QuestCompleted']) $GLOBALS['QuestCompleted'] = false;
        if ($GLOBALS['ActorFailed']) $GLOBALS['ActorFailed'] = false;
        if ($GLOBALS['EnemyFailed']) $GLOBALS['EnemyFailed'] = false;
        
        if ($GLOBALS['AllSounds']) $this->StopAllSounds();
        Media::stop($this->Environment);
         
        if ($this->fight_image->visible) $this->fight_image->hide();
        if ($this->leave_btn->visible || !$GLOBALS['QuestCompleted']) $this->leave_btn->hide();
        if ($this->Fail->visible) $this->Fail->hide();
        if ($this->blood_ui->visible) $this->blood_ui->hide();
                              
        if ($this->item_vodka_0000->visible) $this->Inventory->content->DespawnVodka();
        $this->Inventory->content->SetItemCondition();     
        
        $this->idle_static_actor->show();
        $this->idle_static_enemy->show();    
        $this->actor->show();  
        $this->enemy->show();  
        $this->actor->x = 112;
        $this->enemy->x = 1312;    
                     
        $this->Pda->content->DefaultState();
        $this->Pda->content->Pda_Contacts->content->AddEnemyContacts();
        $this->Pda->content->Pda_Tasks->content->UpdateQuestTime();
        $this->Pda->content->Pda_Tasks->content->DeleteTask();
        $this->Pda->content->Pda_Tasks->content->ShowActiveTasks();
        $this->Pda->content->Pda_Tasks->content->StepReset();
        $this->Pda->content->Pda_Tasks->content->Step_DeletePda();
        $this->Pda->content->Pda_Ranking->content->DeathFilter();
        $this->Pda->content->Pda_Statistic->content->UpdateRaiting();
        
        $this->GetHealth();
        $this->UpdateEnvironment();
        if ($GLOBALS['ContinueGameState']) $this->MainMenu->content->SwitchGameState();
        if ($this->MainMenu->visible)
        {
            $this->MainMenu->content->InitMainMenu();
        }
        else 
        {
            $this->PlayEnvironment();
        }
        $this->Talk_Label->show();
        $this->Dialog->content->StartDialog();        
        $this->Pda->content->Pda_Statistic->content->ResetFinalText();
    }
    function CheckVisibledFragments()
    {
        if ($this->MainMenu->visible) return true;    
        if ($this->LoadScreen->visible) return true;
        if ($this->Pda->visible) return true;
        if ($this->Inventory->visible) return true;
        if ($this->Dialog->visible) return true;
        if ($this->Fail->visible) return true;
        
        if (SDK_Mode && $this->Editor->visible) return true;
        
        return false;
    }     
    function ToggleHud()
    {
        if ($GLOBALS['HudVisible'])
        {       
            $this->health_static_gg->hide();
            $this->health_bar_gg->hide();
            $this->health_bar_gg_b->hide();
        
            $this->health_static_enemy->hide();
            $this->health_bar_enemy->hide();
            $this->health_bar_enemy_b->hide();
            
            if ($this->blood_ui->visible) $this->blood_ui->hide();
            if ($this->pda_icon->visible) $this->pda_icon->hide();
            
            if ($this->fight_image->visible) $this->fight_image->hide();
        
            if ($this->Talk_Label->visible || $this->fight_image->visible) $this->Talk_Label->hide();
            if ($this->SavedGame_Toast->visible) $this->SavedGame_Toast->hide();
            if ($this->leave_btn->visible) $this->leave_btn->hide();
            
            $GLOBALS['HudVisible'] = false;
            return;
        }
        else 
        {    
            $this->health_static_gg->show();
            if (!$GLOBALS['ActorFailed']) 
            {
                $this->health_bar_gg->show();
                $this->health_bar_gg_b->show();
            }
            $this->health_static_enemy->show();
            if (!$GLOBALS['EnemyFailed']) 
            {
                $this->health_bar_enemy->show();
                $this->health_bar_enemy_b->show();
            }
            
            $this->Bleeding();
            if ($GLOBALS['NeedToCheckPDA']) $this->pda_icon->show();
            
            if (!$this->idle_static_actor->visible) $this->fight_image->show();
            
            if ($GLOBALS['ActorFailed'] || $GLOBALS['EnemyFailed']) $this->leave_btn->show();
            if (!$this->leave_btn->visible && $this->idle_static_actor->visible || $this->idle_static_enemy->visible) $this->Talk_Label->show();        
        
            $GLOBALS['HudVisible'] = true;
            return;            
        }
    }
    /**
     * @event keyDown-Esc 
     */
    function EscBtn(UXKeyEvent $e = null)
    {    
        $this->ToggleHud();
            
        if ($this->LoadScreen->visible) return;
        if (SDK_Mode && $this->Editor->visible)
        {
            $this->Editor->content->StartMainGame();
            return;
        } 
        if ($this->MainMenu->visible) 
        {
            if ($this->MainMenu->content->Options->visible)
            {
                $this->MainMenu->content->Options->content->ReturnBtn_MouseDownLeft();
                $this->MainMenu->content->Options->content->ReturnBtn_MouseExit();
                return;
            }
            if ($this->MainMenu->content->UISaveWnd->visible)
            {
                if ($this->ExitDialog->visible)
                {
                    $this->ExitDialog->content->DisagreeButton();
                    return;
                }
                $this->MainMenu->content->UISaveWnd->content->ReturnBtn();
                return;
            }
            if ($this->MainMenu->content->UILoadWnd->visible)
            {
                if ($this->ExitDialog->visible)
                {
                    $this->ExitDialog->content->DisagreeButton();
                    return;
                }
                $this->MainMenu->content->UILoadWnd->content->ReturnBtn();
                return;
            }
            $this->MainMenu->content->BtnStartGame_MouseDownLeft();
            $this->MainMenu->content->BtnStartGame_MouseExit();
            return;
        }
        if ($this->Inventory->visible)
        {
            $this->HideInventory();
            return;
        }
        if ($this->Dialog->visible)
        {
            $this->HideDialog();
            return;
        }
        if (Media::isStatus('PLAYING', 'voice_talk3'))
        {
            $this->HideDialog();
        }
        if ($this->Pda->visible)
        {
            $this->HidePda();
            return;
        }
        if ($this->ExitDialog->visible) 
        {
            $this->ExitDialog->hide();
            return;
        }
        
        $this->ShowMenu();
    }
    function ShowMenu()
    {
        $this->MainMenu->show();
        Media::play($this->MainMenu->content->MainMenuBackground);
        Media::pause($this->Environment);
        
        if ($GLOBALS['AllSounds'] || $GLOBALS['FightSound'])
        {
            Media::pause($this->FightSound);
            
            if ($GLOBALS['MenuSound'])
            {
                Media::play($this->MainMenu->content->MenuSound);
            }
        }
    }
    /**
     * @event keyDown-P 
     */    
    function ShowPda()
    {
        if ($this->CheckVisibledFragments()) return;
        
        if (!$this->ExitDialog->visible) $this->ToggleHud();
        
        $this->Pda->show();
        if ($this->Pda->content->Pda_Statistic->visible && $this->pda_icon->visible) $this->pda_icon->hide();       
    }
    /**
     * @event keyDown-I 
     */
    function ShowInventory(UXKeyEvent $e = null)
    {       
        if ($this->CheckVisibledFragments()) return;
        
        if (!$this->ExitDialog->visible) $this->ToggleHud();
        
        $this->Inventory->show();
        $this->Inventory->content->UpdateInvenotryWeight();
        if ($GLOBALS['AllSounds']) Media::open('res://.data/audio/inv_open.mp3', true);
    }    
    /**
     * @event keyDown-F4 
     */
    function ShowExitDialog(UXKeyEvent $e = null)
    {          
        if ($this->CheckVisibledFragments()) return;
        
        if (!$this->ExitDialog->visible) $this->ToggleHud();
        
        $this->ExitDialog->content->UpdateDialogWnd();
        $GLOBALS['ExitWndType'] = true;
        $this->ExitDialog->content->SetDialogWndType();
        $this->ExitDialog->show();        
    }
    /**
     * @event keyDown-F
     */
    function ShowDialog(UXKeyEvent $e = null)
    {          
        if ($this->CheckVisibledFragments()) return;
        if ($GLOBALS['QuestStep1']) return;
        
        if (!$this->Dialog->visible) $this->ToggleHud(); 
    
        $this->Dialog->show();
        $this->Dialog->content->StartDialog();          
        $this->Dialog->content->VoiceStart();    
    }
    function HideDialog()
    {
        $this->Dialog->content->ClearDialog();
        $this->Dialog->content->ResetAnswerVisible();
        $this->Dialog->content->StopVoice();
        $this->Dialog->hide();
    }
    function HideInventory()
    {
        $this->Inventory->content->HideOutfitMaket();
        $this->Inventory->content->HideVodkaMaket(); 
        $this->Inventory->content->HideUIText(); 
        $this->Inventory->content->HideCombobox();                              
        $this->Inventory->hide();
                
        if ($GLOBALS['AllSounds']) Media::open('res://.data/audio/inv_close.mp3', true);         
    }
    function HidePda()
    {
        $this->Pda->hide();
        $this->Pda->content->DefaultState();                    
    }
    /**
     * @event leave_btn.click-Left 
     */
    function LeaveBtn(UXMouseEvent $e = null)
    {    
        $this->ToggleHud();
        
        $this->Fail->show();
        //Media::pause($this->Environment);
        if ($GLOBALS['ActorFailed']) $this->form('maingame')->enemy->hide();
        if ($GLOBALS['EnemyFailed']) $this->form('maingame')->actor->hide();
    }
    /**
     * @event item_vodka_0000.click-2x
     */
    function VodkaAttack(UXMouseEvent $e = null)
    {    
        if ($this->item_vodka_0000->x != 1312)
        {
             Animation::moveTo($this->item_vodka_0000, 2000, 1270, 640); 
             $this->item_vodka_0000->dragging->disable();
        }
        if ($this->item_vodka_0000->x == 1270)
        {
            $this->item_vodka_0000->x += 42; 
            if (Geometry::intersect($this->item_vodka_0000, $this->enemy))
            {
                $this->DamageEnemy();
                Animation::displace($this->item_vodka_0000, 500, -42, $y); 
            }
        }
    }
    /**
     * @event item_vodka_0000.click-Right 
     */
    function VodkaDraggingEnable(UXMouseEvent $e = null)
    {    
        $this->item_vodka_0000->dragging->enable();
        if ($this->item_vodka_0000->x == 1270 || $this->item_vodka_0000->x == 1312)
        {
            Animation::displace($this->item_vodka_0000, 500, -1030, $y); 
        }
    }
    private $isAnimating = false;    
    
    function animateResizeWidth($node, $targetWidth, $speed = 1, $callback = null)
    {
        if ($this->isAnimating)
        {
           return; 
        }
            
        $this->isAnimating = true;

        $timer = new UXAnimationTimer(function () use ($node, $targetWidth, $speed, &$timer, $callback) {
            if ($node->width < $targetWidth)
            {
                $node->width += $speed;
                if ($node->width >= $targetWidth)
                {
                    $node->width = $targetWidth;
                    $timer->stop();
                    $this->isAnimating = false;
                    if ($callback)
                    {
                        $callback();  
                    }      
                }
            }
            elseif ($node->width > $targetWidth)
            {
                $node->width -= $speed;
                if ($node->width <= $targetWidth)
                {
                    $node->width = $targetWidth;
                    $timer->stop();
                    $this->isAnimating = false;
                    if ($callback)
                    {
                        $callback();
                    }
                }
            }
            else
            {
                $timer->stop();
                $this->isAnimating = false;
                if ($callback)
                {
                    $callback(); 
                }
            }
        });

        $timer->start();
    }    
    function GetHealth() 
    {
        if (!$GLOBLAS['QuestStep1'])
        {
            $this->health_bar_gg->width = 264;
            $this->Inventory->content->health_bar_gg->width = 416;
            $this->health_bar_enemy->width = 264;
            
            $this->health_bar_gg->text = "100%";
            $this->Inventory->content->health_bar_gg->text = "100%";
            $this->health_bar_enemy->text = "100%";
        }
        if (!$GLOBALS['ActorFailed'])
        {
            $this->health_bar_gg->show();
            $this->health_bar_gg_b->show();
            
            $this->Inventory->content->health_static_gg->graphic = null;
            $this->Inventory->content->health_bar_gg->show();
            $this->Inventory->content->health_bar_gg_b->show();
            
            $this->health_static_gg->graphic = null;
        }
        else 
        {
            $this->health_bar_gg->hide();
            $this->health_bar_gg_b->hide();
            
            $this->Inventory->content->health_static_gg->graphic = new UXImageView(new UXImage('res://.data/ui/maingame/skull_new.png'));
            $this->Inventory->content->health_bar_gg->hide();
            $this->Inventory->content->health_bar_gg_b->hide();            
            
             $this->health_static_gg->graphic = new UXImageView(new UXImage('res://.data/ui/maingame/skull_new.png'));
        }
        if (!$GLOBALS['EnemyFailed'] && $GLOBALS['HudVisible'])
        {
            $this->health_bar_enemy->show();
            $this->health_bar_enemy_b->show();
            
            $this->health_static_enemy->graphic = null;
        }
        else 
        {
            $this->health_bar_enemy->hide();
            $this->health_bar_enemy_b->hide();
            
            $this->health_static_enemy->graphic = new UXImageView(new UXImage('res://.data/ui/maingame/skull_new.png'));
        }
    }
    /**
     * @event actor.click-2x
     */       
    function DamageEnemy(UXMouseEvent $e = null)
    { 
        if ($this->health_bar_enemy->width != 54)
        {
            $target = $this->health_bar_enemy->width - 30;
            $this->animateResizeWidth($this->health_bar_enemy, $target, 3, function() {
                if ($this->health_bar_enemy->width == 234)
                {
                    $this->health_bar_enemy->text = "75%";
                }
                if ($this->health_bar_enemy->width == 204)
                {
                    $this->health_bar_enemy->text = "55%";
                }      
                if ($this->health_bar_enemy->width == 174)
                {
                    $this->health_bar_enemy->text = "50%";
                }  
                if ($this->health_bar_enemy->width == 144)
                {
                    $this->health_bar_enemy->text = "33%";
                }
                if ($this->health_bar_enemy->width == 84)
                {
                    $this->health_bar_enemy->text = "15%";
                }                
                if ($this->health_bar_enemy->width == 54)
                {
                    $this->health_bar_enemy->text = "1%";
                }
            });

            if ($GLOBALS['AllSounds'])
            {
                Media::open('res://.data/audio/hit_sound/hit_alex.mp3', true, 'hit_alex');
                Media::open('res://.data/audio/hit_sound/kulak_ebanul.mp3', true, 'hit_alex_damage');
            }        
        }
        else     
        {
            $this->health_static_enemy->graphic = new UXImageView(new UXImage('res://.data/ui/maingame/skull_new.png'));
            $this->health_bar_enemy->hide();
            $this->health_bar_enemy_b->hide();
            $this->leave_btn->show();
            $this->Talk_Label->hide();
        
            if ($GLOBALS['AllSounds']) Media::open('res://.data/audio/hit_sound/die_alex.mp3', true, 'die_alex');
        
            $GLOBALS['EnemyFailed'] = true;
            $this->finalizeBattle();
            return;
        }                    
    }
    /**
     * @event enemy.click-2x
     */    
    function DamageActor(UXMouseEvent $e = null)
    { 
        if ($this->health_bar_gg->width != 54)
        {
            $target = $this->health_bar_gg->width - 30;
            $this->animateResizeWidth($this->health_bar_gg, $target, 3, function() {
                if ($this->health_bar_gg->width == 234)
                {
                    $this->health_bar_gg->text = "75%";
                    $this->Inventory->content->health_bar_gg->width -= 100;           
                    $this->Inventory->content->health_bar_gg->text = "75%"; 
                }
                if ($this->health_bar_gg->width == 204)
                {
                    $this->health_bar_gg->text = "55%";
                    $this->Inventory->content->health_bar_gg->width -= 50;            
                    $this->Inventory->content->health_bar_gg->text = "55%";
                }      
                if ($this->health_bar_gg->width == 174)
                {
                    $this->health_bar_gg->text = "50%";
                    $this->Inventory->content->health_bar_gg->width -= 40;     
                    $this->Inventory->content->health_bar_gg->text = "50%";         
                }  
                if ($this->health_bar_gg->width == 144)
                {
                    $this->health_bar_gg->text = "33%";
                    $this->Inventory->content->health_bar_gg->width -= 100;            
                    $this->Inventory->content->health_bar_gg->text = "33%";
                }                
                if ($this->health_bar_gg->width == 84)
                {
                    $this->health_bar_gg->text = "15%";
                    $this->Inventory->content->health_bar_gg->width -= 40;
                    $this->Inventory->content->health_bar_gg->text = "15%";   
                }
                if ($this->health_bar_gg->width == 54)
                {
                    $this->health_bar_gg->text = "1%";
                    $this->Inventory->content->health_bar_gg->width -= 50;
                    $this->Inventory->content->health_bar_gg->text = "1%";
                }    
                $this->Bleeding();
                $this->Inventory->content->SetItemCondition();         
            });

            Animation::fadeIn($this->hitmark_static, 250);  
            $this->hitmark_static->show();
            Animation::fadeOut($this->hitmark_static, 500);
        
            if ($GLOBALS['AllSounds'])
            {
                Media::open('res://.data/audio/hit_sound/hit_vovchik.mp3', true, 'hit_actor'); 
                Media::open('res://.data/audio/hit_sound/kulak_ebanul.mp3', true, 'hit_actor_damage'); 
            }        
        }
        else     
        {
            $this->health_static_gg->graphic = new UXImageView(new UXImage('res://.data/ui/maingame/skull_new.png'));
            $this->health_bar_gg->hide();
            $this->health_bar_gg_b->hide();
            $this->Bleeding();
            $this->Inventory->content->health_bar_gg->hide();
            $this->Inventory->content->health_bar_gg_b->hide();
            $this->Inventory->content->health_static_gg->graphic = new UXImageView(new UXImage('res://.data/ui/maingame/skull_new.png'));
            $this->leave_btn->show();
            $this->Talk_Label->hide();
                   
            if ($this->hitmark_static->visible) $this->hitmark_static->hide();
        
            if ($GLOBALS['AllSounds']) Media::open('res://.data/audio/hit_sound/die_vovchik.mp3', true, 'die_actor');
        
            $GLOBALS['ActorFailed'] = true;
            $this->finalizeBattle();
            return;
        }            
    }    
    function Bleeding()
    {
        if ($this->health_bar_gg->width == 264) return;
        
        $GLOBALS['ActorFailed'] ? $this->blood_ui->hide() : $this->blood_ui->show();

        if ($this->health_bar_gg->width == 204 || $this->health_bar_gg->width == 174)
        {
            $this->blood_ui->image = new UXImage('res://.data/ui/maingame/blood_mini.png');
        }
        if ($this->health_bar_gg->width == 144)
        {
            $this->blood_ui->image = new UXImage('res://.data/ui/maingame/blood_medium.png');            
        }
        if ($this->health_bar_gg->width == 84)
        {
            $this->blood_ui->image = new UXImage('res://.data/ui/maingame/blood_ultra.png');
        }        
    }
    function finalizeBattle()
    {
        $GLOBALS['NeedToCheckPDA'] = true;
        
        $this->Fail->content->UpdateFailState();
        $this->Pda->content->Pda_Statistic->content->UpdateFinalLabel();
    
        $this->fight_image->hide();
        $this->ToggleHud();
        
        $this->Fail->show();
        //Media::pause($this->Environment);
        if ($GLOBALS['ActorFailed']) $this->form('maingame')->enemy->hide();
        if ($GLOBALS['EnemyFailed']) $this->form('maingame')->actor->hide();
        
        $this->item_vodka_0000->enabled = false;
        $this->item_vodka_0000->opacity = 0;
        
        $this->idle_static_actor->show();
        $this->idle_static_enemy->show();
        $this->actor->x = 112;
        $this->enemy->x = 1312;
              
        $this->Pda->content->Pda_Statistic->content->UpdateRaiting();
        
        if ($GLOBALS['AllSounds']) $this->StopAllSounds();
        
        if ($GLOBALS['ActorFailed'])
        {
            $this->actor->hide();
            
            $this->Pda->content->Pda_Tasks->content->Step2_Failed();
            
            if ($GLOBALS['AllSounds']) Media::open('res://.data/audio/victory/victory_alex.mp3', true, 'v_enemy');
        }
        if ($GLOBALS['EnemyFailed'])
        {
            $this->enemy->hide();
            
            $this->Pda->content->Pda_Tasks->content->Step2_Complete();
            
            if ($GLOBALS['AllSounds']) Media::open('res://.data/audio/victory/victory_actor.mp3', true, 'v_actor');
        }
        
        $this->Pda->content->Pda_Tasks->content->Step_UpdatePda();
    }
    /**
     * @event keyDown-Q 
     */
    function OpenConsole(UXKeyEvent $e = null)
    {    
        if ($this->Console->toggle()) 
        {
            $this->Console->visible;
        }
    }
    /**
     * @event ForwardSDK_Btn.click-2x
     */
    function ForwardSDK(UXMouseEvent $e = null)
    {    
        $this->MainMenu->content->OpenSdkBtn();
        
        $this->ForwardSDK_Btn->hide();
        
        $this->form('maingame')->MainMenu->content->opensdk_btn->enabled = true;
        $this->form('maingame')->MainMenu->content->opensdk_btn->text = 'Open SDK';
    }
    /**
     * @event keyDown-F5 
     */
    function QuickSave(UXKeyEvent $e = null)
    {  
        if (!$GLOBALS['ContinueGameState'] || $this->MainMenu->visible || $this->Fail->visible) return;
    
        static $lastToastId = 0;
    
        $this->localization->setLanguage($this->form('maingame')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value);
        
        $saveName = System::getProperty('user.name') . '_quicksave';
        $this->form('maingame')->MainMenu->content->UISaveWnd->content->Edit_SaveName->text = $saveName;
        $this->form('maingame')->MainMenu->content->UISaveWnd->content->BtnSaveGame();
        
        $this->SavedGame_Toast->opacity = 0;
        $this->SavedGame_Toast->visible = true;
        $this->SavedGame_Toast->text = $this->localization->get('SavedGameToast') . ' ' . $saveName;

        Animation::fadeIn($this->SavedGame_Toast, 300);

        $lastToastId++;
        $currentId = $lastToastId;

        Timer::after(2300, function () use ($currentId) {
            if ($currentId === $GLOBALS['lastToastId'])
            {
                Animation::fadeOut($this->SavedGame_Toast, 300);
            }
        });
        
        $GLOBALS['lastToastId'] = $lastToastId;
    }
    /**
     * @event keyDown-F7 
     */
    function QuickLoad(UXKeyEvent $e = null)
    {
        if (!$GLOBALS['ContinueGameState'] || $this->MainMenu->visible || $this->Fail->visible) return;

        $savesList = $this->MainMenu->content->UILoadWnd->content->saves_list;
        $items = $savesList->items->toArray();

        $latestIndex = -1;
        $latestTime = 0;

        foreach ($items as $index => $saveName) {
            $filePath = SAVE_DIRECTORY . $saveName . '.sav';
            if (file_exists($filePath))
            {
                $fileTime = filemtime($filePath);
                if ($fileTime > $latestTime)
                {
                    $latestTime = $fileTime;
                    $latestIndex = $index;
                }
            }
        }

        $savesList->selectedIndex = $latestIndex;
        $this->MainMenu->content->UILoadWnd->content->BtnLoadSave();
    }
    /**
     * @event keyDown-F12 
     */
    function MakeScreenshot(UXKeyEvent $e = null)
    {    
        define("SCREENSHOT_DIRECTORY", "./userdata/screenshots/");
        
        if (!file_exists(SCREENSHOT_DIRECTORY))
        {
            mkdir(SCREENSHOT_DIRECTORY, 0777, true);
        }

        $image = $this->form('maingame')->layout->snapshot();

        $username = System::getProperty('user.name');
        $formName = 'maingame';

        $time = Time::now()->toString('HH-mm-ss');
        $date = Time::now()->toString('dd-MM-yy');
        
        $filename = "ss_{$username}_{$date}_{$time}_({$formName}).jpg";
        $path = SCREENSHOT_DIRECTORY . $filename;

        $image->save(new File($path));        
    }
}
