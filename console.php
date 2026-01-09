<?php
namespace app\forms;

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

class console extends AbstractForm
{
    private $localization;

    public function __construct()
    {
        parent::__construct();

        $this->localization = new Localization($language);
    }
    
    function getCurrentLanguageFromUI()
    {
        return $this->form('Client')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value;
    }
    
    private $availableCommands = [
        'exit'        => '',
        'clear'       => '',
        'help'        => '',
        'version'     => '',
        'sync_sdk_ltx'=> '',
        'save'        => ' [name]',
        'load'        => ' [name]',
        'g_god'       => ' [off/on]',
        'vid_mode'    => ' [1600x900]',
        'r_version'   => ' [off/on]',
        'r_shadows'   => ' [off/on]',
        'snd_all'     => ' [off/on]',
        'snd_ambient' => ' [off/on]',
        'openform'    => ' [form_name]',
        'call'        => ' [function_name]',
        'language'    => ' [rus/eng]',
        'set_level'   => ' [0-4]',
        'set_cycle'   => ' [night, morning, day, evening, underground]',
        'set_ambient' => ' [1-6]',
        'env_reset'   => '',
        'fatal'       => ' [message]'       
    ];

    private $tabMatches = [];
    private $commandHistory = []; 
    
    private $historyIndex = -1;
    private $tabIndex = 0;
    
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
        }
        $args = explode(" ", $command);
        $command = strtolower($args[0]);
        
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());        

        switch ($command) 
        {
                case "help":
                    $lines = [];
                
                    foreach ($this->availableCommands as $name => $hint)
                    {
                        $lines[] = $name . $hint;
                    }
                
                    $commandsList = implode("\n> ", $lines);
                
                    Element::appendText($this->Console_Log, "> Available commands:\n> {$commandsList}\n\n");
                    Element::appendText($this->Console_Log, "> If you cannot open the PDA, inventory, etc. with the console open, press the TAB key to switch focus!\n");
                    $this->edit->text = "";
                    break;

                case "exit":
                        $this->Console_Log->text = "> exit\n";
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
                        if (isset($args[1])) {
                                $formName = $args[1];
                                if (app()->form($formName)) {
                                        app()->showForm($formName);
                                        Element::appendText($this->Console_Log, "> Form '$formName' opened successfully.\n");
                                } else {
                                        Element::appendText($this->Console_Log, "> Form '$formName' not found.\n");
                                }
                        } else {
                                Element::appendText($this->Console_Log, "> Specify the form name: openform form_name\n");
                        }
                        $this->edit->text = "";
                        break;

                case "r_version":
                        if (isset($args[1])) {
                                Element::appendText($this->Console_Log, "> {$command} {$args[1]}\n");

                                $btn = $this->form('Client')->MainMenu->content->Options->content->Version_Switcher_Btn;
                                if (($args[1] == "off" && $btn->text == $this->localization->get('TurnOn_Label')) || ($args[1] == "on" && $btn->text == $this->localization->get('TurnOff_Label'))) {
                                        $this->form('Client')->MainMenu->content->Options->content->VersionSwitcher();
                                }
                        }
                        $this->edit->text = "";
                        break;
                        
                case "g_god":
                        if (isset($args[1])) {
                            Element::appendText($this->Console_Log, "> {$command} {$args[1]}\n");

                            if ($args[1] == "on")
                            {
                                $GLOBALS['GodMode'] = true;
                                $this->form('Client')->MainGame->content->GodMode();

                                if ($this->form('Client')->ltxInitialized)
                                {
                                    $this->form('Client')->ltx['g_god'] = 'on';
                                    $this->form('Client')->SaveUserLTX($this->form('Client')->ltx);
                                }
                            }
                            elseif ($args[1] == "off")
                            {
                                $GLOBALS['GodMode'] = false;
                                $this->form('Client')->MainGame->content->GodMode();

                                if ($this->form('Client')->ltxInitialized)
                                {
                                    $this->form('Client')->ltx['g_god'] = 'off';
                                    $this->form('Client')->SaveUserLTX($this->form('Client')->ltx);
                                }
                            }
                        }
                        $this->edit->text = "";
                        break;    
                
                case "vid_mode":
                        $form = $this->form('Client');

                        if (isset($args[1])) {
                                $resolution = $args[1];
                                $parts = explode('x', $resolution);

                                if (count($parts) === 2) {
                                        $targetW = (int)$parts[0];
                                        $targetH = (int)$parts[1];

                                        if ($targetW > 0 && $targetH > 0) {
                                                $clientW = $form->Client_Proxy->width;
                                                $clientH = $form->Client_Proxy->height;

                                                $diffW = $form->width - $clientW;
                                                $diffH = $form->height - $clientH;

                                                $form->width = $targetW + $diffW;
                                                $form->height = $targetH + $diffH;

                                                if ($form->ltxInitialized) {
                                                        $form->ltx['vid_mode'] = $resolution;
                                                        $form->SaveUserLTX($form->ltx);
                                                }

                                                if (method_exists($form, 'trackResolution')) {
                                                        $form->trackResolution();
                                                }
                                        }
                                }
                        } else {
                                $currentW = $form->Client_Proxy->width;
                                $currentH = $form->Client_Proxy->height;
                                Element::appendText($this->Console_Log, "> Current resolution: {$currentW}x{$currentH}\n");
                        }
                        $this->edit->text = "";
                        break;

                             

                case "r_shadows":
                        if (isset($args[1])) {
                                Element::appendText($this->Console_Log, "> {$command} {$args[1]}\n");

                                $btn = $this->form('Client')->MainMenu->content->Options->content->Shadows_Switcher_Btn;
                                if (($args[1] === "on" && $btn->text == $this->localization->get('TurnOff_Label')) || ($args[1] == "off" && $btn->text == $this->localization->get('TurnOn_Label'))) {
                                        $this->form('Client')->MainMenu->content->Options->content->ShadowsSwitcher();
                                }
                        }
                        else 
                        {
                            Element::appendText($this->Console_Log, "> Usage r_shadows [off/on]\n");
                        }                        
                        $this->edit->text = "";
                        break;

                case "snd_all":
                        if (isset($args[1])) {
                                Element::appendText($this->Console_Log, "> {$command} {$args[1]}\n");

                                $btn = $this->form('Client')->MainMenu->content->Options->content->AllSound_Switcher_Btn;
                                if (($args[1] === "off" && $btn->text == $this->localization->get('TurnOn_Label')) || ($args[1] === "on" && $btn->text == $this->localization->get('TurnOff_Label'))) {
                                        $this->form('Client')->MainGame->content->Environment->pause();
                                        $this->form('Client')->MainMenu->content->Options->content->AllSoundSwitcher();
                                        $this->form('Client')->MainGame->content->Environment->resume();
                                }
                        }
                        else 
                        {
                            Element::appendText($this->Console_Log, "> Usage snd_all [off/on]\n");
                        }                        
                        $this->edit->text = "";
                        break;
                        
                case "snd_ambient":
                        if (isset($args[1])) {
                                Element::appendText($this->Console_Log, "> {$command} {$args[1]}\n");

                                $btn = $this->form('Client')->MainMenu->content->Options->content->AmbientSound_Switcher_Btn;
                                if (($args[1] === "off" && $btn->text == $this->localization->get('TurnOn_Label')) || ($args[1] === "on" && $btn->text == $this->localization->get('TurnOff_Label'))) {
                                        $this->form('Client')->MainGame->content->Environment->pauseAmbient();
                                        $this->form('Client')->MainMenu->content->Options->content->AmbientSoundSwitcher();
                                        $this->form('Client')->MainGame->content->Environment->resumeAmbient();
                                }
                        }
                        else 
                        {
                            Element::appendText($this->Console_Log, "> Usage snd_ambient [off/on]\n");
                        }
                        $this->edit->text = "";
                        break;                        
                        
                case "version":
                        global $BuildID;
                        Element::appendText($this->Console_Log, "> PseudoStalker, " . VersionID . ", " . $this->form('Client')->BuildID . "\n");
                        $this->edit->text = "";
                        break;                       
                        
                case "save":
                        if (!$GLOBALS['ContinueGameState'] || $this->form('Client')->MainMenu->visible || $this->form('Client')->Fail->visible) return;

                        static $lastToastId = 0;

                        $parts = explode(" ", trim($this->edit->text), 2); 
                        $saveName = "";

                        if (count($parts) == 2 && trim($parts[1]) !== "")
                        {
                            $saveName = trim($parts[1]);
                        }
                        else
                        {
                            $username = System::getProperty('user.name');
                            $saveName = $username . '_quicksave';
                        }

                        $saveUI = $this->form('Client')->MainMenu->content->UISaveWnd->content;
                        $saveUI->Edit_SaveName->text = $saveName;
                        $GLOBALS['AutoRewriteSave'] = true;
                        $saveUI->BtnSaveGame();

                        Element::appendText($this->Console_Log, "> Saved game: $saveName\n");

                        $this->form('Client')->MainGame->content->SavedGame_Toast->opacity = 0;
                        $this->form('Client')->MainGame->content->SavedGame_Toast->visible = true;
                        $this->form('Client')->MainGame->content->SavedGame_Toast->text = $this->localization->get('SavedGameToast') . ' ' . $saveName;

                        Animation::fadeIn($this->form('Client')->MainGame->content->SavedGame_Toast, 300);

                        $lastToastId++;
                        $currentId = $lastToastId;

                        Timer::after(2300, function () use ($currentId) {
                            if ($currentId == $GLOBALS['lastToastId'])
                            {
                                Animation::fadeOut($this->form('Client')->MainGame->content->SavedGame_Toast, 300);
                            }
                        });

                        $GLOBALS['AutoRewriteSave'] = false;

                        $GLOBALS['lastToastId'] = $lastToastId;
                        
                        $this->edit->text = "";
                        break;
                        
                case "load":
                    $parts = explode(" ", trim($this->edit->text), 2);
                    if (count($parts) == 2)
                    {
                        $saveName = trim($parts[1]);
                        if ($saveName !== "")
                        {
                            $filePath = $this->form('Client')->MainMenu->content->UILoadWnd->content->SaveLoadManager->getSaveDir() . $saveName . '.sav';
                            if (file_exists($filePath))
                            {
                                $loadWnd = $this->form('Client')->MainMenu->content->UILoadWnd->content;
                                $savesList = $loadWnd->saves_list;
                                foreach ($savesList->items->toArray() as $index => $item) {
                                if ($item == $saveName)
                                {
                                    $savesList->selectedIndex = $index;
                                    $this->form('Client')->MainMenu->content->UILoadWnd->content->BtnLoadSave();
                                    Element::appendText($this->Console_Log, "> Loaded save: $saveName\n");
                                    break;
                                }}
                        }
                        else
                        {
                            Element::appendText($this->Console_Log, "> Save '$saveName' not found.\n");
                        }
                    }
                }
                
                $this->edit->text = "";
                break;
                      
                case "call":
                        if (isset($args[1])) {
                                $this->edit->text = "";

                                $parts = explode(".", $args[1]);

                                if (count($parts) >= 2) {
                                        $formName = array_shift($parts); 
                                        $methodName = array_pop($parts); 
                                        $fragmentPath = $parts; 

                                        $form = app()->form($formName);
                                        if (!$form) {
                                                Element::appendText($this->Console_Log, "> Form '{$formName}' not found.\n");
                                                break;
                                        }

                                        $target = $form;
                                        foreach ($fragmentPath as $fragment) {
                                                if (isset($target->content)) {
                                                        $target = $target->content;
                                                }                                        
                                        
                                                if (isset($target->$fragment)) {
                                                        $target = $target->$fragment;
                                                } else {
                                                        Element::appendText($this->Console_Log, "> Fragment '{$fragment}' not found in '{$formName}'.\n");
                                                        break 2;
                                                }
                                        }
                                        
                                        if (isset($target->content)) {
                                                $target = $target->content;
                                        }                                        

                                        if (method_exists($target, $methodName)) {
                                                $methodArgs = array_slice($args, 2); 

                                                $argList = implode(", ", $methodArgs);
                                                Element::appendText($this->Console_Log, "> Calling method: {$args[1]}({$argList})\n");

                                                try {
                                                        $result = call_user_func_array([$target, $methodName], $methodArgs);
                                                        if ($result !== null) {
                                                                Element::appendText($this->Console_Log, "> Result: " . print_r($result, true) . "\n");
                                                        } else {
                                                                Element::appendText($this->Console_Log, "> Method executed successfully.\n");
                                                        }
                                                } catch (Exception $e) {
                                                        Element::appendText($this->Console_Log, "> Error: " . $e->getMessage() . "\n");
                                                }
                                        } else {
                                                Element::appendText($this->Console_Log, "> Method '{$methodName}' not found in '{$args[1]}'.\n");
                                        }
                                } else {
                                        Element::appendText($this->Console_Log, "> Usage: call formName[.fragment].methodName [arg1] [arg2] ...\n");
                                }
                        } else {
                                Element::appendText($this->Console_Log, "> Usage: call formName[.fragment].methodName [arg1] [arg2] ...\n");
                        }
                        
                        $this->edit->text = "";
                        break;

                case "language":
                        $args = explode(" ", trim($this->edit->text));

                        $languageMap = [
                                'rus' => 'Русский',
                                'eng' => 'English'
                        ];

                        if (isset($args[1]) && in_array($args[1], array_keys($languageMap))) {
                                $this->localization->setLanguage($args[1]);
                                $this->form('Client')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value = $languageMap[$args[1]];
                                $this->form('Client')->MainMenu->content->Options->content->LanguageSwitcherCombobobx();
                                Element::appendText($this->Console_Log, "> Language changed to: {$args[1]} ({$languageMap[$args[1]]})\n");
                        } else {
                                $currentLang = $this->localization->getCurrentLanguage();
                                $displayLang = $languageMap[$currentLang] ?? $currentLang;
                                Element::appendText($this->Console_Log, "> Current language: {$currentLang} ({$displayLang})\n");
                        }
                        
                        $this->edit->text = "";
                        break;
                        
                case "sync_sdk_ltx":
                        $this->form('Client')->syncWithSDKLTX();
                        Element::appendText($this->Console_Log, "> {$command}\n");
                        
                        $this->edit->text = "";
                        break;      
                        
                case "set_cycle":
                    if (isset($args[1])) {
                        $cycle = strtolower($args[1]);
                        $allowed = ['morning', 'day', 'evening', 'night', 'underground'];
    
                        if (in_array($cycle, $allowed, true)) {
                            $this->edit->text = "";
    
                            $mg  = $this->form('Client')->MainGame->content;
                            if ($mg->Environment) {
                                $mg->Environment->setCycle($cycle);
                                Element::appendText(
                                    $this->Console_Log,
                                    "> set_cycle {$cycle}\n"
                                );
                            } else {
                                Element::appendText(
                                    $this->Console_Log,
                                    "> Environment is not initialized.\n"
                                );
                            }
                        } else {
                            Element::appendText(
                                $this->Console_Log,
                                "> Usage: set_cycle [morning|day|evening|night|underground]\n"
                            );
                        }
                    } else {
                        Element::appendText(
                            $this->Console_Log,
                            "> Usage: set_cycle [morning|day|evening|night|underground]\n"
                        );
                    }
                    
                    $this->edit->text = "";
                    break;    
                    
                case "set_level":
                    if (isset($args[1])) {
                        $arg = strtoupper($args[1]);
    
                        if ($arg[0] === 'L') {
                            $num = substr($arg, 1);
                        } else {
                            $num = $arg;
                        }
    
                        if (is_numeric($num)) {
                            $idx = (int)$num;
                            if ($idx >= 0 && $idx <= 5) {
                                $this->edit->text = "";
    
                                $mg = $this->form('Client')->MainGame->content;
                                if ($mg->Environment) {
                                    $mg->Environment->setLocationIndex($idx);
                                    Element::appendText(
                                        $this->Console_Log,
                                        "> set_level L{$idx}\n"
                                    );
                                } else {
                                    Element::appendText(
                                        $this->Console_Log,
                                        "> Environment is not initialized.\n"
                                    );
                                }
                            } else {
                                Element::appendText(
                                    $this->Console_Log,
                                    "> Usage: set_level L0..L5 or 0..5\n"
                                );
                            }
                        } else {
                            Element::appendText(
                                $this->Console_Log,
                                "> Usage: set_level L0..L5 or 0..5\n"
                            );
                        }
                    } else {
                        Element::appendText(
                            $this->Console_Log,
                            "> Usage: set_level L0..L5 or 0..5\n"
                        );
                    }
                    
                    $this->edit->text = "";
                    break;
                                                           
                case "set_ambient":
                    if (isset($args[1])) {
                        $idx = (int)$args[1];
    
                        $mg = $this->form('Client')->MainGame->content;
                        if ($mg->Environment) {
                            $ok = $mg->Environment->playAmbientByIndex($idx);
                            if ($ok) {
                                $this->edit->text = "";
                                Element::appendText(
                                    $this->Console_Log,
                                    "> set_ambient {$idx}\n"
                                );
                            } else {
                                Element::appendText(
                                    $this->Console_Log,
                                    "> Invalid ambient index: {$idx}\n"
                                );
                            }
                        } else {
                            Element::appendText(
                                $this->Console_Log,
                                "> Environment is not initialized.\n"
                            );
                        }
                    } else {
                        Element::appendText(
                            $this->Console_Log,
                            "> Usage: set_ambient [index]\n"
                        );
                    }
                    
                    $this->edit->text = "";
                    break;
                    
                case "env_reset":
                    Element::appendText($this->Console_Log, "> env_reset\n");
                
                    $mg = $this->form('Client')->MainGame->content;
                
                    if (isset($mg->Environment) && is_object($mg->Environment) && method_exists($mg->Environment, 'reset'))
                    {
                        $mg->Environment->reset();
                    }
                    else
                    {
                        Element::appendText($this->Console_Log, "> Environment is not initialized or reset() not found.\n");
                    }
                
                    $this->edit->text = "";
                    break;
                    
                case "fatal":
                    $message = isset($args[1]) ? implode(" ", array_slice($args, 1)) : "Fatal error triggered from console";
                    
                    Debug::fatal($message, __FILE__, __LINE__);
                    
                    $this->edit->text = "";
                    break;
                    
                    

                default:
                        if ($this->edit->text != "") {
                                $this->edit->text = "";
                                Element::appendText($this->Console_Log, "> Command '$command' does not exist.\n");
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
