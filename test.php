<?php
namespace app\forms;

use std, gui, framework, app;


class test extends AbstractForm
{

    /**
     * @event button.click-Left 
     */
    function MoveForward(UXMouseEvent $e = null)
    {    
        $this->Player->x += 5;
        $this->AttachmentSystem();
    }
    /**
     * @event buttonAlt.click-Left 
     */
    function MoveBack(UXMouseEvent $e = null)
    {    
        $this->Player->x -= 5;
        $this->AttachmentSystem();        
    }
    function AttachmentSystem()
    {
        if($this->Player->x < 232)
        {
            $this->Weapon->x = $this->Player->x - 8;              
        }
        if ($this->Player->x > 232)
        {
           $this->Weapon->x = $this->Player->x + 8;              
        }
    }

}
