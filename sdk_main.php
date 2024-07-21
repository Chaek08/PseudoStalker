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
            $this->pseudosdk_label->tooltipText = "Build 59, Jule 22 2024"; //start date: 24 may 2024
        }
        else 
        {
            $this->pseudosdk_label->tooltipText = "v0.0";
        }
    }
    function SdkStatus()
    {
        $this->status_label->text = 'Main';
        
        if ($this->f_Background->visible)
        {
            $this->status_label->hide();
            $this->pseudosdk_label->y = 8;
        }
        else
        {
            $this->status_label->show();
            $this->pseudosdk_label->y = 0;
        }
    }   
    /**
     * @event SDK_Icon.click-left
     */
    function DefaultSdkState(UXMouseEvent $e = null)
    {         
        $this->SdkStatus();
        $this->ResetFragmentsVisible();
    }
    function ResetFragmentsVisible()
    {
        if ($this->f_DialogEditor->visible) $this->f_DialogEditor->hide();
        if ($this->f_InvEditor->visible) $this->f_InvEditor->hide();
        if ($this->f_MgEditor->visible) $this->f_MgEditor->hide();
        if ($this->f_UserDataEditor->visible) $this->f_UserDataEditor->hide();
        if ($this->f_RoleEditor->visible) $this->f_RoleEditor->hide();
        if ($this->f_FailEditor->visible) $this->f_FailEditor->hide();
        
        $this->f_Background->show();
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
        $this->f_Background->hide();
           
        $this->SdkStatus();
        $this->status_label->text = $this->userdata_e_btn->text;
        
        $this->ResetFragmentsVisible();
        $this->f_UserDataEditor->show();
    }
    /**
     * @event dialog_e_btn.click-Left 
     */
    function OpenDialogEditor(UXMouseEvent $e = null)
    {  
        $this->f_Background->hide();
             
        $this->SdkStatus();
        $this->status_label->text = $this->dialog_e_btn->text;
    
        $this->ResetFragmentsVisible();
        $this->f_DialogEditor->show();
    }
    /**
     * @event inv_e_btn.click-Left 
     */
    function OpenInvEditor(UXMouseEvent $e = null)
    {      
        $this->f_Background->hide();
          
        $this->SdkStatus();
        $this->status_label->text = $this->inv_e_btn->text;
            
        $this->ResetFragmentsVisible();
        $this->f_InvEditor->show();
    }
    /**
     * @event mg_e_btn.click-Left 
     */
    function OpenMgEditor(UXMouseEvent $e = null)
    {    
        $this->f_Background->hide();
         
        $this->SdkStatus();
        $this->status_label->text = $this->mg_e_btn->text;
    
        $this->ResetFragmentsVisible();
        $this->f_MgEditor->show();
    }
    /**
     * @event role_e_btn.click-Left 
     */
    function OpenRoleEditor(UXMouseEvent $e = null)
    {  
        $this->f_Background->hide();
         
        $this->SdkStatus();
        $this->status_label->text = $this->role_e_btn->text;
        
        $this->ResetFragmentsVisible();
        $this->f_RoleEditor->show();
    }    
    /**
     * @event fail_e_btn.click-Left 
     */
    function OpenFailEditor(UXMouseEvent $e = null)
    { 
        $this->f_Background->hide();
          
        $this->SdkStatus();
        $this->status_label->text = $this->fail_e_btn->text;
        
        $this->ResetFragmentsVisible();
        $this->f_FailEditor->show();
    }   
}
