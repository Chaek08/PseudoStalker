<?php
namespace app\forms;

use php\gui\framework\AbstractForm;
use php\gui\event\UXMouseEvent; 


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
        $this->ApplyAlexDesc1();
    }
    /**
     * @event ResetActor_Desc_1_Btn.click-Left 
     */
    function ResetActorDesc1(UXMouseEvent $e = null)
    {    
        $this->Edit_Actor_Desc_1->text = $this->Edit_Actor_Desc_1->promptText;
        $this->ApplyActorDesc1();
    }
    /**
     * @event ResetAlex_Desc_2_Btn.click-Left 
     */
    function ResetAlexDesc2(UXMouseEvent $e = null)
    {    
        $this->Edit_Alex_Desc_2->text = $this->Edit_Alex_Desc_2->promptText;
        $this->ApplyAlexDesc2();        
    }
    /**
     * @event ResetActor_Desc_3_Btn.click-Left 
     */
    function ResetActorDesc3(UXMouseEvent $e = null)
    {    
        $this->Edit_Actor_Desc_3->text = $this->Edit_Actor_Desc_3->promptText;
        $this->ApplyActorDesc3();        
    }
    /**
     * @event ResetAlex_Desc_3_Btn.click-Left 
     */
    function ResetAlexDesc3(UXMouseEvent $e = null)
    {    
        $this->Edit_Alex_Desc_3->text = $this->Edit_Alex_Desc_3->promptText;
        $this->ApplyAlexDesc3();        
    }
}
