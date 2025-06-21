<?php
namespace app\forms;

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

class console extends AbstractForm
{
    private $localization;

    public function __construct() {
        parent::__construct();

        $this->localization = new Localization($language);
    }
    
    private $commandHistory = []; 
    private $historyIndex = -1;
    
    /**
     * @event edit.keyDown-Enter 
     */
    function EnterCommands(UXKeyEvent $e = null)
    {    
        $this->localization->setLanguage($this->form('maingame')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value);
        $this->requestFocus();
    
        $command = trim($this->edit->text);
        if ($command !== "")
        {
            $this->commandHistory[] = $command; // сохраняем в историю
            $this->historyIndex = count($this->commandHistory); // сбрасываем индекс
        }
        $args = explode(" ", $command);
        $command = strtolower($args[0]);

        switch ($command) 
        {
                case "clear":
                        $this->edit->text = "";
                        $this->Console_Log->text = "";
                        break;

                case "help":
                        $this->edit->text = "";
                        $commands = [
                                "exit",
                                "clear",
                                "help",
                                "version",
                                "sync_sdk_ltx",
                                "save",
                                "load",
                                "g_god [off/on]",                                  
                                "r_version [off/on]",
                                "r_shadows [off/on]",
                                "snd_all [off/on]",
                                "openform [form_name]",
                                "call [function_name]",                           
                                "language [rus/eng]"
                        ];
                        $commandsList = implode("\n> ", $commands);
                        Element::appendText($this->Console_Log, "> Available commands:\n> $commandsList\n\n");
                        Element::appendText($this->Console_Log, "> If you cannot open the PDA, inventory, etc. with the console open, press the TAB key to switch focus!\n");
                        break;
                        
                case "openform":
                        if (isset($args[1])) {
                                $formName = $args[1];
                                $this->edit->text = "";
                                if (app()->form($formName)) {
                                        app()->showForm($formName);
                                        Element::appendText($this->Console_Log, "> Form '$formName' opened successfully.\n");
                                } else {
                                        Element::appendText($this->Console_Log, "> Form '$formName' not found.\n");
                                }
                        } else {
                                Element::appendText($this->Console_Log, "> Specify the form name: openform form_name\n");
                        }
                        break;

                case "r_version":
                        if (isset($args[1])) {
                                $this->edit->text = "";
                                Element::appendText($this->Console_Log, "> {$command} {$args[1]}\n");

                                $btn = $this->form('maingame')->MainMenu->content->Options->content->Version_Switcher_Btn;
                                if (($args[1] == "off" && $btn->text == $this->localization->get('TurnOn_Label')) || ($args[1] == "on" && $btn->text == $this->localization->get('TurnOff_Label'))) {
                                        $this->form('maingame')->MainMenu->content->Options->content->VersionSwitcher_MouseDownLeft();
                                        $this->form('maingame')->MainMenu->content->Options->content->VersionSwitcher_MouseExit();
                                }
                        }
                        break;
                        
                case "g_god":
                        if (isset($args[1])) {
                            $this->edit->text = "";
                            Element::appendText($this->Console_Log, "> {$command} {$args[1]}\n");

                            if ($args[1] == "on")
                            {
                                $GLOBALS['GodMode'] = true;
                                $this->form('maingame')->GodMode();

                                if ($this->form('maingame')->ltxInitialized)
                                {
                                    $this->form('maingame')->ltx['g_god'] = 'on';
                                    $this->form('maingame')->SaveUserLTX($this->form('maingame')->ltx);
                                }
                            }
                            elseif ($args[1] == "off")
                            {
                                $GLOBALS['GodMode'] = false;
                                $this->form('maingame')->GodMode();

                                if ($this->form('maingame')->ltxInitialized)
                                {
                                    $this->form('maingame')->ltx['g_god'] = 'off';
                                    $this->form('maingame')->SaveUserLTX($this->form('maingame')->ltx);
                                }
                            }
                        }
                        break;    
                
                case "vid_mode":
                    if (isset($args[1])) {
                        $resolution = $args[1];
                        $parts = explode('x', $resolution);
                        if (count($parts) == 2) {
                            $width = (int)$parts[0];
                            $height = (int)$parts[1];

                            if ($width > 0 && $height > 0) {
                                $form = $this->form('maingame');

                                $form->width = $width;
                                $form->height = $height;

                                Timer::after(300, function () use ($form, $width, $height, $resolution) {
                                    if ($form->Environment_Background) {
                                        $clientW = $form->Environment_Background->width;
                                        $clientH = $form->Environment_Background->height;
                                    } else if ($form->scene && $form->scene->window) {
                                        $clientW = $form->scene->window->width;
                                        $clientH = $form->scene->window->height;
                                    } else {
                                        $clientW = $form->width;
                                        $clientH = $form->height;
                                    }

                                    $diffW = $form->width - $clientW;
                                    $diffH = $form->height - $clientH;

                                    $form->width = $width + $diffW;
                                    $form->height = $height + $diffH;

                                    if ($form->ltxInitialized) {
                                        $form->ltx['vid_mode'] = $resolution;
                                        $form->SaveUserLTX($form->ltx);
                                    }
                                });
                            }
                        }
                    } else {
                        $this->edit->text = "";
                        $form = $this->form('maingame');
                        $currentWidth = $form->Environment_Background->width;
                        $currentHeight = $form->Environment_Background->height;
                        Element::appendText($this->Console_Log, "> Current resolution: {$currentWidth}x{$currentHeight}\n");
                    }
                    break;
                             

                case "r_shadows":
                        if (isset($args[1])) {
                                $this->edit->text = "";
                                Element::appendText($this->Console_Log, "> {$command} {$args[1]}\n");

                                $btn = $this->form('maingame')->MainMenu->content->Options->content->Shadows_Switcher_Btn;
                                if (($args[1] === "on" && $btn->text == $this->localization->get('TurnOff_Label')) || ($args[1] == "off" && $btn->text == $this->localization->get('TurnOn_Label'))) {
                                        $this->form('maingame')->MainMenu->content->Options->content->ShadowsSwitcher_MouseDownLeft();
                                        $this->form('maingame')->MainMenu->content->Options->content->ShadowsSwitcher_MouseExit();
                                }
                        }
                        break;

                case "snd_all":
                        if (isset($args[1])) {
                                $this->edit->text = "";
                                Element::appendText($this->Console_Log, "> {$command} {$args[1]}\n");

                                $btn = $this->form('maingame')->MainMenu->content->Options->content->AllSound_Switcher_Btn;
                                if (($args[1] === "off" && $btn->text == $this->localization->get('TurnOn_Label')) || ($args[1] === "on" && $btn->text == $this->localization->get('TurnOff_Label'))) {
                                        $this->form('maingame')->MainMenu->content->Options->content->AllSoundSwitcher_MouseDownLeft();
                                        $this->form('maingame')->MainMenu->content->Options->content->AllSoundSwitcher_MouseExit();
                                }
                        }
                        break;
                        
                case "version":
                        $this->edit->text = "";
                        global $BuildID;
                        Element::appendText($this->Console_Log, "> PseudoStalker, " . VersionID . ", " . $BuildID . "\n");
                        break;                       
                        
                case "save":
                        if (!$GLOBALS['ContinueGameState'] || $this->form('maingame')->MainMenu->visible || $this->form('maingame')->Fail->visible) return;

                        static $lastToastId = 0;

                        $this->localization->setLanguage($this->form('maingame')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value);

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

                        $saveUI = $this->form('maingame')->MainMenu->content->UISaveWnd->content;
                        $saveUI->Edit_SaveName->text = $saveName;
                        $GLOBALS['AutoRewriteSave'] = true;
                        $saveUI->BtnSaveGame();

                        Element::appendText($this->Console_Log, "> Saved game: $saveName\n");
                        $this->edit->text = "";

                        $this->form('maingame')->SavedGame_Toast->opacity = 0;
                        $this->form('maingame')->SavedGame_Toast->visible = true;
                        $this->form('maingame')->SavedGame_Toast->text = $this->localization->get('SavedGameToast') . ' ' . $saveName;

                        Animation::fadeIn($this->form('maingame')->SavedGame_Toast, 300);

                        $lastToastId++;
                        $currentId = $lastToastId;

                        Timer::after(2300, function () use ($currentId) {
                            if ($currentId == $GLOBALS['lastToastId'])
                            {
                                Animation::fadeOut($this->form('maingame')->SavedGame_Toast, 300);
                            }
                        });

                        $GLOBALS['AutoRewriteSave'] = false;

                        $GLOBALS['lastToastId'] = $lastToastId;
                        break;
                        
                case "load":
                    $parts = explode(" ", trim($this->edit->text), 2);
                    if (count($parts) == 2)
                    {
                        $saveName = trim($parts[1]);
                        if ($saveName !== "")
                        {
                            $filePath = SAVE_DIRECTORY . $saveName . '.sav';
                            if (file_exists($filePath))
                            {
                                $loadWnd = $this->form('maingame')->MainMenu->content->UILoadWnd->content;
                                $savesList = $loadWnd->saves_list;
                                foreach ($savesList->items->toArray() as $index => $item) {
                                if ($item == $saveName)
                                {
                                    $savesList->selectedIndex = $index;
                                    $this->form('maingame')->MainMenu->content->UILoadWnd->content->BtnLoadSave();
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
                                                if (isset($target->$fragment)) {
                                                        $target = $target->$fragment;
                                                } else {
                                                        Element::appendText($this->Console_Log, "> Fragment '{$fragment}' not found in '{$formName}'.\n");
                                                        break 2;
                                                }
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
                        break;

                case "language":
                        $args = explode(" ", trim($this->edit->text));
                        $this->edit->text = "";

                        $languageMap = [
                                'rus' => 'Русский',
                                'eng' => 'English'
                        ];

                        if (isset($args[1]) && in_array($args[1], array_keys($languageMap))) {
                                $this->localization->setLanguage($args[1]);
                                $this->form('maingame')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value = $languageMap[$args[1]];
                                $this->form('maingame')->MainMenu->content->Options->content->LanguageSwitcherCombobobx();
                                Element::appendText($this->Console_Log, "> Language changed to: {$args[1]} ({$languageMap[$args[1]]})\n");
                        } else {
                                $currentLang = $this->localization->getCurrentLanguage();
                                $displayLang = $languageMap[$currentLang] ?? $currentLang;
                                Element::appendText($this->Console_Log, "> Current language: {$currentLang} ({$displayLang})\n");
                        }
                        break;
                        
                case "sync_sdk_ltx":
                        $this->form('maingame')->syncWithSDKLTX();
                        Element::appendText($this->Console_Log, "> {$command}\n");
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
     * @event close_btn.click-Left 
     */
    function CloseConsole(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->Console->hide();
    }
}
