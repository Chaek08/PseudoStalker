<?php
namespace app\forms;

use action\Element;
use php\gui\framework\AbstractForm;
use php\gui\event\UXWindowEvent; 
use php\gui\event\UXKeyEvent; 
use php\gui\event\UXMouseEvent; 


class console extends AbstractForm
{
    /**
     * @event show 
     */
    function InitConsole(UXWindowEvent $e = null)
    {    

    }
    /**
     * @event edit.keyDown-Enter 
     */
    function EnterCommands(UXKeyEvent $e = null)
    {    
        $command = $this->edit->text;
        switch($command) 
        {
            case "clear":
            $this->edit->text = "";
            $this->console_list->text = "";                                    
            break;     
            
            case "help":
            $this->edit->text = "";   
            Element::appendText($this->console_list, "> exit,  clear,  help,  version,  hide_watermark, show_watermark, reset_game_client\n");
            break;  
            
            case "exit":
            $this->console_list->text = "> exit\n";
            $this->edit->text = "";
            $this->form('maingame')->OpenConsole();
            $this->form('exit_dlg')->AcceptButton();
            break;    
            
            case "hide_watermark":
            $this->edit->text = "";   
            Element::appendText($this->console_list, "> Watermark hidden..\n");
            $this->form('maingame')->fragment_opt->content->WatermarkOff();
            break;
        
            case "show_watermark":
            $this->edit->text = "";   
            Element::appendText($this->console_list, "> Watermark visible..\n");                                
            $this->form('maingame')->fragment_opt->content->WatermarkOn();
            break;  
            
            case "reset_game_client":
            $this->edit->text = "";   
            Element::appendText($this->console_list, "> function ResetGameClient() executed\n");                                
            $this->form('maingame')->ResetGameClient();
            break;                                             
        
            case "version":
            $this->edit->text = "";   
            Element::appendText($this->console_list, "> ");
            Element::appendText($this->console_list, uiText($this->form('maingame')->fragment_menu->content->label_version));
            Element::appendText($this->console_list, "\n");
            break;
            
            default:
            if ($this->edit->text == '') {}   
            else
            {
                $this->edit->text = "";                                  
                Element::appendText($this->console_list, "> There is no such command\n");                                    
            }
            break;              
        }        
    }
    /**
     * @event close_btn.click-Left 
     */
    function CloseConsole(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->fragment_console->hide();
    }
}
