<?php
namespace app\forms;

use php\gui\framework\AbstractForm;
use php\gui\event\UXMouseEvent; 


class sdk_fail_e extends AbstractForm
{
    /**
     * @event ResetAll_Btn.click-Left 
     */
    function ResetAll(UXMouseEvent $e = null)
    {
        $this->ResetActorFailDesc();
        $this->ResetActorFailEdit();
        $this->ResetActorFailIcon();
        $this->ResetEnemyFailDesc();
        $this->ResetEnemyFailEdit();
        $this->ResetEnemyFailIcon();
    }
    /**
     * @event ClearAll_Btn.click-Left 
     */
    function ClearAll(UXMouseEvent $e = null)
    {    
        $this->ClearActorFailDesc();
        $this->ClearActorFailEdit();
        $this->ClearActorFailIcon();
        $this->ClearEnemyFailDesc();
        $this->ClearEnemyFailEdit();
        $this->ClearEnemyFailIcon();        
    }    
    /**
     * @event ResetActorFailEdit_Btn.click-Left 
     */
    function ResetActorFailEdit(UXMouseEvent $e = null)
    {    
        $this->Win_Fail_Text_Actor_Edit->text = $this->Win_Fail_Text_Actor_Edit->promptText;
    }
    /**
     * @event ResetActorFailDesc_Btn.click-Left 
     */
    function ResetActorFailDesc(UXMouseEvent $e = null)
    {    
        $this->Win_Fail_Desc_Actor_Edit->text = $this->Win_Fail_Desc_Actor_Edit->promptText;
    }
    /**
     * @event ResetEnemyFailEdit_Btn.click-Left 
     */
    function ResetEnemyFailEdit(UXMouseEvent $e = null)
    {    
        $this->Win_Fail_Text_Enemy_Edit->text = $this->Win_Fail_Text_Enemy_Edit->promptText;
    }
    /**
     * @event ResetEnemyFailDesc_Btn.click-Left 
     */
    function ResetEnemyFailDesc(UXMouseEvent $e = null)
    {    
        $this->Win_Fail_Desc_Enemy_Edit->text = $this->Win_Fail_Desc_Enemy_Edit->promptText;
    }
    /**
     * @event ResetActorFailIcon_Btn.click-Left 
     */
    function ResetActorFailIcon(UXMouseEvent $e = null)
    {    
        $this->EditActorFailIcon->text = $this->EditActorFailIcon->promptText;
    }
    /**
     * @event ResetEnemyFailIcon_Btn.click-Left 
     */
    function ResetEnemyFailIcon(UXMouseEvent $e = null)
    {    
        $this->EditEnemyFailIcon->text = $this->EditEnemyFailIcon->promptText;
    }
    /**
     * @event ClearActorFailIcon_Btn.click-Left 
     */
    function ClearActorFailIcon(UXMouseEvent $e = null)
    {    
        $this->EditActorFailIcon->text = '';
    }
    /**
     * @event ClearEnemyFailIcon_Btn.click-Left 
     */
    function ClearEnemyFailIcon(UXMouseEvent $e = null)
    {    
        $this->EditEnemyFailIcon->text = '';
    }
    /**
     * @event ClearActorFailEdit_Btn.click-Left 
     */
    function ClearActorFailEdit(UXMouseEvent $e = null)
    {    
        $this->Win_Fail_Text_Actor_Edit->text = '';
    }
    /**
     * @event ClearActorFailDesc_Btn.click-Left 
     */
    function ClearActorFailDesc(UXMouseEvent $e = null)
    {    
        $this->Win_Fail_Desc_Actor_Edit->text = '';
    }
    /**
     * @event ClearEnemyFailEdit_Btn.click-Left 
     */
    function ClearEnemyFailEdit(UXMouseEvent $e = null)
    {    
        $this->Win_Fail_Text_Enemy_Edit->text = '';
    }
    /**
     * @event ClearEnemyFailDesc_Btn.click-Left 
     */
    function ClearEnemyFailDesc(UXMouseEvent $e = null)
    {    
        $this->Win_Fail_Desc_Enemy_Edit->text = '';
    }
}
