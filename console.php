<?php
namespace app\forms;

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
        $command = $args[0];

        switch ($command) 
        {
                case "clear":
                        $this->edit->text = "";
                        $this->console_list->text = "";
                        break;

                case "help":
                        $this->edit->text = "";
                        Element::appendText($this->console_list, "> exit, clear, help, version, r_version [off/on], reset_game_client, r_shadows [off/on], snd_all [off/on]\n\n");
                        Element::appendText($this->console_list, "Если при открытой консоли вы не можете открывать пда, инвентарь и т.д, то нажмите клавишу TAB, чтобы переключить фокус!\n");
                        break;

                case "exit":
                        $this->console_list->text = "> exit\n";
                        $this->edit->text = "";
                        $this->form('maingame')->OpenConsole();
                        $this->form('exit_dlg')->AcceptButton();
                        break;
                        
                case "openform":
                        if (isset($args[1])) {
                                $formName = $args[1];
                                $this->edit->text = "";
                                if (app()->form($formName)) {
                                        app()->showForm($formName);
                                        Element::appendText($this->console_list, "> Form '$formName' opened successfully.\n");
                                } else {
                                        Element::appendText($this->console_list, "> Form '$formName' not found.\n");
                                }
                        } else {
                                Element::appendText($this->console_list, "> Specify the form name: openform form_name\n");
                        }
                        break;

                case "r_version":
                        if (isset($args[1])) {
                                $this->edit->text = "";
                                Element::appendText($this->console_list, "> r_version {$args[1]}\n");

                                $btn = $this->form('maingame')->Options->content->Version_Switcher_Btn;
                                if (($args[1] === "off" && $btn->text === 'Вкл') || ($args[1] === "on" && $btn->text === 'Выкл')) {
                                        $this->form('maingame')->Options->content->VersionSwitcher_MouseDownLeft();
                                }
                        }
                        break;

                case "r_shadows":
                        if (isset($args[1])) {
                                $this->edit->text = "";
                                Element::appendText($this->console_list, "> r_shadows {$args[1]}\n");

                                $btn = $this->form('maingame')->Options->content->Shadows_Switcher_Btn;
                                if (($args[1] === "on" && $btn->text === 'Выкл') || ($args[1] === "off" && $btn->text === 'Вкл')) {
                                        $this->form('maingame')->Options->content->ShadowsSwitcher_MouseDownLeft();
                                }
                        }
                        break;

                case "snd_all":
                        if (isset($args[1])) {
                                $this->edit->text = "";
                                Element::appendText($this->console_list, "> snd_all {$args[1]}\n");

                                $btn = $this->form('maingame')->Options->content->AllSound_Switcher_Btn;
                                if (($args[1] === "off" && $btn->text === 'Вкл') || ($args[1] === "on" && $btn->text === 'Выкл')) {
                                        $this->form('maingame')->Options->content->AllSoundSwitcher_MouseDownLeft();
                                }
                        }
                        break;

                case "reset_game_client":
                        $this->edit->text = "";
                        Element::appendText($this->console_list, "> function ResetGameClient() executed\n");
                        $this->form('maingame')->ResetGameClient();
                        break;

                case "version":
                        $this->edit->text = "";
                        Element::appendText($this->console_list, "> PseudoStalker, " . VersionID . ", " . BuildID . "\n");
                        break;

                case "ToggleHud":
                        $this->edit->text = "";
                        if (ToggleHudFeature) {
                                Element::appendText($this->console_list, "> function ToggleHud() executed\n");
                                $this->form('maingame')->ToggleHud();
                        }
                        break;

                case "language":
                        $args = explode(" ", trim($this->edit->text));
                        $this->edit->text = "";

                        if (isset($args[1]) && in_array($args[1], ['rus', 'eng'])) {
                                $this->localization->setLanguage($args[1]);
                                $this->form('maingame')->Options->content->Language_Switcher_Combobobx->value = $args[1];
                                $this->form('maingame')->Options->content->LanguageSwitcherCombobobx();
                                Element::appendText($this->console_list, "> Language changed to: {$args[1]}\n");
                        } else {
                                Element::appendText($this->console_list, "> Current language: " . $this->localization->getCurrentLanguage() . "\n");
                        }
                        break;

                default:
                        if ($this->edit->text != "") {
                                $this->edit->text = "";
                                Element::appendText($this->console_list, "> There is no such command\n");
                        }
                        break;
        }
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
    }
    /**
     * @event close_btn.click-Left 
     */
    function CloseConsole(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->Console->hide();
    }
}
