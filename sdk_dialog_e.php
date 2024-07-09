<?php
namespace app\forms;

use php\gui\framework\AbstractForm;
use php\gui\event\UXMouseEvent; 
use php\gui\event\UXEvent; 


class sdk_dialog_e extends AbstractForm
{
    /**
     * @event ApplyAll_Btn.click-Left 
     */
    function ApplyAll(UXMouseEvent $e = null)
    {    
        $this->ApplyActorDesc1();
        $this->ApplyActorDesc3();
        $this->ApplyAlexDesc1();
        $this->ApplyAlexDesc2();
        $this->ApplyAlexDesc3();
    }
    /**
     * @event ResetAll_Btn.click-Left 
     */
    function ResetAll(UXMouseEvent $e = null)
    {    
        $this->ResetActorDesc1();
        $this->ResetActorDesc3();
        $this->ResetAlexDesc1();
        $this->ResetAlexDesc2();
        $this->ResetAlexDesc3();
        $this->ResetFinalPhase();
        $this->ResetVoiceStart();
        $this->ResetVoiceTalk1();
        $this->ResetVoiceTalk2();
        $this->ResetVoiceTalk3();
    }
    /**
     * @event ClearAll_Btn.click-Left 
     */
    function ClearAll(UXMouseEvent $e = null)
    {    
        $this->ClearActor_Desc_1();
        $this->ClearActor_Desc_3();
        $this->ClearAlex_Desc_1();
        $this->ClearAlex_Desc_2();
        $this->ClearAlex_Desc_3();
        $this->ClearFinalPhase();
        $this->ClearVoiceStart();
        $this->ClearVoiceTalk1();
        $this->ClearVoiceTalk2();
        $this->ClearVoiceTalk3();
    }       
    /**
     * @event ApplyAlex_Desc_1_Btn.click-Left 
     */
    function ApplyAlexDesc1(UXMouseEvent $e = null)
    {    
        if ($this->Edit_Alex_Desc_1->text == '')
        {
            $this->form('sdk_main')->toast("enter dialog text");
        }
        else
        {
            $this->form('maingame')->fragment_dlg->content->alex_desc_1->text = $this->Edit_Alex_Desc_1->text;            
        }
    }
    /**
     * @event ApplyActor_Desc_1_Btn.click-Left 
     */
    function ApplyActorDesc1(UXMouseEvent $e = null)
    {    
        if ($this->Edit_Actor_Desc_1->text == '')
        {
            $this->form('sdk_main')->toast("enter dialog text");
        }
        else    
        {
            $this->form('maingame')->fragment_dlg->content->actor_desc_1->text = $this->Edit_Actor_Desc_1->text;            
        }
    }
    /**
     * @event ApplyAlex_Desc_2_Btn.click-Left 
     */
    function ApplyAlexDesc2(UXMouseEvent $e = null)
    {  
        if ($this->Edit_Alex_Desc_2->text == '')
        {
            $this->form('sdk_main')->toast("enter dialog text");
        }
        else  
        {
            $this->form('maingame')->fragment_dlg->content->alex_desc_2->text = $this->Edit_Alex_Desc_2->text;            
        }    
    }
    /**
     * @event ApplyActor_Desc_3_Btn.click-Left 
     */
    function ApplyActorDesc3(UXMouseEvent $e = null)
    {    
        if ($this->Edit_Actor_Desc_3->text == '')
        {
            $this->form('sdk_main')->toast("enter dialog text");
        }
        else    
        {
            $this->form('maingame')->fragment_dlg->content->actor_desc_3->text = $this->Edit_Actor_Desc_3->text;            
        }       
    }
    /**
     * @event ApplyAlex_Desc_3_Btn.click-Left 
     */
    function ApplyAlexDesc3(UXMouseEvent $e = null)
    {    
        if ($this->Edit_Alex_Desc_3->text == '')
        {
            $this->form('sdk_main')->toast("enter dialog text");
        }
        else  
        {
            $this->form('maingame')->fragment_dlg->content->alex_desc_3->text = $this->Edit_Alex_Desc_3->text;            
        }  
    }
    /**
     * @event ResetAlex_Desc_1_Btn.click-Left 
     */
    function ResetAlexDesc1(UXMouseEvent $e = null)
    {    
        $this->Edit_Alex_Desc_1->text = $this->Edit_Alex_Desc_1->promptText;
    }
    /**
     * @event ResetActor_Desc_1_Btn.click-Left 
     */
    function ResetActorDesc1(UXMouseEvent $e = null)
    {    
        $this->Edit_Actor_Desc_1->text = $this->Edit_Actor_Desc_1->promptText;
    }
    /**
     * @event ResetAlex_Desc_2_Btn.click-Left 
     */
    function ResetAlexDesc2(UXMouseEvent $e = null)
    {    
        $this->Edit_Alex_Desc_2->text = $this->Edit_Alex_Desc_2->promptText;
    }
    /**
     * @event ResetActor_Desc_3_Btn.click-Left 
     */
    function ResetActorDesc3(UXMouseEvent $e = null)
    {    
        $this->Edit_Actor_Desc_3->text = $this->Edit_Actor_Desc_3->promptText;
    }
    /**
     * @event ResetAlex_Desc_3_Btn.click-Left 
     */
    function ResetAlexDesc3(UXMouseEvent $e = null)
    {    
        $this->Edit_Alex_Desc_3->text = $this->Edit_Alex_Desc_3->promptText;  
    }
    /**
     * @event ResetVoiceStart_Btn.click-Left 
     */
    function ResetVoiceStart(UXMouseEvent $e = null)
    {    
        $this->Edit_VoiceStart->text = $this->Edit_VoiceStart->promptText;
    }
    /**
     * @event ResetVoiceTalk1_Btn.click-Left 
     */
    function ResetVoiceTalk1(UXMouseEvent $e = null)
    {    
        $this->Edit_VoiceTalk1->text = $this->Edit_VoiceTalk1->promptText;
    }
    /**
     * @event ResetVoiceTalk2_Btn.click-Left 
     */
    function ResetVoiceTalk2(UXMouseEvent $e = null)
    {    
        $this->Edit_VoiceTalk2->text = $this->Edit_VoiceTalk2->promptText;        
    }
    /**
     * @event ResetVoiceTalk3_Btn.click-Left 
     */
    function ResetVoiceTalk3(UXMouseEvent $e = null)
    {    
        $this->Edit_VoiceTalk3->text = $this->Edit_VoiceTalk3->promptText;        
    }
    /**
     * @event ResetFinalPhase_Btn.click-Left 
     */
    function ResetFinalPhase(UXMouseEvent $e = null)
    {    
        $this->Edit_Final_Phase->text = $this->Edit_Final_Phase->promptText;
    }
    /**
     * @event ClearAlex_Desc_1_Btn.click-Left 
     */
    function ClearAlex_Desc_1(UXMouseEvent $e = null)
    {    
        $this->Edit_Alex_Desc_1->text = '';
    }
    /**
     * @event ClearActor_Desc_1_Btn.click-Left 
     */
    function ClearActor_Desc_1(UXMouseEvent $e = null)
    {    
        $this->Edit_Actor_Desc_1->text = '';
    }
    /**
     * @event ClearAlex_Desc_2_Btn.click-Left 
     */
    function ClearAlex_Desc_2(UXMouseEvent $e = null)
    {    
        $this->Edit_Alex_Desc_2->text = '';
    }
    /**
     * @event ClearActor_Desc_3_Btn.click-Left 
     */
    function ClearActor_Desc_3(UXMouseEvent $e = null)
    {    
        $this->Edit_Actor_Desc_3->text = '';
    }
    /**
     * @event ClearAlex_Desc_3_Btn.click-Left 
     */
    function ClearAlex_Desc_3(UXMouseEvent $e = null)
    {    
        $this->Edit_Alex_Desc_3->text = '';
    }
    /**
     * @event ClearFinalPhase_Btn.click-Left 
     */
    function ClearFinalPhase(UXMouseEvent $e = null)
    {    
        $this->Edit_Final_Phase->text = '';
    }
    /**
     * @event ClearVoiceStart_Btn.click-Left 
     */
    function ClearVoiceStart(UXMouseEvent $e = null)
    {    
        $this->Edit_VoiceStart->text = '';
    }
    /**
     * @event ClearVoiceTalk1_Btn.click-Left 
     */
    function ClearVoiceTalk1(UXMouseEvent $e = null)
    {    
        $this->Edit_VoiceTalk1->text = '';
    }
    /**
     * @event ClearVoiceTalk2_Btn.click-Left 
     */
    function ClearVoiceTalk2(UXMouseEvent $e = null)
    {    
        $this->Edit_VoiceTalk2->text = '';
    }
    /**
     * @event ClearVoiceTalk3_Btn.click-Left 
     */
    function ClearVoiceTalk3(UXMouseEvent $e = null)
    {    
        $this->Edit_VoiceTalk3->text = '';
    }
}
