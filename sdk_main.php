<?php
namespace app\forms;

use php\gui\framework\AbstractForm;
use php\gui\event\UXMouseEvent; 
use php\gui\event\UXWindowEvent; 


class sdk_main extends AbstractForm
{
    /**
     * @event show 
     */
    function InitSdk(UXWindowEvent $e = null)
    {    
        
    }
    function GetSdkVersion()
    {
        if ($this->form('maingame')->debug_build->visible)
        {
            $this->version->text = "Build 11, June 4 2024";            
        }
        else 
        {
            $this->version->text = "...";
        }
    }
    
    function ResetButtonState()
    {
        $this->userdata_e_btn->textColor = 'black';
        $this->dialog_e_btn->textColor = 'black';
        $this->inv_e_btn->textColor = 'black';
        $this->mm_e_btn->textColor = 'black';
        $this->pda_e_btn->textColor = 'black';
        $this->role_e_btn->textColor = 'black';        
    }
    function ResetFragmentsVisible()
    {
        if ($this->f_DialogEditor->visible) $this->f_DialogEditor->hide();
        if ($this->f_InvEditor->visible) $this->f_InvEditor->hide();
        if ($this->f_MmEditor->visible) $this->f_MmEditor->hide();
        if ($this->f_PdaEditor->visible) $this->f_PdaEditor->hide();
        if ($this->f_UserDataEditor->visible) $this->f_UserDataEditor->hide();
        if ($this->f_RoleEditor->visible) $this->f_RoleEditor->hide();
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
     * @event pda_e_btn.click-Left 
     */
    function OpenPdaEditor(UXMouseEvent $e = null)
    {    
        $this->ResetButtonState();
        $this->pda_e_btn->textColor = 'green';
            
        $this->ResetFragmentsVisible();
        $this->f_PdaEditor->show();
    }
    /**
     * @event mm_e_btn.click-Left 
     */
    function OpenMmEditor(UXMouseEvent $e = null)
    {    
        $this->ResetButtonState();
        $this->mm_e_btn->textColor = 'green';    
    
        $this->ResetFragmentsVisible();
        $this->f_MmEditor->show();
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
     * @event start_game_btn.click-Left 
     */
    function StartMainGame(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->fragment_editor->hide();
    }
}
