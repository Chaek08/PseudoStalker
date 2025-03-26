<?php
namespace app\forms;

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
        $this->localization->setLanguage($this->form('maingame')->Options->content->Language_Switcher_Combobobx->value);
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
                                "r_version [off/on]",
                                "r_shadows [off/on]",
                                "snd_all [off/on]",
                                "reset_game_client",
                                "openform [form_name]",
                                "ToggleHud",
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

                                $btn = $this->form('maingame')->Options->content->Version_Switcher_Btn;
                                if (($args[1] === "off" && $btn->text === 'Вкл') || ($args[1] === "on" && $btn->text === 'Выкл')) {
                                        $this->form('maingame')->Options->content->VersionSwitcher_MouseDownLeft();
                                        $this->form('maingame')->Options->content->VersionSwitcher_MouseExit();
                                }
                        }
                        break;

                case "r_shadows":
                        if (isset($args[1])) {
                                $this->edit->text = "";
                                Element::appendText($this->Console_Log, "> {$command} {$args[1]}\n");

                                $btn = $this->form('maingame')->Options->content->Shadows_Switcher_Btn;
                                if (($args[1] === "on" && $btn->text === 'Выкл') || ($args[1] === "off" && $btn->text === 'Вкл')) {
                                        $this->form('maingame')->Options->content->ShadowsSwitcher_MouseDownLeft();
                                        $this->form('maingame')->Options->content->ShadowsSwitcher_MouseExit();
                                }
                        }
                        break;

                case "snd_all":
                        if (isset($args[1])) {
                                $this->edit->text = "";
                                Element::appendText($this->Console_Log, "> {$command} {$args[1]}\n");

                                $btn = $this->form('maingame')->Options->content->AllSound_Switcher_Btn;
                                if (($args[1] === "off" && $btn->text === 'Вкл') || ($args[1] === "on" && $btn->text === 'Выкл')) {
                                        $this->form('maingame')->Options->content->AllSoundSwitcher_MouseDownLeft();
                                        $this->form('maingame')->Options->content->AllSoundSwitcher_MouseExit();
                                }
                        }
                        break;
/*
                case "reset_game_client":
                        $this->edit->text = "";
                        Element::appendText($this->Console_Log, "> function ResetGameClient() executed\n");
                        $this->form('maingame')->ResetGameClient();
                        break;
*/
                case "version":
                        $this->edit->text = "";
                        Element::appendText($this->Console_Log, "> PseudoStalker, " . VersionID . ", " . BuildID . "\n");
                        break;
/*
                case "ToggleHud":
                        $this->edit->text = "";
                        if (ToggleHudFeature) {
                                Element::appendText($this->Console_Log, "> function ToggleHud() executed\n");
                                $this->form('maingame')->ToggleHud();
                        }
                        break;
*/                        
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
                                $this->form('maingame')->Options->content->Language_Switcher_Combobobx->value = $languageMap[$args[1]];
                                $this->form('maingame')->Options->content->LanguageSwitcherCombobobx();
                                Element::appendText($this->Console_Log, "> Language changed to: {$args[1]} ({$languageMap[$args[1]]})\n");
                        } else {
                                $currentLang = $this->localization->getCurrentLanguage();
                                $displayLang = $languageMap[$currentLang] ?? $currentLang;
                                Element::appendText($this->Console_Log, "> Current language: {$currentLang} ({$displayLang})\n");
                        }
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
        if (!empty($this->commandHistory) && $this->historyIndex > 0) {
            $this->historyIndex--;
            $this->edit->text = $this->commandHistory[$this->historyIndex];
        } elseif ($this->historyIndex == -1 && !empty($this->commandHistory)) {
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
        if ($this->historyIndex < count($this->commandHistory) - 1) {
            $this->historyIndex++;
            $this->edit->text = $this->commandHistory[$this->historyIndex];
        } else {
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
