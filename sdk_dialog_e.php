<?php
namespace app\forms;

use action\Media;
use php\gui\framework\AbstractForm;
use php\gui\event\UXMouseEvent; 
use php\gui\event\UXEvent; 


class sdk_dialog_e extends AbstractForm
{
    function ApplyAll()
    {    
        $this->ApplyActorDesc1();
        $this->ApplyActorDesc3();
        $this->ApplyAlexDesc1();
        $this->ApplyAlexDesc2();
        $this->ApplyAlexDesc3();
    }
    function ResetAll()
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
    function ClearAll()
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
            $this->form('maingame')->Dialog->content->alex_desc_1->text = $this->Edit_Alex_Desc_1->text;            
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
            $this->form('maingame')->Dialog->content->actor_desc_1->text = $this->Edit_Actor_Desc_1->text;            
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
            $this->form('maingame')->Dialog->content->alex_desc_2->text = $this->Edit_Alex_Desc_2->text;            
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
            $this->form('maingame')->Dialog->content->actor_desc_3->text = $this->Edit_Actor_Desc_3->text;            
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
            $this->form('maingame')->Dialog->content->alex_desc_3->text = $this->Edit_Alex_Desc_3->text;            
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
    function ResetVoice()
    {
        Media::stop('PreviewVoiceStart');
        Media::stop('PreviewVoiceTalk1');
        Media::stop('PreviewVoiceTalk2');
        Media::stop('PreviewVoiceTalk3');                        
    }
    /**
     * @event VoiceStart_PreviewBtn.click-Left 
     */
    function PreviewVoiceStart(UXMouseEvent $e = null)
    {    
        if ($this->form('maingame')->Options->content->All_Sounds->visible)
        {
            $this->ResetVoice();        
            Media::open($this->Edit_VoiceStart->text, true, 'PreviewVoiceStart');
        }
    }
    /**
     * @event VoiceStart_PreviewBtn.click-Right 
     */
    function StopPreviewVoiceStart(UXMouseEvent $e = null)
    {    
        $this->ResetVoice();
    }    
    /**
     * @event VoiceTalk1_PreviewBtn.click-Left 
     */
    function PreviewVoiceTalk1(UXMouseEvent $e = null)
    {
        if ($this->form('maingame')->Options->content->All_Sounds->visible)
        {
            $this->ResetVoice();        
            Media::open($this->Edit_VoiceTalk1->text, true, 'PreviewVoiceTalk1');
        }
    }
    /**
     * @event VoiceTalk1_PreviewBtn.click-Right 
     */
    function StopPreviewVoiceTalk1(UXMouseEvent $e = null)
    {    
        $this->ResetVoice();
    }     
    /**
     * @event VoiceTalk2_PreviewBtn.click-Left 
     */
    function PreviewVoiceTalk2(UXMouseEvent $e = null)
    {
        if ($this->form('maingame')->Options->content->All_Sounds->visible)
        {
            $this->ResetVoice();
            Media::open($this->Edit_VoiceTalk2->text, true, 'PreviewVoiceTalk2');
        }
    }
    /**
     * @event VoiceTalk2_PreviewBtn.click-Right 
     */
    function StopPreviewVoiceTalk2(UXMouseEvent $e = null)
    {    
        $this->ResetVoice();
    }    
    /**
     * @event VoiceTalk3_PreviewBtn.click-Left 
     */
    function PreviewVoiceTalk3(UXMouseEvent $e = null)
    {
        if ($this->form('maingame')->Options->content->All_Sounds->visible)
        {
            $this->ResetVoice();        
            Media::open($this->Edit_VoiceTalk3->text, true, 'PreviewVoiceTalk3');
        }
    }
    /**
     * @event VoiceTalk3_PreviewBtn.click-Right 
     */
    function StopPreviewVoiceTalk3(UXMouseEvent $e = null)
    {    
        $this->ResetVoice();
    }    
    /**
     * @event ChooseVoiceStart_Btn.click-Left 
     */
    function ChooseVoiceStart(UXMouseEvent $e = null)
    {
        $this->FileChooser->inputNode = $this->Edit_VoiceStart;
        if ($this->FileChooser->execute())
        {
            $this->Edit_VoiceStart->text = $this->FileChooser->file;
            
            $this->StopPreviewVoiceStart();
        }
    }
    /**
     * @event ChooseVoiceTalk1_Btn.click-Left 
     */
    function ChooseVoiceTalk1(UXMouseEvent $e = null)
    {
        $this->FileChooser->inputNode = $this->Edit_VoiceTalk1;
        if ($this->FileChooser->execute())
        {
            $this->Edit_VoiceTalk1->text = $this->FileChooser->file;
            
            $this->StopPreviewVoiceTalk1();
        }
    }
    /**
     * @event ChooseVoiceTalk2_Btn.click-Left 
     */
    function ChooseVoiceTalk2(UXMouseEvent $e = null)
    {
        $this->FileChooser->inputNode = $this->Edit_VoiceTalk2;
        if ($this->FileChooser->execute())
        {
            $this->Edit_VoiceTalk2->text = $this->FileChooser->file;
            
            $this->StopPreviewVoiceTalk2();
        }
    }
    /**
     * @event ChooseVoiceTalk3_Btn.click-Left 
     */
    function ChooseVoiceTalk3(UXMouseEvent $e = null)
    {
        $this->FileChooser->inputNode = $this->Edit_VoiceTalk3;
        if ($this->FileChooser->execute())
        {
            $this->Edit_VoiceTalk3->text = $this->FileChooser->file;
            
            $this->StopPreviewVoiceTalk3();
        }
    }
}
