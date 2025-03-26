<?php
namespace app\forms;

use php\gui\framework\AbstractForm;
use php\gui\event\UXMouseEvent; 
use app\forms\classes\Localization;

class sdk_fail_e extends AbstractForm
{
    private $localization;

    public function __construct() 
    {
        parent::__construct();

        $this->localization = new Localization($language);
    }
    
    function ApplyAll()
    {    
        $this->ApplyActorFailDesc();
        $this->ApplyEnemyFailDesc();
    }  
    function ResetAll()
    {
        $this->ResetActorFailDesc();
        $this->ResetActorFailEdit();
        $this->ResetActorFailIcon();
        $this->ResetEnemyFailDesc();
        $this->ResetEnemyFailEdit();
        $this->ResetEnemyFailIcon();
    }
    function ClearAll()
    {    
        $this->ClearActorFailDesc();
        $this->ClearActorFailEdit();
        $this->ClearActorFailIcon();
        $this->ClearEnemyFailDesc();
        $this->ClearEnemyFailEdit();
        $this->ClearEnemyFailIcon();
    }  
    /**
     * @event ApplyActorFailDesc_Btn.click-Left 
     */
    function ApplyActorFailDesc(UXMouseEvent $e = null)
    {    
        if ($this->Win_Fail_Desc_Actor_Edit->text == '')
        {
            $this->form('maingame')->toast('enter a (actor) win_fail_text');
        }
        else
        {
            $this->form('maingame')->Pda->content->Pda_Statistic->content->a_fail->text = $this->Win_Fail_Desc_Actor_Edit->text;
        }
    }
    /**
     * @event ApplyEnemyFailDesc_Btn.click-Left 
     */
    function ApplyEnemyFailDesc(UXMouseEvent $e = null)
    {    
        if ($this->Win_Fail_Desc_Enemy_Edit->text == '')
        {
            $this->form('maingame')->toast('enter a (enemy) win_fail_text');
        }
        else
        {
            $this->form('maingame')->Pda->content->Pda_Statistic->content->e_fail->text = $this->Win_Fail_Desc_Enemy_Edit->text;
        }
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
        $this->localization->setLanguage($this->form('maingame')->Options->content->Language_Switcher_Combobobx->value);
        $this->Win_Fail_Desc_Actor_Edit->text = $this->localization->get('ActorFail_Desc');//$this->Win_Fail_Desc_Actor_Default->text;
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
        $this->localization->setLanguage($this->form('maingame')->Options->content->Language_Switcher_Combobobx->value);
        $this->Win_Fail_Desc_Enemy_Edit->text = $this->localization->get('EnemyFail_Desc');//$this->Win_Fail_Desc_Enemy_Default->text;
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
    /**
     * @event ChooseActorFailIcon_Btn.click-Left 
     */
    function ChooseActorFailIcon(UXMouseEvent $e = null)
    {
        $this->ImageFileChooser->inputNode = $this->EditActorFailIcon;
        if ($this->ImageFileChooser->execute())
        {
            $this->EditActorFailIcon->text = $this->ImageFileChooser->file;
        }
    }
    /**
     * @event ChooseEnemyFailIcon_Btn.click-Left 
     */
    function ChooseEnemyFailIcon(UXMouseEvent $e = null)
    {
        $this->ImageFileChooser->inputNode = $this->EditEnemyFailIcon;
        if ($this->ImageFileChooser->execute())
        {
            $this->EditEnemyFailIcon->text = $this->ImageFileChooser->file;
        }
    }
}
