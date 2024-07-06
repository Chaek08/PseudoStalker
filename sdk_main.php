<?php
namespace app\forms;

use php\gui\framework\AbstractForm;
use php\gui\event\UXMouseEvent; 
use php\gui\event\UXWindowEvent; 


class sdk_main extends AbstractForm
{
    function GetSdkVersion()
    {
        if ($this->form('maingame')->debug_build->visible)
        {
            $this->version->text = "Build 43, Jule 6 2024"; //start date: 24 may 2024           
        }
        else 
        {
            $this->version->text = "v1.0";
        }
    }
    /**
     * @event SDK_Icon.click-2x
     */
    function DefaultSdkState(UXMouseEvent $e = null)
    {    
        $this->ResetButtonState();
        $this->ResetFragmentsVisible();
    }    
    function ResetButtonState()
    {
        $this->userdata_e_btn->textColor = 'black';
        $this->dialog_e_btn->textColor = 'black';
        $this->inv_e_btn->textColor = 'black';
        $this->mg_e_btn->textColor = 'black';
        $this->role_e_btn->textColor = 'black';
        $this->fail_e_btn->textColor = 'black';
    }
    function ResetFragmentsVisible()
    {
        if ($this->f_DialogEditor->visible) $this->f_DialogEditor->hide();
        if ($this->f_InvEditor->visible) $this->f_InvEditor->hide();
        if ($this->f_MgEditor->visible) $this->f_MgEditor->hide();
        if ($this->f_UserDataEditor->visible) $this->f_UserDataEditor->hide();
        if ($this->f_RoleEditor->visible) $this->f_RoleEditor->hide();
        if ($this->f_FailEditor->visible) $this->f_FailEditor->hide();        
    }
    /**
     * @event reset_all_btn.click-Left 
     */
    function ResetAllChanges(UXMouseEvent $e = null)
    {    
        $this->f_DialogEditor->content->ResetAll();
        $this->f_FailEditor->content->ResetAll();
        $this->f_InvEditor->content->ResetAll();   
        $this->f_MgEditor->content->ResetAll();      
        $this->f_RoleEditor->content->ResetAll();
        $this->f_UserDataEditor->content->ResetAll();                       
    }
    /**
     * @event start_game_btn.click-Left 
     */
    function StartMainGame(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->fragment_editor->hide();
    }      
    /**
     * @event userdata_e_btn.click-Left 
     */
    function OpenUserDataEditor(UXMouseEvent $e = null)
    {    
        $this->ResetButtonState();
        $this->userdata_e_btn->textColor = 'green';
        
        $this->ResetFragmentsVisible();
        $this->f_UserDataEditor->show();
    }
    /**
     * @event dialog_e_btn.click-Left 
     */
    function OpenDialogEditor(UXMouseEvent $e = null)
    {    
        $this->ResetButtonState();
        $this->dialog_e_btn->textColor = 'green';    
    
        $this->ResetFragmentsVisible();
        $this->f_DialogEditor->show();
    }
    /**
     * @event inv_e_btn.click-Left 
     */
    function OpenInvEditor(UXMouseEvent $e = null)
    {    
        $this->ResetButtonState();
        $this->inv_e_btn->textColor = 'green';
            
        $this->ResetFragmentsVisible();
        $this->f_InvEditor->show();
    }
    /**
     * @event mg_e_btn.click-Left 
     */
    function OpenMgEditor(UXMouseEvent $e = null)
    {    
        $this->ResetButtonState();
        $this->mg_e_btn->textColor = 'green';    
    
        $this->ResetFragmentsVisible();
        $this->f_MgEditor->show();
    }
    /**
     * @event role_e_btn.click-Left 
     */
    function OpenRoleEditor(UXMouseEvent $e = null)
    {
        $this->ResetButtonState();
        $this->role_e_btn->textColor = 'green';    
        
        $this->ResetFragmentsVisible();
        $this->f_RoleEditor->show();
    }    
    /**
     * @event fail_e_btn.click-Left 
     */
    function OpenFailEditor(UXMouseEvent $e = null)
    {
        $this->ResetButtonState();
        $this->fail_e_btn->textColor = 'green';    
        
        $this->ResetFragmentsVisible();
        $this->f_FailEditor->show();
    }  
}
