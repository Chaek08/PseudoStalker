<?php
namespace app\forms;

use php\gui\framework\AbstractForm;
use php\gui\event\UXWindowEvent; 
use app\forms\classes\Localization;

class MessageBox extends AbstractForm
{
    public function __construct()
    {
        parent::__construct();

        $this->InitMessageBox();       
    }
     
    function InitMessageBox()
    {
        $GLOBALS['Task_Status_Update'] = false;
        $GLOBALS['Task_Status_Failed'] = false;
    }
    
    function UpdateMessageBox()
    {
        $this->form('Client')->Pda->content->Pda_Tasks->content->UpdateData();
        
        if ($GLOBALS['Task_Status_Update'])
        {
            $this->Task_Status->text = Localization::get('Task_Status_Update');
        }
        if ($GLOBALS['Task_Status_Failed'])
        {
            $this->Task_Status->text = Localization::get('Task_Status_Failed');
        }
        
        $this->InitMessageBox();
    }
}
