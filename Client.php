<?php
namespace app\forms;

use app\forms\classes\DimaAsyncHackEbatNaxyi;
use app\forms\classes\CWindowManager;
use app\forms\classes\UI\UIProgressBarAnimator;
use app\forms\classes\Environment\EnvironmentBase;
use app\forms\classes\Environment\EnvironmentBrightness;
use php\lang\Thread;
use php\gui\animation\UXAnimationTimer;
use action\Animation;
use php\framework\Logger;
use action\Media;
use php\io\File;
use php\lang\System;
use php\gui\event\UXKeyEvent;
use php\gui\UXApplication;
use php\gui\UXImage;
use php\gui\paint\UXColor;
use php\time\Timer;
use action\Element;
use app\forms\classes\DimasCryptoZlodey;
use php\time\Time;
use app\forms\classes\Localization;
use discord\rpc\DiscordRPC;
use php\gui\framework\AbstractForm;
use php\gui\event\UXWindowEvent; 
use php\gui\event\UXMouseEvent; 
use php\gui\event\UXEvent; 
use app\forms\classes\Debug;
use app\forms\classes\Log;
use php\gui\event\UXScrollEvent; 
use app\forms\classes\FileSystem\CSimpleInifile;

class Client extends AbstractForm
{
    private $localization;
    
    public $device;
    
    public $ltx;
    public $ltxInitialized = false;
    
    /**
     * @event show 
     */
    function InitClient(UXWindowEvent $e = null)
    {    
        define('VersionID', 'v1.3 (rc2)');
        define('client_version', '3');
        define('Debug_Build', true);
        define('ResTracker', false);
        
        $GLOBALS['AllSounds']  = true;
        $GLOBALS['MenuSound']  = true;
        $GLOBALS['FightSound'] = true;
        $GLOBALS['AmbientSound'] = true;        
        $GLOBALS['HudVisible'] = true;
        
        Debug::setClient($this);        
        
        $this->GetVersion(); 
        
        $this->localization = new Localization($language);
        
        $this->ltx = new CSimpleInifile('./userdata/user.ltx', [
            'language' => 'rus',
            'r_shadows' => 'on',
            'all_sounds' => 'on',
            'mm_sound' => 'on',
            'fight_sound' => 'on',
            'ambient_sound' => 'on',
            'r_version' => 'on',
            'g_god' => 'off',
            'g_unlimitedammo' => 'off',
            'vid_mode' => '1600x900',
            'vid_fullscreen' => 'off',
            'discord_rpc' => 'on'
        ]);        
        
        $this->device = new CWindowManager($this, $this->ltx);
        Timer::after(300, function() {
            $this->device->applyResolutionFromLTX();
            $this->device->startTracking();
        });
        
        $this->InitUserLTX();
        $this->syncWithSDKLTX();
       
        $this->MainMenu->content->InitMainMenu();       
        $this->MainMenu->content->Options->content->InitOptions();
        
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
    }
    
    /**
     * @event close 
     */
    function DestroyClient(UXWindowEvent $e = null)
    {    
        Log::flush();
        
        $this->ltx->save();
        
        app()->shutdown();
    }    
          
    function GetVersion()
    {
        $filePath = "PseudoCore.dll";
    
        if (!file_exists($filePath))
        {
            Debug::fatal("$filePath not found");
        }
    
        $encrypted = file_get_contents($filePath);
        $this->BuildID = '(null)';
    
        if ($encrypted == false)
        {
            Debug::fatal("Failed to read $filePath");
        }
    
        $decrypted = DimasCryptoZlodey::decryptData($encrypted);
    
        if ($decrypted != false && trim($decrypted) != '')
        {
            $this->BuildID = trim($decrypted);
        }
    
        if (Debug_Build)
        {
            $this->DebugUtilities->content->version->show();
            $this->DebugUtilities->content->version_detail->show();
    
            Element::setText($this->DebugUtilities->content->version_detail, $this->getBuildID());
        }
        else
        {
            $this->MainMenu->content->version->show();
            $this->MainMenu->content->version_detail->show();
    
            Element::setText($this->MainMenu->content->version_detail, $this->getVersionID());
        }
    
        Log::setBuildData($this->getBuildID(), $this->getVersionID());
    }
    
    public function getVersionID(): string
    {
        return VersionID;
    }
    
    public function getBuildID(): string
    {
        return $this->BuildID ?? '(null)';
    }
    
    public function getProductName(): string
    {
        return 'PseudoStalker ' . $this->getVersionID() . ', ' . $this->getBuildID();
    }    
    
    function getCurrentLanguageFromUI()
    {
        return $this->MainMenu->content->Options->content->Language_Switcher_Combobobx->value;
    }
    
    function ShowLoadScreen(callable $task)
    {
        $this->LoadScreen->opacity = 1;
        $this->LoadScreen->show();
        $this->LoadScreen->toFront();
        $this->CustomCursor->hide();

        UXApplication::runLater(function() use ($task) {
            $task();
            Timer::after(500, function() {
                $this->HideLoadScreen();
            });
        });
    }
    function HideLoadScreen()
    {
        $this->LoadScreen->opacity = 0;
        $this->LoadScreen->hide();
        $this->CustomCursor->show();
    }    
    
    function InitUserLTX()
    {
        if ($this->ltx->r_bool('g_god'))
        {
            $this->MainGame->content->setGodMode($this->MainGame->content->GameActor, true);
        }
    
        if ($this->ltx->r_bool('g_unlimitedammo'))
        {
            $GLOBALS['UnlimitedAmmoFlag'] = true;
        }
    
        if ($this->ltx->r_bool('vid_fullscreen'))
        {
            $this->device->setFullscreen(true);
        }
    
        if ($this->ltx->r_bool('discord_rpc'))
        {
            $appId = "1387765734704418846";
            $discord = new DiscordRPC($appId);
    
            $discord->setDetails($this->localization->get('RPC_MainMenu'));
            $discord->setBigImage("icon", $this->BuildID);
            $discord->setStartTimestamp(Time::now()->getTime());
    
            $discord->updateState();
    
            $GLOBALS['discord'] = $discord;
        }
    
        $this->ltxInitialized = true;
    }

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
            
            Log::info("[SDK]: $key = $value");

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
            
                // MgEditor
                case 'mm_background': $this->MainMenu->content->SDK_MMBackground = $value; break;
                case 'health_bar_actor_c': 
                    $this->MainGame->content->health_bar_gg->color = UXColor::of($value);
                    $this->Inventory->content->health_bar_gg->color = UXColor::of($value);
                    break;
                case 'health_bar_enemy_c': $this->MainGame->content->health_bar_enemy->color = UXColor::of($value); break;
                case 'actor_model': 
                    $this->MainGame->content->actor->image = new UXImage($value);
                    $this->Inventory->content->inv_maket_visual->image = new UXImage($value);
                    $this->MainGame->content->SDK_ActorModel = $value;
                    break;
                case 'actor_model_opt_stretch':
                    if ($value == 'on')
                    {
                        $this->MainGame->content->actor->stretch = true;
                    }
                    elseif ($value == 'off')
                    {
                        $this->MainGame->content->actor->stretch = false;
                    }
                    break;
                case 'enemy_model':
                    $this->MainGame->content->enemy->image = new UXImage($value);
                    $this->MainGame->content->SDK_EnemyModel = $value;
                    break;
                case 'enemy_model_opt_stretch':
                    if ($value == 'on')
                    {
                        $this->MainGame->content->enemy->stretch = true;
                    }
                    elseif ($value == 'off')
                    {
                        $this->MainGame->content->enemy->stretch = false;
                    }
                    break;
                case 'fight_sound': $this->MainGame->content->SDK_FightSound = $value; break;

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

        $form = $this->form('Client');
        $console = $form->Console;

        $originalX = $console->x;
        $originalY = $console->y;

        $console->x = max(0, min($console->x, $form->width - $console->width));
        $console->y = max(0, min($console->y, $form->height - $console->height));

        UXApplication::runLater(function () use ($form, $console, $originalX, $originalY)
        {
            $image = $form->layout->snapshot();

            $username = System::getProperty('user.name');
            $time = Time::now()->toString('HH-mm-ss');
            $date = Time::now()->toString('dd-MM-yy');

            $fragments = [
                'LoadScreen'  => $this->LoadScreen,
                'Fail'        => $this->Fail,
                'ExitDialog'  => $this->ExitDialog,
                'Dialog'      => $this->Dialog,
                'Inventory'   => $this->Inventory,
                'Pda'         => $this->Pda,
                'MainMenu'    => $this->MainMenu,
                'MainGame'    => $this->MainGame
            ];

            $formName = 'Client';
            $foundVisible = false;

            foreach ($fragments as $name => $fragment)
            {
                if ($fragment && $fragment->visible)
                {
                    if ($name != 'MainGame')
                    {
                        $formName = $name;
                        $foundVisible = true;
                        break;
                    }
                }
            }

            if (!$foundVisible && $this->MainGame && $this->MainGame->visible)
            {
                $formName = 'MainGame';
            }

            $filename = "ss_{$username}_{$date}_{$time}_({$formName}).jpg";
            $path = SCREENSHOT_DIRECTORY . $filename;

            $image->save(new File($path));

            $console->x = $originalX;
            $console->y = $originalY;
        });
    }    
    /**
     * @event keyDown-F11 
     */
    function FullscreenMode(UXKeyEvent $e = null)
    {    
        $this->device->toggleFullscreen();
    }
    /**
     * @event keyDown-Esc 
     */
    function EscBtn(UXKeyEvent $e = null)
    {    
        $this->MainGame->content->RenderHud(false);
        if ($this->LoadScreen->visible) return;
        if ($this->MainMenu->visible) 
        {
            if ($this->MainMenu->content->Options->visible)
            {
                $this->MainMenu->content->Options->content->ReturnBtn();
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
            $this->MainMenu->content->BtnStartGame();
            return;
        }
        if ($this->Fail->visible)
        {
            return;
        }
        if ($this->Inventory->visible)
        {
            $this->HideInventory();
            $this->MainGame->content->RenderHud(true);
            return;
        }
        if ($this->Dialog->visible)
        {
            $this->HideDialog();
            $this->MainGame->content->RenderHud(true);
            return;
        }
        /*
        if (Media::isStatus('PLAYING', 'voice_talk3'))
        {
            $this->HideDialog();
        }
        */
        if ($this->Pda->visible)
        {
            $this->HidePda();
            $this->MainGame->content->RenderHud(true);
            return;
        }
        if ($this->ExitDialog->visible) 
        {
            $this->ExitDialog->hide();
            $this->MainGame->content->RenderHud(true);
            return;
        }

        $this->ShowMenu();
    }
    function ShowMenu()
    {
        $this->MainMenu->show();
        
        Media::play($this->MainMenu->content->MainMenuBackground);
        
        $this->MainGame->content->Environment->pause();
        
        if ($GLOBALS['AllSounds'])
        {
            DimaAsyncHackEbatNaxyi::pauseAllSfx();
        
            if (!$GLOBALS['QuestCompleted'] && $GLOBALS['QuestStep1'])
            {          
                $this->MainGame->content->fightPlayer->pause();
            }            
            if ($GLOBALS['MenuSound'])
            {
                $this->MainMenu->content->menuPlayer->play();
            }
        }
        
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
        
        if ($this->ltx->r_bool('discord_rpc'))
        {        
            $GLOBALS['discord']->setDetails($this->localization->get('RPC_MainMenu'));
            $GLOBALS['discord']->updateState();   
        }     
    } 
       
    function CheckVisibledFragments()
    {
        if ($this->MainMenu->visible) return true;    
        if ($this->LoadScreen->visible) return true;
        if ($this->Pda->visible) return true;
        if ($this->Inventory->visible) return true;
        if ($this->Dialog->visible) return true;
        if ($this->Fail->visible) return true;
        if ($this->ExitDialog->visible) return true;
                
        return false;
    }    
    /**
     * @event keyDown-Q 
     */
    function OpenConsole(UXKeyEvent $e = null)
    {    
        if ($this->Console->visible) return;
        
        $this->Console->visible = !$this->Console->visible;
        $this->Console->toFront();
    }
    function ShowPda()
    {
        if ($this->CheckVisibledFragments()) return;
        
        $this->MainGame->content->RenderHud(false);
        
        $this->Pda->content->InitPDA();
        $this->Pda->show();
        
        if ($this->Pda->content->Pda_Statistic->visible && $this->pda_icon->visible) $this->pda_icon->hide();          
    }
    /**
     * @event keyDown-P 
     */    
    function ShowPdaTasks()
    {
        $this->ShowPda();
        $this->Pda->content->TasksBtn();
    }
    /**
     * @event keyDown-C 
     */
    function ShowPdaContacts(UXKeyEvent $e = null)
    {    
        $this->ShowPda();
        $this->Pda->content->ContactsBtn();
    }    
    
    /**
     * @event keyDown-I 
     */
    function ShowInventory(UXKeyEvent $e = null)
    {       
        if ($this->CheckVisibledFragments()) return;
        
        if ($this->MainGame->content->GameActor->isDead()) return;
        
        $this->MainGame->content->RenderHud(false);
        
        $this->Inventory->show();
        $this->Inventory->content->UpdateInventoryHealthBar();        
        $this->Inventory->content->UpdateInventoryStatus();
        $this->Inventory->content->repackInventory();
        
        $this->Inventory->content->OpenSound();
    }
     
    /**
     * @event keyDown-F4 
     */
    function ShowExitDialog(UXKeyEvent $e = null)
    {          
        if ($this->CheckVisibledFragments()) return;
        
        $this->MainGame->content->RenderHud(false);
        
        $this->ExitDialog->content->showDialog(exit_dlg::TYPE_EXIT);       
    }
    /**
     * @event keyDown-F
     */
    function ShowDialog(UXKeyEvent $e = null)
    {          
        if ($this->CheckVisibledFragments()) return;
        if ($GLOBALS['QuestStep1']) return;
        
        $this->MainGame->content->RenderHud(false);
    
        $this->Dialog->content->StartDialog();
        //$this->Dialog->content->VoicePlay(0);
        $this->Dialog->show();
    }  
    function HideDialog()
    {
        $this->Dialog->content->StopVoice();
        
        $this->Dialog->content->answerStep = 0;
        
        $this->Dialog->hide();
    }
    function HideInventory()
    {
        $this->Inventory->content->UpdateSelectedItems();
        $this->Inventory->content->SetItemInfo();
        $this->Inventory->content->HideUIText(); 
        $this->Inventory->content->HideCombobox();
        $this->Inventory->content->cancelDrag();
        $this->Inventory->hide();
                      
        $this->Inventory->content->CloseSound();
    }
    function HidePda()
    {
        $this->Pda->hide();
        $this->Pda->content->DefaultState();                    
    }   
    
    /**
     * @event keyDown-F5 
     */
    function QuickSave(UXKeyEvent $e = null)
    {  
        if ($this->CheckVisibledFragments()) return;
    
        $saveName = System::getProperty('user.name') . '_quicksave';
        $this->MainGame->content->performSave($saveName, true);
    }
    /**
     * @event keyDown-F7 
     */
    function QuickLoad(UXKeyEvent $e = null)
    {
        if ($this->CheckVisibledFragments()) return;

        $latest = $this->MainMenu->content->UILoadWnd->content->SaveLoadManager->getLastSaveName();
        if ($latest)
        {
            $this->MainGame->content->performLoad($latest);
        }
    }    
    /**
     * @event keyDown-Tab 
     */
    function CheckTaskStep(UXKeyEvent $e = null)
    {
        $this->MainGame->content->Task_Step_Label->visible = true;
    }
    /**
     * @event keyUp-Tab 
     */
    function HideCheckTaskStep(UXKeyEvent $e = null)
    {
        $this->MainGame->content->Task_Step_Label->visible = false;
    }

    /**
     * @event keyDown-R 
     */
    function ReloadWeaponProxy(UXKeyEvent $e = null)
    {    
        if ($this->CheckVisibledFragments()) return;
            
        $this->MainGame->content->GameActor->ReloadWeapon();
    }

    /**
     * @event keyDown-1 
     */
    function SwitchWeapon1Proxy(UXKeyEvent $e = null)
    {    
        if ($this->CheckVisibledFragments()) return;
        
        $this->MainGame->content->GameActor->setWeaponIndex(0);
    }

    /**
     * @event keyDown-2 
     */
    function SwitchWeapon2Proxy(UXKeyEvent $e = null)
    {    
        if ($this->CheckVisibledFragments()) return;
    
        $this->MainGame->content->GameActor->setWeaponIndex(1);
    }
    
    /**
     * @event scroll-Up 
     */
    function SwitchWeaponOnScroll1Proxy(UXScrollEvent $e = null)
    {    
        if ($this->CheckVisibledFragments() || $this->Console->visible) return;
    
        $this->MainGame->content->GameActor->switchNextWeapon();
    }
    
    /**
     * @event scroll-Down 
     */
    function SwitchWeaponOnScroll2Proxy(UXScrollEvent $e = null)
    {    
        if ($this->CheckVisibledFragments() || $this->Console->visible) return;
    
        $this->MainGame->content->GameActor->switchPrevWeapon();
    }    
    
    /**
     * @event keyDown-Enter 
     */
    function DialogEnterAnswerProxy(UXKeyEvent $e = null)
    {    
        if (!$this->Dialog->visible) return;
        
        $this->Dialog->content->EnterAnswer();
    }

    /**
     * @event keyDown-Space 
     */
    function SpaceProxy(UXKeyEvent $e = null)
    {    
        if ($this->PseudoDebug->visible) $this->PseudoDebug->content->Close();
    }
}
