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
            Element::appendText($this->console_list, "> exit,  clear,  help,  version, r_version [off/on], reset_game_client, r_shadows [off/on], snd_all [off/on]\n\nЕсли при открытой консоли вы не можете открывать пда, инвентарь и т.д, то нажмите клавишу TAB, чтобы переключить фокус!\n");
            break;  
            
            case "exit":
            $this->console_list->text = "> exit\n";
            $this->edit->text = "";
            $this->form('maingame')->OpenConsole();
            $this->form('exit_dlg')->AcceptButton();
            break;    
            
            case "r_version off":
            $this->edit->text = "";   
            Element::appendText($this->console_list, "> r_version off\n");
            $this->form('maingame')->Options->content->WatermarkOff();
            break;
        
            case "r_version on":
            $this->edit->text = "";   
            Element::appendText($this->console_list, "> r_version on\n");                                
            $this->form('maingame')->Options->content->WatermarkOn();
            break;  
            
            case "r_shadows on":
            $this->edit->text = "";   
            Element::appendText($this->console_list, "> r_shadows on\n");                                
            $this->form('maingame')->Options->content->ShadowOptOn();
            break;    
            
            case "r_shadows off":
            $this->edit->text = "";   
            Element::appendText($this->console_list, "> r_shadows off\n");                                
            $this->form('maingame')->Options->content->ShadowOptOff();
            break;   
            
            case "snd_all off":
            $this->edit->text = "";   
            Element::appendText($this->console_list, "> snd_all off\n");                                
            $this->form('maingame')->Options->content->AllSoundOff();
            break;    
            
            case "snd_all on":
            $this->edit->text = "";   
            Element::appendText($this->console_list, "> snd_all on\n");                                
            $this->form('maingame')->Options->content->AllSoundOn();
            break;                                           
            
            case "reset_game_client":
            $this->edit->text = "";   
            Element::appendText($this->console_list, "> function ResetGameClient() executed\n");                                
            $this->form('maingame')->ResetGameClient();
            break;                                             
        
            case "version":
            $this->edit->text = "";   
            Element::appendText($this->console_list, "> ");
            Element::appendText($this->console_list, "PseudoStalker, ");
            Element::appendText($this->console_list, uiText($this->form('maingame')->version_detail));
            Element::appendText($this->console_list, "\n");
            break;
            
            default:
            if ($this->edit->text != '')
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
        $this->form('maingame')->Console->hide();
    }
}
