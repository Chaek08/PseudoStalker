<?php
namespace app\forms;

use php\gui\UXImageView;
use php\gui\UXImage;
use php\util\LauncherClassLoader;
use php\lib\reflect;
use php\gui\framework\AbstractForm;
use php\gui\event\UXMouseEvent; 


class sdk_quest_e extends AbstractForm
{
    function ApplyAll()
    {    
        $this->ApplyQuestName();
        $this->ApplyQuestIcon();
        $this->ApplyQuestDesc();
        $this->ApplyQuestStep1();
        $this->ApplyQuestStep2();
        $this->ApplyQuestTarget();
    }
    function ResetAll()
    {    
        $this->ResetQuestName();
        $this->ResetQuestIcon();
        $this->ResetQuestDesc();
        $this->ResetQuestStep1();
        $this->ResetQuestStep2();
        $this->ResetQuestTarget();
    }
    function ClearAll()
    {    
        $this->ClearQuestName();
        $this->ClearQuestIcon();
        $this->ClearQuestDesc();
        $this->ClearQuestStep1();
        $this->ClearQuestStep2();
        $this->ClearQuestTarget();
    }
    /**
     * @event ApplyQuestName_Btn.click-Left 
     */
    function ApplyQuestName(UXMouseEvent $e = null)
    {    
        if ($this->Edit_QuestName->text == '')
        {
            $this->form('maingame')->toast('enter a quest name');
        }
        else 
        {
            $this->form('maingame')->Pda->content->Pda_Tasks->content->task_label->text = $this->Edit_QuestName->text;
        }
    }
    /**
     * @event ApplyQuestIcon_Btn.click-Left 
     */
    function ApplyQuestIcon(UXMouseEvent $e = null)
    {    
        if ($this->Edit_QuestIcon->text == '')
        {
            $this->form('maingame')->toast('enter a quest icon');
        }
        else 
        {
            $this->form('maingame')->Pda->content->Pda_Tasks->content->icon_task->image = new UXImage($this->Edit_QuestIcon->text);
        }
    }
    /**
     * @event ApplyQuestDesc_Btn.click-Left 
     */
    function ApplyQuestDesc(UXMouseEvent $e = null)
    {    
        if ($this->Edit_QuestDesc->text == '')
        {
            $this->form('maingame')->toast('enter a quest desc');
        }
        else 
        {
            $this->form('maingame')->Pda->content->Pda_Tasks->content->task_detail_text->text = $this->Edit_QuestDesc->text;
        }
    }
    /**
     * @event ApplyQuestStep1_Btn.click-Left 
     */
    function ApplyQuestStep1(UXMouseEvent $e = null)
    {    
        if ($this->Edit_QuestStep1->text == '')
        {
            $this->form('maingame')->toast('enter a quest step 1');
        }
        else 
        {
            $this->form('maingame')->Pda->content->Pda_Tasks->content->step1->text = $this->Edit_QuestStep1->text;
        }
    }
    /**
     * @event ApplyQuestStep2_Btn.click-Left 
     */
    function ApplyQuestStep2(UXMouseEvent $e = null)
    {    
        if ($this->Edit_QuestStep2->text == '')
        {
            $this->form('maingame')->toast('enter a quest step 2');
        }
        else 
        {
            $this->form('maingame')->Pda->content->Pda_Tasks->content->step2->text = $this->Edit_QuestStep2->text;
        }
    }
    /**
     * @event ApplyQuestTarget_Btn.click-Left 
     */    
    function ApplyQuestTarget(UXMouseEvent $e = null)
    {
        if ($this->Edit_QuestTarget->text == '')
        {
            $this->form('maingame')->toast('enter a quest target');
        }
        else 
        {
            $this->form('maingame')->Pda->content->Pda_Statistic->content->target_label->text = $this->Edit_QuestTarget->text;
        }
    }    
    /**
     * @event ResetQuestName_Btn.click-Left 
     */
    function ResetQuestName(UXMouseEvent $e = null)
    {    
        $this->Edit_QuestName->text = $this->Edit_QuestName->promptText;
    }
    /**
     * @event ResetQuestIcon_Btn.click-Left 
     */
    function ResetQuestIcon(UXMouseEvent $e = null)
    {    
        $this->Edit_QuestIcon->text = $this->Edit_QuestIcon->promptText;
    }
    /**
     * @event ResetQuestDesc_Btn.click-Left 
     */
    function ResetQuestDesc(UXMouseEvent $e = null)
    {
        $this->Edit_QuestDesc->text = $this->Edit_QuestDesc->promptText;
    }
    /**
     * @event ResetQuestStep1_Btn.click-Left 
     */
    function ResetQuestStep1(UXMouseEvent $e = null)
    {    
        $this->Edit_QuestStep1->text = $this->Edit_QuestStep1->promptText;
    }
    /**
     * @event ResetQuestStep2_Btn.click-Left 
     */
    function ResetQuestStep2(UXMouseEvent $e = null)
    {    
        $this->Edit_QuestStep2->text = $this->Edit_QuestStep2->promptText;
    }
    /**
     * @event ResetQuestTarget_Btn.click-Left 
     */
    function ResetQuestTarget(UXMouseEvent $e = null)
    {
        $this->Edit_QuestTarget->text = $this->Prompt_QuestTarget->text;
    }    
    /**
     * @event ClearQuestName_Btn.click-Left 
     */
    function ClearQuestName(UXMouseEvent $e = null)
    {    
        $this->Edit_QuestName->text = '';
    }
    /**
     * @event ClearQuestIcon_Btn.click-Left 
     */
    function ClearQuestIcon(UXMouseEvent $e = null)
    {    
        $this->Edit_QuestIcon->text = '';
    }
    /**
     * @event ClearQuestDesc_Btn.click-Left 
     */
    function ClearQuestDesc(UXMouseEvent $e = null)
    {    
        $this->Edit_QuestDesc->text = '';
    }
    /**
     * @event ClearQuestStep1_Btn.click-Left 
     */
    function ClearQuestStep1(UXMouseEvent $e = null)
    {    
        $this->Edit_QuestStep1->text = '';
    }
    /**
     * @event ClearQuestStep2_Btn.click-Left 
     */
    function ClearQuestStep2(UXMouseEvent $e = null)
    {    
        $this->Edit_QuestStep2->text = '';
    }
    /**
     * @event ClearQuestTarget_Btn.click-Left 
     */
    function ClearQuestTarget(UXMouseEvent $e = null)
    {
        $this->Edit_QuestTarget->text = '';
    }
    /**
     * @event ChooseQuestIcon_Btn.click-Left 
     */
    function ChooseQuestIcon(UXMouseEvent $e = null)
    {
        $this->ImageFileChooser->inputNode = $this->Edit_QuestIcon;
        if ($this->ImageFileChooser->execute())
        {
            $this->Edit_QuestIcon->text = $this->ImageFileChooser->file;
        }
    }
}
