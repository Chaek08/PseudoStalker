<?php
namespace app\forms;

use php\lang\Thread;
use app\forms\classes\FPSGandon;
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


class Client extends AbstractForm
{
    private $localization;
    
    /**
     * @event show 
     */
    function InitClient(UXWindowEvent $e = null)
    {    
        define('VersionID', 'v1.3 (rc2)');
        define('client_version', '3');
        define('Debug_Build', true);
        define('ResTracker', true);
        define('UseLegacyEnvironment', false);
        
        $appId = "1387765734704418846";
        $discord = new DiscordRPC($appId);
        
        $GLOBALS['discord'] = $discord;

        $GLOBALS['AllSounds']  = true;
        $GLOBALS['MenuSound']  = true;
        $GLOBALS['FightSound'] = true;
        $GLOBALS['HudVisible'] = true;
        
        $this->localization = new Localization($language);     
        
        $this->syncWithSDKLTX();
        $this->InitUserLTX();        

        $this->GetVersion();

        $this->MainMenu->content->InitMainMenu();
        $this->MainMenu->content->Options->content->InitOptions();

        $this->MainGame->content->InitEnvironmentTimer();
        $this->MainGame->content->UpdateEnvironment();
                
        $this->MainGame->content->RenderHud(false);
        
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
        
        $discord->setDetails($this->localization->get('RPC_MainMenu'));

        $discord->setBigImage("icon", $this->BuildID);

        $discord->setStartTimestamp(Time::now()->getTime());
        $discord->updateState();
    }
    function applyResolutionFromLTX()
    {
        $parts = explode('x', $this->ltx['vid_mode']);

        if (count($parts) != 2) return;

        $targetW = (int)$parts[0];
        $targetH = (int)$parts[1];

        $clientW = $this->Client_Proxy->width;
        $clientH = $this->Client_Proxy->height;
        
        $diffW = $this->width - $clientW;
        $diffH = $this->height - $clientH;
            
        $this->width = $targetW + $diffW;
        $this->height = $targetH + $diffH;

        $this->trackResolution();

        Logger::info("window {$this->width}x{$this->height}, client via BG: {$clientW}x{$clientH}");
    }       
    
    private $prevRes = null;
    private $prevClientW = null;
    private $prevClientH = null;
    
    function trackResolution()
    {
        $w = $this->Client_Proxy->width;
        $h = $this->Client_Proxy->height;
    
        if ($this->prevClientW == $w && $this->prevClientH == $h)
        {
            Timer::after(700, [$this, 'trackResolution']);
            return;
        }
    
        $this->prevClientW = $w;
        $this->prevClientH = $h;
        
        $res = "{$w}x{$h}";
        
        $this->ltx['vid_mode'] = $res;
        $this->SaveUserLTX($this->ltx);
    
        UXApplication::runLater(function() use ($w, $h) {
            if (ResTracker)
            {
                static $prevRes = '';
    
                $res = "$w x $h";
                if ($res != $prevRes)
                {
                    $prevRes = $res;
                    $this->DebugUtilities->content->track_res->text = $res;
                }
            }
    
            $sceneW = $this->Client_Proxy->width;
            $sceneH = $this->Client_Proxy->height;
    
            foreach ([
                $this->MainGame,
                $this->MainMenu,
                $this->Pda,
                $this->Dialog,
                $this->ExitDialog,
                $this->Inventory,
                $this->Fail,
                $this->DebugUtilities
            ] as $obj) {
                $scale = min($sceneW / $obj->width, $sceneH / $obj->height);
                $obj->scaleX = $scale;
                $obj->scaleY = $scale;
                $obj->x = ($sceneW - $obj->width) / 2;
                $obj->y = ($sceneH - $obj->height) / 2;
            }
        });
    
        Timer::after(700, [$this, 'trackResolution']);
    }
    
    function playSoundAsync(string $path, bool $loop = true, $channel = null)
    {
        (new Thread(function() use ($path, $loop, $channel)
        {
            if (is_bool($channel))
            {
                $channel = $channel ? 'true' : 'false';
            }
    
            if ($channel != null)
            {
                Media::open($path, $loop, (string)$channel);
            }
            else
            {
                Media::open($path, $loop);
            }
        }))->start();
    }      
    function GetVersion()
    {
        $filePath = "PseudoCore.dll";

        if (!file_exists($filePath))
        {
            app()->shutdown();
            return;
        }

        $encrypted = file_get_contents($filePath);
        $this->BuildID = '(null)';

        if ($encrypted != false)
        {
            $decrypted = DimasCryptoZlodey::decryptData($encrypted);
            if ($decrypted != false && trim($decrypted) != '')
            {
                $this->BuildID = trim($decrypted);
            }
        }

        if (Debug_Build)
        {
            $this->DebugUtilities->content->version->show();
            $this->DebugUtilities->content->version_detail->show();
            Element::setText($this->DebugUtilities->content->version_detail, $this->BuildID);
        }
        else
        {
            $this->MainMenu->content->version->show();
            $this->MainMenu->content->version_detail->show();
            Element::setText($this->MainMenu->content->version_detail, VersionID);
        }
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
    
    public $ltx = [];
    public $ltxInitialized = false;
    private $ltxPath = './userdata/user.ltx';
    
    function InitUserLTX()
    {
        $default = [
            'language' => 'rus',
            'r_shadows' => 'on',
            'all_sounds' => 'on',
            'mm_sound' => 'on',
            'fight_sound' => 'on',
            'r_version' => 'on',
            'g_god' => 'off',
            'vid_mode' => '1600x900',
            'vid_fullscreen' => 'off'
        ];

        if (!file_exists($this->ltxPath))
        {
            $this->SaveUserLTX($default);
            $this->ltx = $default;
        }
        else
        {
            $this->ltx = $this->LoadUserLTX($default);
            $this->SaveUserLTX($this->ltx);
        }
        
        if ($this->ltx['g_god'] == 'on')
        {
            $GLOBALS['GodMode'] = true;
            $this->MainGame->content->GodMode();
        }
        
        Timer::after(100, function() {
            $this->applyResolutionFromLTX();
        });
          
        if ($this->ltx['vid_fullscreen'] == 'on')
        {
            $this->FullscreenMode();
        }

        $this->ltxInitialized = true;
    }
    function LoadUserLTX($default)
    {
        $config = [];

        $lines = file($this->ltxPath);
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
        $dir = dirname($this->ltxPath);
        if (!is_dir($dir))
        {
            mkdir($dir, 0777, true);
        }        
        file_put_contents($this->ltxPath, $content);
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
    function StopAllSounds()
    {
        if (Media::isStatus('PLAYING', $this->MainGame->content->FightSound)) Media::stop($this->MainGame->content->FightSound);
        if (Media::isStatus('PLAYING', $this->MainMenu->content->MenuSound)) Media::stop($this->MainMenu->content->MenuSound);
        //if (Media::isStatus('PLAYING', 'v_enemy')) Media::stop('v_enemy');
        //if (Media::isStatus('PLAYING', 'v_actor')) Media::stop('v_actor');
        //if (Media::isStatus('PLAYING', 'hit_enemy')) Media::stop('hit_enemy');
        //if (Media::isStatus('PLAYING', 'hit_enemy_damage')) Media::stop('hit_enemy_damage');      
        //if (Media::isStatus('PLAYING', 'hit_actor')) Media::stop('hit_actor');
        //if (Media::isStatus('PLAYING', 'hit_actor_damage')) Media::stop('hit_actor_damage');
        //if (Media::isStatus('PLAYING', 'die_enemy')) Media::stop('die_enemy');
        //if (Media::isStatus('PLAYING', 'die_actor')) Media::stop('die_actor');
        if (Media::isStatus('PLAYING', 'AK74_reload')) Media::stop('AK74_reload');
        if (Media::isStatus('PLAYING', 'Pm_reload')) Media::stop('Pm_reload');
        if (Media::isStatus('PLAYING', 'AK74_shot')) Media::stop('AK74_shot');
        if (Media::isStatus('PLAYING', 'Pm_shot')) Media::stop('Pm_shot');        
        if (Media::isStatus('PLAYING', 'pm_draw')) Media::stop('pm_draw');
        if (Media::isStatus('PLAYING', 'ak74_draw')) Media::stop('ak74_draw');
        if (Media::isStatus('PLAYING', 'generic_close')) Media::stop('generic_close');
            
        if (!$GLOBALS['AllSounds']) $this->MainGame->content->Environment->volume = 0;
        
        $this->Dialog->content->StopVoice();        
    }
    function StopAllSoundsAsync()
    {
        (new Thread(function() {
            $channels = [
                $this->MainGame->content->FightSound,
                $this->MainMenu->content->MenuSound,
                //'v_enemy', 'v_actor',
                //'hit_enemy', 'hit_enemy_damage',
                //'hit_actor', 'hit_actor_damage',
                //'die_enemy', 'die_actor',
                'AK74_reload', 'Pm_reload',
                'AK74_shot', 'Pm_shot',
                'pm_draw', 'ak74_draw',
                'generic_close'
            ];
    
            foreach ($channels as $ch)
            {
                if (Media::isStatus('PLAYING', $ch))
                {
                    Media::stop($ch);
                }
            }
    
            UXApplication::runLater(function() {
                if (!$GLOBALS['AllSounds'])
                {
                    $this->MainGame->content->Environment->volume = 0;
                }
    
                if ($this->Dialog && $this->Dialog->content)
                {
                    $this->Dialog->content->StopVoice();
                }
            });
    
        }))->start();
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
        $this->fullScreen = !$this->fullScreen;

        $this->ltx['vid_fullscreen'] = $this->fullScreen ? 'on' : 'off';
        $this->SaveUserLTX($this->ltx);
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
        if (Media::isStatus('PLAYING', 'voice_talk3'))
        {
            $this->HideDialog();
        }
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
        Media::pause($this->MainGame->content->Environment);
        if ($GLOBALS['AllSounds'] || $GLOBALS['FightSound'])
        {
            //$this->StopAllSoundsAsync(); //возможно temp
            
            Media::pause($this->MainGame->content->FightSound);
            
            if ($GLOBALS['MenuSound'])
            {
                Media::play($this->MainMenu->content->MenuSound);
            }
        }
        
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
        $GLOBALS['discord']->setDetails($this->localization->get('RPC_MainMenu'));
        $GLOBALS['discord']->updateState();        
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
        $this->Pda->content->UpdateBtnColor();
        $this->Pda->content->tasks_label->textColor = '#d59b30';
    }
    /**
     * @event keyDown-C 
     */
    function ShowPdaContacts(UXKeyEvent $e = null)
    {    
        $this->ShowPda();
        $this->Pda->content->ContactsBtn();
        $this->Pda->content->UpdateBtnColor();
        $this->Pda->content->contacts_label->textColor = '#d59b30';
    }    
    /**
     * @event keyDown-I 
     */
    function ShowInventory(UXKeyEvent $e = null)
    {       
        if ($this->CheckVisibledFragments()) return;
        
        if ($GLOBALS['ActorFailed']) return;
        
        $this->MainGame->content->RenderHud(false);
        
        $this->Inventory->show();
        $this->Inventory->content->UpdateInventoryStatus();
        $this->Inventory->content->InventoryGrid->content->repackInventory();
        
        if ($GLOBALS['AllSounds']) $this->playSoundAsync('res://.data/audio/inv_open.mp3', true);
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
        $this->Inventory->hide();
                
        if ($GLOBALS['AllSounds']) $this->playSoundAsync('res://.data/audio/inv_close.mp3', true);         
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
        if (!$GLOBALS['ContinueGameState'] || $this->MainMenu->visible || $this->Fail->visible) return;
    
        static $lastToastId = 0;
    
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
        
        $saveName = System::getProperty('user.name') . '_quicksave';
        $this->MainMenu->content->UISaveWnd->content->Edit_SaveName->text = $saveName;
        $this->MainMenu->content->UISaveWnd->content->BtnSaveGame();
        
        $this->MainGame->content->SavedGame_Toast->opacity = 0;
        $this->MainGame->content->SavedGame_Toast->visible = true;
        $this->MainGame->content->SavedGame_Toast->text = $this->localization->get('SavedGameToast') . ' ' . $saveName;

        Animation::fadeIn($this->MainGame->content->SavedGame_Toast, 300);

        $lastToastId++;
        $currentId = $lastToastId;

        Timer::after(2300, function () use ($currentId) {
            if ($currentId == $GLOBALS['lastToastId'])
            {
                Animation::fadeOut($this->MainGame->content->SavedGame_Toast, 300);
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
            $filePath = $this->MainMenu->content->UILoadWnd->content->SaveLoadManager->getSaveDir() . $saveName . '.sav';
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
        $this->MainGame->content->ReloadWeapon();
    }

    /**
     * @event keyDown-1 
     */
    function SwitchWeapon1(UXKeyEvent $e = null)
    {    
        $this->MainGame->content->SwitchWeapon('Pm');
    }

    /**
     * @event keyDown-2 
     */
    function SwitchWeapon2(UXKeyEvent $e = null)
    {    
        $this->MainGame->content->SwitchWeapon('AK74');
    }

    /**
     * @event keyDown-Enter 
     */
    function LeaveBtn(UXKeyEvent $e = null)
    {    
        $this->MainGame->content->LeaveGame();
    }
}
