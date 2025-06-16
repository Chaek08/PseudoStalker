<?php
namespace app\forms;

use php\gui\UXImage;
use php\gui\UXApplication;
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

        $GLOBALS['AllSounds']  = true;
        $GLOBALS['MenuSound']  = true;
        $GLOBALS['FightSound'] = true;
        $GLOBALS['HudVisible'] = true;
        
        $this->localization = new Localization($language);        

        $this->syncWithSDKLTX();

        $this->GetVersion();

        $this->MainMenu->content->InitMainMenu();
        $this->MainMenu->content->Options->content->InitOptions();

        $this->currentCycle = '';
        $this->UpdateEnvironment();
        
        $this->InitUserLTX();
        if ($this->ltx['g_god'] == 'on')
        {
            $GLOBALS['GodMode'] = true;
            $this->GodMode();
        }        
    }
    
    public $ltx = [];
    public $ltxInitialized = false;
    function InitUserLTX()
    {
        define('LTX_DIR', './userdata/user.ltx');

        $default = [
            'language' => 'rus',
            'r_shadows' => 'on',
            'r_version' => 'on',
            'g_god' => 'off'
        ];

        if (!file_exists(LTX_DIR))
        {
            $this->SaveUserLTX($default);
            $this->ltx = $default;
        }
        else
        {
            $config = [];

            $lines = file(LTX_DIR);
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
                $this->SaveUserLTX($config);
            }

            $this->ltx = $config;
        }

        $this->ltxInitialized = true;
    
        $this->MainMenu->content->Options->content->InitOptions();
    }
    function LoadUserLTX($default)
    {
        $config = [];

        $lines = file(LTX_DIR);
        foreach ($lines as $line)
        {
            $parts = explode(' ', trim($line));
            if (count($parts) >= 2)
            {;
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
    function SaveUserLTX($config)
    {
        $content = '';
        foreach ($config as $key => $value)
        {
            $content .= $key . ' ' . $value . "\n";
        }
        $dir = dirname(LTX_DIR);
        if (!is_dir($dir))
        {
            mkdir($dir, 0777, true);
        }        
        file_put_contents(LTX_DIR, $content);
    }    
    public $SDK_FightSound = '';
    public $SDK_ActorModel = '';
    public $SDK_EnemyModel = '';
    function syncWithSDKLTX()
    {
        define('DATA_FILE', 'sdk_data.ltx');
    
        if (!file_exists(DATA_FILE))
        {
            return;
        }

        $lines = explode("\n", file_get_contents(DATA_FILE));

        foreach ($lines as $line)
        {
            $line = trim($line);

            if ($line == '' || strpos($line, '=') == false) continue;

            [$key, $value] = explode('=', $line, 2);

            switch ($key)
            {
                // InvEditor
                case 'outfit_name': $this->Inventory->content->SDK_OutfitName = $value; break;
                case 'outfit_icon': $this->Inventory->content->SDK_OutfitIcon = $value; break;
                case 'outfit_price': $this->Inventory->content->SDK_OutfitPrice = $value; break;
                case 'outfit_weight': $this->Inventory->content->SDK_OutfitWeight = $value; break;
                case 'outfit_desc': $this->Inventory->content->SDK_OutfitDesc = $value; break;
                case 'vodka_name': $this->Inventory->content->SDK_VodkaName = $value; break;
                case 'vodka_icon': $this->Inventory->content->SDK_VodkaIcon = $value; break;
                case 'vodka_price': $this->Inventory->content->SDK_VodkaPrice = $value; break;
                case 'vodka_weight': $this->Inventory->content->SDK_VodkaWeight = $value; break;
                case 'vodka_desc': $this->Inventory->content->SDK_VodkaDesc = $value; break;
                            
                // FailEditor
                case 'win_fail_text_actor': $this->Fail->content->SDK_FailTextActor = $value; break;
                case 'win_fail_text_icon_actor': $this->Fail->content->SDK_FailTextIconActor = $value; break;
                case 'win_fail_desc_actor': $this->Fail->content->SDK_FailDescActor = $value; break;
                case 'win_fail_text_enemy': $this->Fail->content->SDK_FailTextEnemy = $value; break;
                case 'win_fail_text_icon_enemy': $this->Fail->content->SDK_FailTextIconEnemy = $value; break;
                case 'win_fail_desc_enemy': $this->Fail->content->SDK_FailDescEnemy = $value; break;
            
                // RoleEditor
                case 'role_color_de': $this->Pda->content->SDK_DeRoleColor = $value; break;
                case 'role_color_pido': $this->Pda->content->SDK_PidoRoleColor = $value; break;
                case 'role_color_la': $this->Pda->content->SDK_LaRoleColor = $value; break;
                case 'role_name_de': $this->Pda->content->SDK_DeRoleName = $value; break;
                case 'role_name_pido': $this->Pda->content->SDK_PidoRoleName = $value; break;
                case 'role_name_la': $this->Pda->content->SDK_LaRoleName = $value; break;
                case 'role_icon_de': $this->Pda->content->SDK_DeRoleIcon = $value; break;
                case 'role_icon_pido': $this->Pda->content->SDK_PidoRoleIcon = $value; break;
                case 'role_icon_la': $this->Pda->content->SDK_LaRoleIcon = $value; break;
            
                // UserDataEditor
                case 'actor_name': $this->Pda->content->SDK_ActorName = $value; break;
                case 'actor_bio': $this->Pda->content->SDK_ActorBio = $value; break;
                case 'actor_icon': $this->Pda->content->SDK_ActorIcon = $value; break;
                case 'enemy_name': $this->Pda->content->SDK_EnemyName = $value; break;
                case 'enemy_bio': $this->Pda->content->SDK_EnemyBio = $value; break;
                case 'enemy_icon': $this->Pda->content->SDK_EnemyIcon = $value; break;
                case 'valerok_name': $this->Pda->content->SDK_ValerokName = $value; break;
                case 'valerok_bio':  $this->Pda->content->SDK_ValerokBio = $value; break;
                case 'valerok_icon': $this->Pda->content->SDK_ValerokIcon = $value; break;           
            
                // DialogEditor
                case 'alex_desc_1': $this->Dialog->content->SDK_AlexDesc1 = $value; break;
                case 'actor_desc_1': $this->Dialog->content->SDK_ActorDesc1 = $value; break;
                case 'alex_desc_2': $this->Dialog->content->SDK_AlexDesc2 = $value; break;
                case 'alex_desc_3': $this->Dialog->content->SDK_AlexDesc3 = $value; break;
                case 'actor_desc_3': $this->Dialog->content->SDK_ActorDesc3 = $value; break;
                case 'final_phase': $this->Dialog->content->SDK_FinalPhase = $value; break;                 
                case 'voice_start': $this->Dialog->content->SDK_VoiceStart = $value; break;
                case 'voice_talk1': $this->Dialog->content->SDK_VoiceTalk1 = $value; break;
                case 'voice_talk2': $this->Dialog->content->SDK_VoiceTalk2 = $value; break;
                case 'voice_talk3': $this->Dialog->content->SDK_VoiceTalk3 = $value; break;            
            
                // MgEditor
                case 'mm_background': $this->MainMenu->content->SDK_MMBackground = $value; break;
                case 'health_bar_actor_c': 
                    $this->health_bar_gg->color = UXColor::of($value);
                    $this->Inventory->content->health_bar_gg->color = UXColor::of($value);
                    break;
                case 'health_bar_enemy_c': $this->health_bar_enemy->color = UXColor::of($value); break;
                case 'actor_model': 
                    $this->actor->image = new UXImage($value);
                    $this->Inventory->content->inv_maket_visual->image = new UXImage($value);
                    $this->SDK_ActorModel = $value;
                    break;
                case 'actor_model_opt_stretch':
                    if ($value == 'on')
                    {
                        $this->actor->stretch = true;
                    }
                    elseif ($value == 'off')
                    {
                        $this->actor->stretch = false;
                    }
                    break;
                case 'enemy_model':
                    $this->enemy->image = new UXImage($value);
                    $this->SDK_EnemyModel = $value;
                    break;
                case 'enemy_model_opt_stretch':
                    if ($value == 'on')
                    {
                        $this->enemy->stretch = true;
                    }
                    elseif ($value == 'off')
                    {
                        $this->enemy->stretch = false;
                    }
                    break;
                case 'fight_sound': $this->SDK_FightSound = $value; break;

                // QuestEditor
                case 'quest_name': $this->Pda->content->Pda_Tasks->content->SDK_QuestName = $value; break;
                case 'quest_icon': $this->Pda->content->Pda_Tasks->content->SDK_QuestIcon = $value; break;
                case 'quest_desc': $this->Pda->content->Pda_Tasks->content->SDK_QuestDesc = $value; break;
                case 'quest_step1': $this->Pda->content->Pda_Tasks->content->SDK_QuestStep1 = $value; break;
                case 'quest_step2': $this->Pda->content->Pda_Tasks->content->SDK_QuestStep2 = $value; break;
                case 'quest_target': $this->Pda->content->Pda_Tasks->content->SDK_QuestTarget = $value; break;
            }
        }     
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
        
        $brightnessByCycle = [
            'morning' => -0.1,
            'day' => 0.0,
            'evening' => -0.2,
            'night' => -0.4
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
            
            $brightness = $brightnessByCycle[$newCycle];
            $this->actor->colorAdjustEffect->brightness = $brightness;
            $this->enemy->colorAdjustEffect->brightness = $brightness;
            $this->item_vodka_0000->colorAdjustEffect->brightness = $brightness;
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

            Element::setText($this->version_detail, $BuildID);
        }
        else
        {
            $this->MainMenu->content->version->show();
            $this->MainMenu->content->version_detail->show();

            Element::setText($this->MainMenu->content->version_detail, VersionID);
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
            $path = trim($this->SDK_FightSound);

            if ($path != '')
            {
                Media::open($path, true, $this->FightSound);
            } 
            else
            {
                Media::open('res://.data/audio/fight/fight_sound_20_05_2025.mp3', true, $this->FightSound);
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
                              
        $this->Inventory->content->DespawnItems();
        $this->Inventory->content->SetItemCondition();     
        
        $this->idle_static_actor->show();
        $this->idle_static_enemy->show();    
        $this->actor->show();  
        $this->enemy->show();  
        $this->actor->x = 112;
        $this->enemy->x = 1312;    
                     
        $this->Pda->content->DefaultState();
        $this->Pda->content->Pda_Contacts->content->UpdateContacts();
        $this->Pda->content->Pda_Tasks->content->UpdateQuestTime();
        $this->Pda->content->Pda_Tasks->content->DeleteTask();
        $this->Pda->content->Pda_Tasks->content->ShowActiveTasks();
        $this->Pda->content->Pda_Tasks->content->StepReset();
        $this->Pda->content->Pda_Tasks->content->Step_DeletePda();
        $this->Pda->content->Pda_Ranking->content->DeathFilter();
        $this->Pda->content->Pda_Statistic->content->UpdateRaiting();
        $this->Pda->content->Pda_Statistic->content->UpdateFinalLabel();
        
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
        $this->Dialog->content->StartDialog();
    }
    function CheckVisibledFragments()
    {
        if ($this->MainMenu->visible) return true;    
        if ($this->LoadScreen->visible) return true;
        if ($this->Pda->visible) return true;
        if ($this->Inventory->visible) return true;
        if ($this->Dialog->visible) return true;
        if ($this->Fail->visible) return true;
        
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
            if ($this->GodMode_Icon->visible) $this->GodMode_Icon->hide();
            if ($this->pda_icon->visible) $this->pda_icon->hide();
            
            if ($this->fight_image->visible) $this->fight_image->hide();
        
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
            
            if ($GLOBALS['GodMode']) $this->GodMode_Icon->show();
            
            if (!$this->idle_static_actor->visible) $this->fight_image->show();
            
            if ($GLOBALS['ActorFailed'] || $GLOBALS['EnemyFailed']) $this->leave_btn->show();
        
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
            if ($this->ExitDialog->visible) 
            {
                $this->ExitDialog->hide();
                return;
            }            
            $this->MainMenu->content->BtnStartGame_MouseDownLeft();
            $this->MainMenu->content->BtnStartGame_MouseExit();
            return;
        }
        if ($this->Fail->visible)
        {
            $this->ToggleHud();
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
        
        if (!$this->Pda->visible) $this->ToggleHud();
        
        $this->Pda->show();
        if ($this->Pda->content->Pda_Statistic->visible && $this->pda_icon->visible) $this->pda_icon->hide();       
    }
    /**
     * @event keyDown-I 
     */
    function ShowInventory(UXKeyEvent $e = null)
    {       
        if ($this->CheckVisibledFragments()) return;
        
        if (!$this->Inventory->visible) $this->ToggleHud();
        
        $this->Inventory->show();
        $this->Inventory->content->UpdateInventoryStatus();
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
    
        $this->Dialog->content->StartDialog();
        $this->Dialog->content->VoiceStart();
        $this->Dialog->show();
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
        $this->Inventory->content->UpdateSelectedItems();
        $this->Inventory->content->SetItemInfo();
        $this->Inventory->content->HideUIText(); 
        $this->Inventory->content->InventoryGrid->content->HideCombobox();                              
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
    function SpawnItem()
    {
        $actor = $this->actor;
        $vodka = $this->form('maingame')->item_vodka_0000;

        $floorY = 696;

        $spawnX = $actor->x + ($actor->width * 1.2);
        $spawnY = $floorY;

        $vodka->x = $spawnX;
        $vodka->y = $spawnY;
        $vodka->opacity = 100;
        $vodka->show();
    }  
    /**
     * @event item_vodka_0000.click-2x
     */
    function VodkaAttack(UXMouseEvent $e = null)
    {
        $vodka = $this->item_vodka_0000;
        $enemy = $this->enemy;

        $targetX = $enemy->x + ($enemy->width / 2) - ($vodka->width / 2);
        $targetY_Head = $enemy->y;

        $floorY = 696;

        $startX = $vodka->x;
        $startY = $vodka->y;

        $dx = $targetX - $startX;
        $dy = $targetY_Head - $startY;

        $distance = sqrt($dx * $dx + $dy * $dy);

        $speed = 0.9;
        $duration = (int)($distance / $speed);

        $initialEnemyX = $enemy->x;
        $initialEnemyY = $enemy->y;

        Animation::moveTo($vodka, $duration, $targetX, $targetY_Head, function () use ($vodka, $enemy, $floorY, $initialEnemyX, $initialEnemyY) {
            $enemyStillHere = $enemy->x === $initialEnemyX && $enemy->y === $initialEnemyY;

            if ($enemyStillHere && Geometry::intersect($vodka, $enemy))
            {
                for ($i = 0; $i < 3; $i++) 
                {
                    $scatterX = rand(-25, 25);
                    $scatterY = rand(-25, 25);

                    $particle = new UXImageView();
                    $particle->image = new UXImage("res://.data/ui/particles/blood.png");
                    $particle->width = 86;
                    $particle->height = 86;

                    $hitX = $enemy->x + ($enemy->width / 2) - ($particle->width / 2);
                    $hitY = $enemy->y - 10;

                    $particle->x = $hitX + $scatterX;
                    $particle->y = $hitY + $scatterY;
                    $particle->opacity = 1.0;

                    $this->add($particle);

                    Animation::fadeOut($particle, 300, function () use ($particle) {
                        $particle->free();
                    });
                } 
            
                $this->DamageEnemy();

                Animation::displace($vodka, 300, -150, -10, function () use ($vodka, $floorY) {
                    Animation::moveTo($vodka, 300, $vodka->x, $floorY);
                });
            }
            else
            {
                Animation::moveTo($vodka, 400, $vodka->x, $floorY);
            }
        });
    }
    /**
     * @event item_vodka_0000.click-Right 
     */
    function VodkaDraggingEnable(UXMouseEvent $e = null)
    {    
        $vodka = $this->item_vodka_0000;
        $actor = $this->actor;

        $targetX = $actor->x + ($actor->width * 1.2) - ($vodka->width / 2);
        $targetY = $vodka->y;

        $dx = $targetX - $vodka->x;
        $dy = 0;
        $distance = sqrt($dx * $dx + $dy * $dy);

        $speed = 1.3;
        $duration = (int)($distance / $speed);

        Animation::moveTo($vodka, $duration, $targetX, $targetY);
    }
    
    public $isAnimating = false;
    private $isAnimatingBars = [];
    function animateResizeWidth($node, $targetWidth, $speed = 1, $callback = null)
    {
        $id = spl_object_hash($node);

        if (isset($this->isAnimatingBars[$id]) && $this->isAnimatingBars[$id])
        {
            return;
        }

        $this->isAnimatingBars[$id] = true;

        $timer = new UXAnimationTimer(function () use ($node, $targetWidth, $speed, &$timer, $callback, $id) {
            if ($node->width < $targetWidth)
            {
                $node->width += $speed;
                if ($node->width >= $targetWidth)
                {
                    $node->width = $targetWidth;
                    $timer->stop();
                    $this->isAnimatingBars[$id] = false;
                    if ($callback) $callback();
                }
            }
            elseif ($node->width > $targetWidth)
            {
                $node->width -= $speed;
                if ($node->width <= $targetWidth)
                {
                    $node->width = $targetWidth;
                    $timer->stop();
                    $this->isAnimatingBars[$id] = false;
                    if ($callback) $callback();
                }
            }
            else
            {
                $timer->stop();
                $this->isAnimatingBars[$id] = false;
                if ($callback) $callback();
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
    function GodMode()
    {
        if ($GLOBALS['GodMode'])
        {
            $this->GodMode_Icon->show();
            if ($this->blood_ui->visible)
            {
                $this->blood_ui->y += 60;
            }
        }
        else
        {
            $this->GodMode_Icon->hide();
            if ($this->blood_ui->visible && $this->blood_ui->y != 96)
            {
                $this->blood_ui->y -= 60;
            }            
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
    public $lastHitTime = 0;
    public $hitmarkLevel = 1;
    public $hitmarkVisibleUntil = 0;
    /**
     * @event enemy.click-2x
     */    
    function DamageActor(UXMouseEvent $e = null)
    { 
        if ($this->health_bar_gg->width != 54)
        {
            if (!$GLOBALS['GodMode'])
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
                });                
            }

            $now = Time::millis();
            $timeDiff = $now - $this->lastHitTime;
            $this->lastHitTime = $now;

            if ($timeDiff < 500)
            {
                if ($this->hitmarkLevel < 6)
                {
                    $this->hitmarkLevel++;
                }
            }

            Timer::after(1500, function () {
                $sinceLastHit = Time::millis() - $this->lastHitTime;
                    if ($sinceLastHit >= 1500 && $this->hitmarkLevel > 1)
                    {
                        $this->hitmarkLevel--;
                    }
            });

            switch ($this->hitmarkLevel)
            {
                case 1:
                    $this->HitMark->image = new UXImage("res://.data/ui/maingame/hitmark/hitmark_1.png");
                    break;
                case 2:
                    $this->HitMark->image = new UXImage("res://.data/ui/maingame/hitmark/hitmark_2.png");
                    break;
                case 3:
                    $this->HitMark->image = new UXImage("res://.data/ui/maingame/hitmark/hitmark_3.png");
                    break;
                case 4:
                    $this->HitMark->image = new UXImage("res://.data/ui/maingame/hitmark/hitmark_4.png");
                    break;
                case 5:
                    $this->HitMark->image = new UXImage("res://.data/ui/maingame/hitmark/hitmark_5.png");
                    break;      
                case 6:
                    $this->HitMark->image = new UXImage("res://.data/ui/maingame/hitmark/hitmark_6.png");
                    break;                                                       
            }

            $this->HitMark->opacity = 0;
            $this->HitMark->visible = true;

            Animation::fadeIn($this->HitMark, 100);

            $this->hitmarkVisibleUntil = Time::millis() + 500;

            Timer::after(500, function () {
                if (Time::millis() >= $this->hitmarkVisibleUntil)
                {
                    Animation::fadeOut($this->HitMark, 300);
        
                    Timer::after(300, function () {
                        $this->hitmarkLevel = 1;
                    });
                }
            });
        
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
                   
            if ($this->HitMark->visible) $this->HitMark->hide();
        
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
        
        $this->Pda->content->Pda_Statistic->content->UpdateRaiting();
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
            if ($currentId == $GLOBALS['lastToastId'])
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

        $console = $this->form('maingame')->Console;

        $formWidth = 1600;
        $formHeight = 900;

        $originalX = $console->x;
        $originalY = $console->y;

        if ($console->x < 0)
        {
            $console->x = 0;
        }
        elseif ($console->x + $console->width > $formWidth)
        {
            $console->x = $formWidth - $console->width;
        }
        if ($console->y < 0)
        {
            $console->y = 0;
        }
        elseif ($console->y + $console->height > $formHeight)
        {
            $console->y = $formHeight - $console->height;
        }

        UXApplication::runLater(function () use ($console, $originalX, $originalY) {

            $image = $this->form('maingame')->layout->snapshot();

            $username = System::getProperty('user.name');
            $formName = 'maingame';
            $time = Time::now()->toString('HH-mm-ss');
            $date = Time::now()->toString('dd-MM-yy');
            $filename = "ss_{$username}_{$date}_{$time}_({$formName}).jpg";
            $path = SCREENSHOT_DIRECTORY . $filename;

            $image->save(new File($path));

            $console->x = $originalX;
            $console->y = $originalY;
        });
    }
    
    protected $isHovered = false;
    protected $isLabelVisible = false;
    /**
     * @event idle_static_enemy.mouseEnter
     */
    function EnemyHoverEnter(UXMouseEvent $e = null)
    {
        if ($GLOBALS['QuestStep1']) return;
        
        $this->isHovered = true;

        Timer::after(300, function () {
            if ($this->isHovered && !$this->isLabelVisible)
            {
                $this->Talk_Label->opacity = 0;
                $this->Talk_Label->visible = true;
                Animation::fadeIn($this->Talk_Label, 300);
                $this->isLabelVisible = true;
            }
        });
    }
    /**
     * @event idle_static_enemy.mouseExit
     */
    function EnemyHoverExit(UXMouseEvent $e = null)
    {
        $this->isHovered = false;

        if ($this->isLabelVisible)
        {
            Animation::fadeOut($this->Talk_Label, 300, function () {
                $this->Talk_Label->visible = false;
                $this->isLabelVisible = false;
            });
        }
    }
}
