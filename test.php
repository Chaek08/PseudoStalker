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

    /**
     * @event button_drop.click-Left 
     */
    function doButton_dropClickLeft(UXMouseEvent $e = null)
    {
        $this->DropSound();  
        $this->HideCombobox(); 
        $this->SpawnVodka();          
        if ($this->inv_maket_select_2->visible)
        {
            $this->HideVodkaMaket();
            $this->HideUIText();
            if ($this->inv_maket_select->visible)
            {
                $this->inv_maket_select->hide();
            }
        }  
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
