<?php
namespace app\forms;

use action\Media;
use php\gui\UXImageView;
use php\gui\UXImage;
use php\gui\framework\AbstractForm;
use php\gui\event\UXMouseEvent; 
use php\gui\event\UXWindowEvent; 


class sdk_main extends AbstractForm
{
    function GetSDKVersion()
    {
    /*
        if ($this->form('maingame')->debug_build->visible)
        {
            $this->pseudosdk_label->tooltipText = "Build 77, August 8 2024"; //start date: 24 may 2024
        }
        else 
        {
            $this->pseudosdk_label->tooltipText= "v1.0";
        }
     */  
    }
    function SDKStatus()
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
    function StopSDKSounds()
    {
        if (!$this->f_DialogEditor->visible || !$this->form('maingame')->Editor->visible)
        {
            if (Media::isStatus('PLAYING', 'PreviewVoiceStart')) Media::stop('PreviewVoiceStart');
            if (Media::isStatus('PLAYING', 'PreviewVoiceTalk1')) Media::stop('PreviewVoiceTalk1');
            if (Media::isStatus('PLAYING', 'PreviewVoiceTalk2')) Media::stop('PreviewVoiceTalk2');
            if (Media::isStatus('PLAYING', 'PreviewVoiceTalk3')) Media::stop('PreviewVoiceTalk3');
        }
        
        if (!$this->f_MgEditor->visible || !$this->form('maingame')->Editor->visible)
        {
            if (Media::isStatus('PLAYING', 'PreviewFightSound')) Media::stop('PreviewFightSound');
        }  
    }                                                                                                                             
    /**
     * @event SDK_Icon.click-left
     */
    function DefaultSDKState(UXMouseEvent $e = null)
    {         
        $this->ResetTabs();
        $this->SDKStatus();
    }
    function ResetTabs()
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
        $this->form('maingame')->Editor->hide();
        $this->form('maingame')->ForwardSDK_Btn->hide();
        
        $this->form('maingame')->MainMenu->content->opensdk_btn->enabled = true;
        $this->form('maingame')->MainMenu->content->opensdk_btn->text = 'Open SDK';
        
        $this->DefaultSDKState();
        
        $this->StopSDKSounds();
        if (Media::isStatus('PAUSED', 'menu_sound') && $this->form('maingame')->MainMenu->visible) Media::play("menu_sound");
    }      
    /**
     * @event userdata_e_btn.click-Left 
     */
    function OpenUserDataEditor(UXMouseEvent $e = null)
    {    
        $this->DefaultSDKState();
        
        $this->StopSDKSounds();
        
        $this->f_Background->hide();
        $this->f_UserDataEditor->show();
                
        $this->SDKStatus();
        $this->status_label->text = $this->userdata_e_btn->text;
    }
    /**
     * @event dialog_e_btn.click-Left 
     */
    function OpenDialogEditor(UXMouseEvent $e = null)
    {  
        $this->DefaultSDKState();
        
        $this->StopSDKSounds();

        $this->f_Background->hide();
        $this->f_DialogEditor->show();
        
        $this->SDKStatus();        
        $this->status_label->text = $this->dialog_e_btn->text;
    }
    /**
     * @event inv_e_btn.click-Left 
     */
    function OpenInvEditor(UXMouseEvent $e = null)
    {
        $this->DefaultSDKState();
        
        $this->StopSDKSounds();
           
        $this->f_Background->hide();
        $this->f_InvEditor->show();
        
        $this->SDKStatus();
        $this->status_label->text = $this->inv_e_btn->text;
    }
    /**
     * @event mg_e_btn.click-Left 
     */
    function OpenMgEditor(UXMouseEvent $e = null)
    {    
        $this->DefaultSDKState();
        
        $this->StopSDKSounds();
        
        $this->f_Background->hide();
        $this->f_MgEditor->show();
        
        $this->SDKStatus();
        $this->status_label->text = $this->mg_e_btn->text;
    }
    /**
     * @event role_e_btn.click-Left 
     */
    function OpenRoleEditor(UXMouseEvent $e = null)
    {  
        $this->DefaultSDKState();
        
        $this->StopSDKSounds();
        
        $this->f_Background->hide();
        $this->f_RoleEditor->show();
        
        $this->SDKStatus();
        $this->status_label->text = $this->role_e_btn->text;
    }    
    /**
     * @event fail_e_btn.click-Left 
     */
    function OpenFailEditor(UXMouseEvent $e = null)
    { 
        $this->DefaultSDKState();
        
        $this->StopSDKSounds();
        
        $this->f_Background->hide();
        $this->f_FailEditor->show();
        
        $this->SDKStatus();        
        $this->status_label->text = $this->fail_e_btn->text;         
    }   
    /**
     * @event quest_e_btn.click-Left 
     */
    function OpenQuestEditor(UXMouseEvent $e = null)
    {
        $this->DefaultSDKState();
        
        $this->StopSDKSounds();
        
        $this->f_Background->hide();
        $this->f_QuestEditor->show();
        
        $this->SDKStatus();
        $this->status_label->text = $this->quest_e_btn->text;
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
    /**
     * @event MinimizeSDK_Btn.click-Left 
     */
    function MinimizeSDK(UXMouseEvent $e = null)
    {
        $this->form('maingame')->Editor->hide();
        
        $this->form('maingame')->ForwardSDK_Btn->show();
        
        $this->form('maingame')->MainMenu->content->opensdk_btn->enabled = false;
        $this->form('maingame')->MainMenu->content->opensdk_btn->text = 'Opened';
        
        if (Media::isStatus('PAUSED', 'menu_sound') && $this->form('maingame')->MainMenu->visible) Media::play("menu_sound");
    }
}
