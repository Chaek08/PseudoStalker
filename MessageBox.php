<?php
namespace app\forms;

use app\forms\classes\Localization;
use php\gui\framework\AbstractForm;
use php\gui\event\UXWindowEvent; 


class MessageBox extends AbstractForm
{
    public function __construct()
    {
        parent::__construct();

        $this->localization = new Localization($language);
    }
    /**
     * @event show 
     */    
    function InitMessageBox()
    {
        $GLOBALS['Task_Status_Update'] = false;
        $GLOBALS['Task_Status_Failed'] = false;
    }
    
    function UpdateMessageBox()
    {
        $this->localization->setLanguage($this->form('maingame')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value); 
        
        $this->form('maingame')->Pda->content->Pda_Tasks->content->UpdateData();   
    
        if ($GLOBALS['Task_Status_Update'])
        {
            $this->Task_Status->text = $this->localization->get('Task_Status_Update');
        }
        if ($GLOBALS['Task_Status_Failed'])
        {
            $this->Task_Status->text = $this->localization->get('Task_Status_Failed');
        }
        
        $this->InitMessageBox();
    }
}
