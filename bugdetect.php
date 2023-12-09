<?php
namespace app\forms;

use std, gui, framework, app;


class bugdetect extends AbstractForm
{

    /**
     * @event button.click-Left 
     */
    function exit_btn(UXMouseEvent $e = null)
    {    
        app()->shutdown();
    }

}
