<?php
namespace app\forms;

use app\forms\classes\ConsoleBuffer;
use php\lang\Thread;
use php\lang\ThreadGroup;
use php\lang\ThreadPool;
use app\forms\exit_dlg;
use php\framework\Logger;
use php\time\Timer;
use action\Animation;
use php\lang\System;
use Exception;
use action\Element;
use php\gui\framework\AbstractForm;
use php\gui\event\UXWindowEvent; 
use php\gui\event\UXKeyEvent; 
use php\gui\event\UXMouseEvent; 
use app\forms\classes\Localization;
use app\forms\classes\Debug;
use app\forms\classes\Log;

class console extends AbstractForm
{
    private $consoleBuffer;

    public function __construct()
    {
        parent::__construct();
    
        $this->consoleBuffer = new ConsoleBuffer();
    
        Log::onWrite(function (string $text)
        {
            $this->consoleBuffer->add("> $text\n");
        });
    
        Timer::every(50, function ()
        {
            $this->flushConsoleBuffer();
        });
    }
    
    private function flushConsoleBuffer()
    {
        if ($this->consoleBuffer->isEmpty())
        {
            return;
        }
    
        $text = $this->consoleBuffer->getChunk(25);
    
        if ($text === '')
        {
            return;
        }
    
        Element::appendText($this->Console_Log, $text);
    
        $this->Console_Log->positionCaret(strlen($this->Console_Log->text));
    }    
    
    private $availableCommands = [
        'exit'                => '',
        'clear'               => '',
        'help'                => '',
        'version'             => '',
        'sync_sdk_ltx'        => '',
        'save'                => ' [name]',
        'load'                => ' [name]',
        'g_god'               => ' [off/on/1/0]',
        'g_unlimitedammo'     => ' [off/on/1/0]',        
        'vid_mode'            => ' [1600x900]',
        'r_version'           => ' [off/on/1/0]',
        'r_shadows'           => ' [off/on/1/0]',
        'snd_all'             => ' [off/on/1/0]',
        'snd_ambient'         => ' [off/on/1/0]',
        'openform'            => ' [form_name]',
        'call'                => ' [function_name]',
        'language'            => ' [gamedata/config/locales/]',
        'set_level'           => ' [0-4]',
        'set_cycle'           => ' [night, morning, day, evening, underground]',
        'set_ambient'         => ' [1-6]',
        'env_reset'           => '',
        'fatal'               => ' [message]',  
        'flush'               => '',
        'dbg_restracker'      => ' [off/on/1/0]',
          
        'threadpool_stresstest' => ''
    ];

    private $tabMatches = [];
    private $commandHistory = []; 
    
    private $historyIndex = -1;
    private $tabIndex = 0;
    
    private function parseBoolArg($arg)
    {
        $arg = strtolower(trim($arg));
    
        if (in_array($arg, ['on', '1', 'true', 'yes'], true))
        {
            return true;
        }
    
        if (in_array($arg, ['off', '0', 'false', 'no'], true))
        {
            return false;
        }
    
        return null;
    }    
    
    /**
     * @event edit.keyDown-Enter 
     */
    function EnterCommands(UXKeyEvent $e = null)
    {    
        $this->requestFocus();
    
        $this->tabMatches = [];
        
        $command = trim($this->edit->text);
        if ($command !== "")
        {
            $this->commandHistory[] = $command; // сохраняем в историю
            $this->historyIndex = count($this->commandHistory); // сбрасываем индекс
            
            Log::info($command);
        }
        $args = explode(" ", $command);
        $command = strtolower($args[0]);
        
        switch ($command) 
        {
                case "help":
                    $lines = [];
                
                    foreach ($this->availableCommands as $name => $hint)
                    {
                        $lines[] = $name . $hint;
                    }
                
                    $commandsList = implode("\n> ", $lines);
                
                    Log::result("Available commands:");
                    foreach ($this->availableCommands as $name => $hint)
                    {
                        Log::result($name . $hint);
                    }

                    $this->edit->text = "";
                    break;

                case "exit":
                        $this->edit->text = "";
                        $this->form('Client')->OpenConsole();
                        $this->form('exit_dlg')->showDialog(exit_dlg::TYPE_EXIT);
                        $this->form('exit_dlg')->AcceptButton();
                        break;                        
                        
                case "clear":
                        $this->Console_Log->text = "";
                        $this->edit->text = "";
                        break;                
                        
                case "openform":
                        if (isset($args[1]))
                        {
                            $formName = $args[1];
                            if (app()->form($formName))
                            {
                                app()->showForm($formName);
                                Log::result("Form '$formName' opened successfully.");
                            }
                            else
                            {
                                Log::result("Form '$formName' not found.");
                            }
                        }
                        else
                        {
                            Log::result("Specify the form name: openform form_name");
                        }
                        $this->edit->text = "";
                        break;

                case "r_version":
                    $options = $this->form('Client')->MainMenu->content->Options->content;
                    $btn = $options->Version_Switcher_Btn;
                    $isEnabled = ($btn->text == Localization::get('TurnOn_Label')); //ебаный даун
                
                    if (!isset($args[1]))
                    {
                        Log::result("r_version " . ($isEnabled ? "on" : "off"));
                        $this->edit->text = "";
                        break;
                    }
                
                    $state = $this->parseBoolArg($args[1]);
                
                    if ($state === null)
                    {
                        Log::result("Usage: r_version [on/off/1/0]");
                        $this->edit->text = "";
                        break;
                    }
                
                    if ($state !== $isEnabled)
                    {
                        $options->VersionSwitcher();
                    }
                
                    Log::result("r_version " . ($state ? "on" : "off"));
                    $this->edit->text = "";
                    break;
                        
                case "g_god":
                    $client = $this->form('Client');
                
                    if (!isset($args[1]))
                    {
                        $state = $client->ltx->r_bool('g_god');
                        Log::result("g_god " . ($state ? "on" : "off"));
                        $this->edit->text = "";
                        break;
                    }
                
                    $state = $this->parseBoolArg($args[1]);
                
                    if ($state === null)
                    {
                        Log::result("Usage: g_god [on/off/1/0]");
                        $this->edit->text = "";
                        break;
                    }
                
                    $client->MainGame->content->setGodMode($client->MainGame->content->GameActor,$state);
                
                    if ($client->ltxInitialized)
                    {
                        $client->ltx->w_bool('g_god', $state);
                    }
                
                    Log::result("g_god " . ($state ? "on" : "off"));
                    $this->edit->text = "";
                    break;
                            
                case "g_unlimitedammo":
                    $client = $this->form('Client');
                
                    if (!isset($args[1]))
                    {
                        $state = !empty($GLOBALS['UnlimitedAmmoFlag']);
                        Log::result("g_unlimitedammo " . ($state ? "on" : "off"));
                        $this->edit->text = "";
                        break;
                    }
                
                    $state = $this->parseBoolArg($args[1]);
                
                    if ($state === null)
                    {
                        Log::result("Usage: g_unlimitedammo [on/off/1/0]");
                        $this->edit->text = "";
                        break;
                    }
                
                    $GLOBALS['UnlimitedAmmoFlag'] = $state;
                
                    $weapon = $client->MainGame->content->GameActor->getWeapon();
                
                    if ($weapon)
                    {
                        $weapon->setUnlimitedAmmo($state);
                    }
                
                    $client->MainGame->content->UpdateMagazine();
                
                    if ($client->ltxInitialized)
                    {
                        $client->ltx->w_bool('g_unlimitedammo', $state);
                    }
                
                    Log::result("g_unlimitedammo " . ($state ? "on" : "off"));
                    $this->edit->text = "";
                    break;                        
                
                case "vid_mode":
                    if (isset($args[1]))
                    {
                        $resolution = $args[1];
                
                        if (preg_match('/^[1-9]\d*x[1-9]\d*$/', $resolution))
                        {
                            if ($this->form('Client')->ltxInitialized)
                            {
                                $this->form('Client')->ltx->w_string('vid_mode', $resolution);
                            }
                
                            $this->form('Client')->device->applyResolutionFromLTX();
                        }
                    }
                    else
                    {
                        $w = $this->form('Client')->Client_Proxy->width;
                        $h = $this->form('Client')->Client_Proxy->height;
                
                        Log::result("Current resolution: {$w}x{$h}");
                    }
                
                    $this->edit->text = "";
                    break;

                case "r_shadows":
                    $options = $this->form('Client')->MainMenu->content->Options->content;
                    $btn = $options->Shadows_Switcher_Btn;
                    $isEnabled = ($btn->text == Localization::get('TurnOn_Label')); //ебаный даун
                
                    if (!isset($args[1]))
                    {
                        Log::result("r_shadows " . ($isEnabled ? "on" : "off"));
                        $this->edit->text = "";
                        break;
                    }
                
                    $state = $this->parseBoolArg($args[1]);
                
                    if ($state === null)
                    {
                        Log::result("Usage: r_shadows [on/off/1/0]");
                        $this->edit->text = "";
                        break;
                    }
                
                    if ($state !== $isEnabled)
                    {
                        $options->ShadowsSwitcher();
                    }
                
                    Log::result("r_shadows " . ($state ? "on" : "off"));
                    $this->edit->text = "";
                    break;

                case "snd_all":
                    $client = $this->form('Client');
                    $options = $client->MainMenu->content->Options->content;
                    $btn = $options->AllSound_Switcher_Btn;
                    $isEnabled = ($btn->text == Localization::get('TurnOn_Label')); //ебаный даун
                
                    if (!isset($args[1]))
                    {
                        Log::result("snd_all " . ($isEnabled ? "on" : "off"));
                        $this->edit->text = "";
                        break;
                    }
                
                    $state = $this->parseBoolArg($args[1]);
                
                    if ($state === null)
                    {
                        Log::result("Usage: snd_all [on/off/1/0]");
                        $this->edit->text = "";
                        break;
                    }
                
                    if ($state !== $isEnabled)
                    {
                        $client->MainGame->content->Environment->pause();
                        $options->AllSoundSwitcher();
                        $client->MainGame->content->Environment->resume();
                    }
                
                    Log::result("snd_all " . ($state ? "on" : "off"));
                    $this->edit->text = "";
                    break;
                        
                case "snd_ambient":
                    $client = $this->form('Client');
                    $options = $client->MainMenu->content->Options->content;
                    $btn = $options->AmbientSound_Switcher_Btn;
                    $isEnabled = ($btn->text == Localization::get('TurnOn_Label')); //ебаный даун
                
                    if (!isset($args[1]))
                    {
                        Log::result("snd_ambient " . ($isEnabled ? "on" : "off"));
                        $this->edit->text = "";
                        break;
                    }
                
                    $state = $this->parseBoolArg($args[1]);
                
                    if ($state === null)
                    {
                        Log::result("Usage: snd_ambient [on/off/1/0]");
                        $this->edit->text = "";
                        break;
                    }
                
                    if ($state !== $isEnabled)
                    {
                        $client->MainGame->content->Environment->pauseAmbient();
                        $options->AmbientSoundSwitcher();
                        $client->MainGame->content->Environment->resumeAmbient();
                    }
                
                    Log::result("snd_ambient " . ($state ? "on" : "off"));
                    $this->edit->text = "";
                    break;                       
                        
                case "version":
                        Log::result("PseudoStalker, " . VersionID . ", " . $this->form('Client')->BuildID);
                        $this->edit->text = "";
                        break;                       
                        
                case "save":
                        if ($this->form('Client')->CheckVisibledFragments()) return;
                    
                        $saveName = !empty($args[1]) ? trim($args[1]) : System::getProperty('user.name') . '_quicksave';
                            
                        $this->form('Client')->MainGame->content->performSave($saveName, true);
                        
                        $this->edit->text = "";
                        break;
                        
                case "load":
                
                        if ($this->form('Client')->CheckVisibledFragments()) return;
                    
                        if (!empty($args[1]))
                        {
                            $this->form('Client')->MainGame->content->SaveLoadManager->restoreGame(trim($args[1]));
                        }
                        else
                        {
                            $last = $this->form('Client')->MainMenu->content->UILoadWnd->content->SaveLoadManager->getLastSaveName();
                    
                            if ($last)
                            {
                                $this->form('Client')->MainGame->content->performLoad($last);
                            }
                        }
                    
                        $this->edit->text = "";
                        break;
                      
                case "call":
                        if (isset($args[1]))
                        {
                            $this->edit->text = "";

                            $parts = explode(".", $args[1]);

                            if (count($parts) >= 2)
                            {
                                $formName = array_shift($parts); 
                                $methodName = array_pop($parts); 
                                $fragmentPath = $parts; 

                                $form = app()->form($formName);
                                if (!$form)
                                {
                                    Log::result("Form '{$formName}' not found.");
                                    break;
                                }

                                $target = $form;
                                foreach ($fragmentPath as $fragment)
                                {
                                    if (isset($target->content))
                                    {
                                        $target = $target->content;
                                    }                                        
                                        
                                    if (isset($target->$fragment))
                                    {
                                        $target = $target->$fragment;
                                    }
                                    else
                                    {
                                        Log::result("Fragment '{$fragment}' not found in '{$formName}'.");
                                        break 2;
                                    }
                                }
                                        
                                if (isset($target->content))
                                {
                                    $target = $target->content;
                                }                                        

                                if (method_exists($target, $methodName))
                                {
                                    $methodArgs = array_slice($args, 2); 

                                    $argList = implode(", ", $methodArgs);
                                    Log::result("Calling method: {$args[1]}({$argList})");

                                    try {
                                        $result = call_user_func_array([$target, $methodName], $methodArgs);
                                        if ($result !== null)
                                        {
                                            Log::result("Result: " . print_r($result, true));
                                        }
                                        else
                                        {
                                            Log::result("Method executed successfully.");
                                        }
                                    } catch (Exception $e){
                                        Log::result("Error: " . $e->getMessage());
                                    }}
                                    else 
                                    {
                                        Log::result("Method '{$methodName}' not found in '{$args[1]}'.");
                                    }}
                            else
                            {
                                 Log::result("Usage: call formName[.fragment].methodName [arg1] [arg2] ...");
                            }
                        }
                        else
                        {
                            Log::result("Usage: call formName[.fragment].methodName [arg1] [arg2] ...");
                        }
                        
                        $this->edit->text = "";
                        break;

                case "language":
                        $args = explode(" ", trim($this->edit->text));

                        if (isset($args[1]) && Localization::isValidLanguage($args[1]))
                        {
                            $code = $args[1];
                    
                            Localization::setLanguage($code);
                            
                            $displayLanguage = Localization::getDisplayLanguage();
                    
                            $this->form('Client')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value = Localization::getDisplayLanguage();
                            
                            $this->form('Client')->MainMenu->content->Options->content->LanguageSwitcherCombobobx();
                            
                            Log::result("Language changed to: {$code} ({$displayLanguage})");
                        }
                        else
                        {
                            $currentLang = Localization::getCurrentLanguage();
                            $displayLang = Localization::getDisplayLanguage();
                    
                            Log::result("Current language: {$currentLang} ({$displayLang})");                            
                        }
                        
                        $this->edit->text = "";
                        break;
                        
                case "sync_sdk_ltx":
                        $this->form('Client')->syncWithSDKLTX();

                        $this->edit->text = "";
                        break;      
                        
                case "set_cycle":
                    if (isset($args[1]))
                    {
                        $cycle = strtolower($args[1]);
                        $allowed = ['morning', 'day', 'evening', 'night', 'underground'];
    
                        if (in_array($cycle, $allowed, true))
                        {
                            $this->edit->text = "";
    
                            $mg  = $this->form('Client')->MainGame->content;
                            if ($mg->Environment)
                            {
                                $mg->Environment->setCycle($cycle);
                            }
                            else
                            {
                                Log::result("Environment is not initialized.");
                            }
                        }
                        else
                        {
                            Log::result("Usage: set_cycle [morning|day|evening|night|underground]");
                        }
                    }
                    else
                    {
                        Log::result("Usage: set_cycle [morning|day|evening|night|underground]");
                    }
                    
                    $this->edit->text = "";
                    break;    
                    
                case "set_level":
                    if (isset($args[1]))
                    {
                        $arg = strtoupper($args[1]);
    
                        if ($arg[0] === 'L')
                        {
                            $num = substr($arg, 1);
                        }
                        else
                        {
                            $num = $arg;
                        }
    
                        if (is_numeric($num))
                        {
                            $idx = (int)$num;
                            if ($idx >= 0 && $idx <= 5)
                            {
                                $this->edit->text = "";
    
                                $mg = $this->form('Client')->MainGame->content;
                                if ($mg->Environment)
                                {
                                    $mg->Environment->setLocationIndex($idx);
                                }
                                else
                                {
                                    Log::result("Environment is not initialized.");
                                }
                            }
                            else
                            {
                                Log::result("Usage: set_level L0..L5 or 0..5");
                            }
                        }
                        else
                        {
                            Log::result("Usage: set_level L0..L5 or 0..5");
                        }
                    }
                    else
                    {
                        Log::result("Usage: set_level L0..L5 or 0..5");
                    }
                    
                    $this->edit->text = "";
                    break;
                                                           
                case "set_ambient":
                    if (isset($args[1]))
                    {
                        $idx = (int)$args[1];
    
                        $mg = $this->form('Client')->MainGame->content;
                        if ($mg->Environment)
                        {
                            $ok = $mg->Environment->playAmbientByIndex($idx);
                            if ($ok)
                            {
                                $this->edit->text = "";
                            }
                            else
                            {
                                Log::result("Invalid ambient index: {$idx}");
                            }
                        }
                        else
                        {
                            Log::result("Environment is not initialized.");
                        }
                    }
                    else
                    {
                        Log::result("Usage: set_ambient [index]");
                    }
                    
                    $this->edit->text = "";
                    break;
                    
                case "env_reset":
                    $mg = $this->form('Client')->MainGame->content;
                
                    if (isset($mg->Environment) && is_object($mg->Environment) && method_exists($mg->Environment, 'reset'))
                    {
                        $mg->Environment->reset();
                    }
                    else
                    {
                        Log::result("Environment is not initialized or reset() not found.");
                    }
                
                    $this->edit->text = "";
                    break;
                    
                case "fatal":
                    $message = isset($args[1]) ? implode(" ", array_slice($args, 1)) : "Fatal error triggered from console";
                    
                    Debug::fatal($message);
                    
                    $this->edit->text = "";
                    break;
                    
                case "flush":
                    Log::flush();
                
                    Log::result("Log buffer flushed.");
                
                    $this->edit->text = "";
                    break;   
                    
                case "threadpool_stresstest":
                    for ($i = 0; $i < 7777; $i++)
                    {
                        Log::info("TEST " . $i);
                    }
                    
                    Log::flush();
                    
                    $this->edit->text = "";
                    break;  
                    
                case "dbg_restracker":
                    $state = isset($args[1]) ? $this->parseBoolArg($args[1]) : null;
                
                    if ($state === null)
                    {
                        Log::result("Usage: dbg_restracker [off/on/1/0]");
                        $this->edit->text = "";
                        break;
                    }
                
                    $GLOBALS['ResTracker'] = $state;
                    
                    $this->form('Client')->Device_RefreshDebug();
                    
                    if ($this->form('Client')->ltxInitialized)
                    {
                        $this->form('Client')->ltx->w_bool('dbg_restracker', $state);
                    }                    
                
                    $this->edit->text = "";
                    break;                                                                          
                    
                default:
                    if ($this->edit->text != "")
                    {
                        $this->edit->text = "";
                        Log::result("Command '$command' does not exist.");
                    }
                    break;                    
        }
        
        $this->Console_Log->positionCaret(strlen($this->Console_Log->text));
    }
    /**
     * @event edit.keyDown-Up
     */
    function handleArrowUp(UXKeyEvent $e) 
    {    
        $this->tabMatches = [];
        if (!empty($this->commandHistory) && $this->historyIndex > 0)
        {
            $this->historyIndex--;
            $this->edit->text = $this->commandHistory[$this->historyIndex];
        }
        elseif ($this->historyIndex == -1 && !empty($this->commandHistory))
        {
            $this->historyIndex = count($this->commandHistory) - 1;
            $this->edit->text = $this->commandHistory[$this->historyIndex];
        }
        uiLater(function() {
           $this->edit->positionCaret(strlen($this->edit->text));
        });
    }
    /**
     * @event edit.keyDown-Down
     */
    function handleArrowDown(UXKeyEvent $e) 
    {    
        $this->tabMatches = [];
        if ($this->historyIndex < count($this->commandHistory) - 1)
        {
            $this->historyIndex++;
            $this->edit->text = $this->commandHistory[$this->historyIndex];
        }
        else
        {
            $this->historyIndex = count($this->commandHistory); 
            $this->edit->text = "";
        }
        uiLater(function() {
           $this->edit->positionCaret(strlen($this->edit->text));
        });
    }
    /**
     * @event edit.keyDown
     */
    function handleEditKeyDown(UXKeyEvent $e)
    {
        if ($e->codeName === 'TAB')
        {
            return;
        }

        $this->tabMatches = [];
        $this->tabIndex = 0;
    }
    
    /**
     * @event edit.keyDown-Tab
     */
    function autocomplete(UXKeyEvent $e)
    {
        $text  = trim($this->edit->text);
        $names = array_keys($this->availableCommands);
    
        if ($text === "")
        {
            $this->tabMatches = $names;
            $this->tabIndex   = 0;
    
            $this->edit->text = $this->tabMatches[$this->tabIndex];
            $this->edit->positionCaret(strlen($this->edit->text));
            $e->consume();
            return;
        }
    
        $exact      = false;
        $exactIndex = 0;
    
        foreach ($names as $i => $name)
        {
            if ($name === $text)
            {
                $exact      = true;
                $exactIndex = $i;
                break;
            }
        }
    
        if ($exact)
        {
            $this->tabMatches = $names;
    
            $this->tabIndex = $exactIndex + 1;
            if ($this->tabIndex >= count($this->tabMatches))
            {
                $this->tabIndex = 0;
            }
    
            $this->edit->text = $this->tabMatches[$this->tabIndex];
            $this->edit->positionCaret(strlen($this->edit->text));
            $e->consume();
            return;
        }
        
        if (empty($this->tabMatches))
        {
            $lower = strtolower($text);
            $this->tabMatches = [];
    
            foreach ($names as $name)
            {
                if (strpos($name, $lower) === 0)
                {
                    $this->tabMatches[] = $name;
                }
            }
    
            if (empty($this->tabMatches))
            {
                $e->consume();
                return;
            }
    
            $this->tabIndex = 0;
        }
        else
        {
            $this->tabIndex++;
            if ($this->tabIndex >= count($this->tabMatches))
            {
                $this->tabIndex = 0;
            }
        }
    
        $this->edit->text = $this->tabMatches[$this->tabIndex];
        $this->edit->positionCaret(strlen($this->edit->text));
    
        $e->consume();
    }

    /**
     * @event close_btn.click-Left 
     */
    function CloseConsole(UXMouseEvent $e = null)
    {    
        $this->form('Client')->Console->hide();
    }
}
