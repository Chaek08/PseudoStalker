<?php
namespace app\forms;

use php\gui\UXImageView;
use php\gui\UXImage;
use php\gui\framework\AbstractForm;
use php\gui\event\UXMouseEvent; 
use php\gui\event\UXWindowEvent; 


class sdk_main extends AbstractForm
{
    function GetSDKVersion()
    {
        if ($this->form('maingame')->debug_build->visible)
        {
            $this->pseudosdk_label->tooltipText = "Build 77, August 8 2024"; //start date: 24 may 2024
        }
        else 
        {
            $this->pseudosdk_label->tooltipText= "v1.0";
        }
    }
    function SdkStatus()
    {
        if ($this->f_Background->visible)
        {
            $this->GetSDKVersion();
            
            $this->status_label->hide();
            $this->pseudosdk_label->y = 8;
            
            $this->Separator->hide();
            
            $this->Apply_All_Btn->hide();
            $this->Reset_All_Btn->hide();
            $this->Clear_All_Btn->hide();
        }
        else 
        {
            $this->Separator->show();
            
            $this->status_label->show();
            $this->pseudosdk_label->y = 0;
          
            $this->Apply_All_Btn->show();
            $this->Reset_All_Btn->show();
            $this->Clear_All_Btn->show();
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
        if ($this->f_QuestEditor->visible) $this->f_QuestEditor->hide();
                
        $this->f_Background->show();
    }
    /**
     * @event start_game_btn.click-Left 
     */
    function StartMainGame(UXMouseEvent $e = null)
    {
        $this->form('maingame')->fragment_editor->hide();
        $this->DefaultSdkState();
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
    /**
     * @event quest_e_btn.click-Left 
     */
    function OpenQuestEditor(UXMouseEvent $e = null)
    {
        $this->f_Background->hide();
          
        $this->SdkStatus();
        $this->status_label->text = $this->quest_e_btn->text;
        
        $this->ResetFragmentsVisible();
        $this->f_QuestEditor->show();
    }
    /**
     * @event Apply_All_Btn.click-Left 
     */
    function ApplyAllChanges(UXMouseEvent $e = null)
    {
        if ($this->f_UserDataEditor->visible) $this->f_UserDataEditor->content->ApplyAll();
        if ($this->f_DialogEditor->visible) $this->f_DialogEditor->content->ApplyAll();
        if ($this->f_RoleEditor->visible) $this->f_RoleEditor->content->ApplyAll();
        if ($this->f_InvEditor->visible) $this->f_InvEditor->content->ApplyAll();
        if ($this->f_MgEditor->visible) $this->f_MgEditor->content->ApplyAll();
        if ($this->f_FailEditor->visible) $this->f_FailEditor->content->ApplyAll();
        if ($this->f_QuestEditor->visible) $this->f_QuestEditor->content->ApplyAll();
    }
    /**
     * @event Reset_All_Btn.click-Left 
     */
    function ResetAllChanges(UXMouseEvent $e = null)
    {
        if ($this->f_UserDataEditor->visible) $this->f_UserDataEditor->content->ResetAll();
        if ($this->f_DialogEditor->visible) $this->f_DialogEditor->content->ResetAll();
        if ($this->f_RoleEditor->visible) $this->f_RoleEditor->content->ResetAll();
        if ($this->f_InvEditor->visible) $this->f_InvEditor->content->ResetAll();
        if ($this->f_MgEditor->visible) $this->f_MgEditor->content->ResetAll();
        if ($this->f_FailEditor->visible) $this->f_FailEditor->content->ResetAll();
        if ($this->f_QuestEditor->visible) $this->f_QuestEditor->content->ResetAll();       
    }
    /**
     * @event Clear_All_Btn.click-Left 
     */
    function ClearAllChanges(UXMouseEvent $e = null)
    {
        if ($this->f_UserDataEditor->visible) $this->f_UserDataEditor->content->ClearAll();
        if ($this->f_DialogEditor->visible) $this->f_DialogEditor->content->ClearAll();
        if ($this->f_RoleEditor->visible) $this->f_RoleEditor->content->ClearAll();
        if ($this->f_InvEditor->visible) $this->f_InvEditor->content->ClearAll();
        if ($this->f_MgEditor->visible) $this->f_MgEditor->content->ClearAll();
        if ($this->f_FailEditor->visible) $this->f_FailEditor->content->ClearAll();
        if ($this->f_QuestEditor->visible) $this->f_QuestEditor->content->ClearAll();
    }
}
