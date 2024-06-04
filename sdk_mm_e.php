<?php
namespace app\forms;

use php\gui\paint\UXColor;
use php\gui\UXImage;
use php\gui\framework\AbstractForm;
use php\gui\event\UXMouseEvent; 


class sdk_mm_e extends AbstractForm
{
    /**
     * @event ApplyBackground_Btn.click-Left 
     */
    function ApplyBackground(UXMouseEvent $e = null)
    {    
        if ($this->Edit_Background->text == '')
        {
            $this->form('sdk_main')->toast("enter background url");
        }
        else 
        {
            $this->form('maingame')->platform->image = new UXImage($this->Edit_Background->text);            
        }
    }
    /**
     * @event ResetBackground_Btn.click-Left 
     */
    function ResetBackground(UXMouseEvent $e = null)
    {    
        $this->Edit_Background->text = $this->Edit_Background->promptText;
        $this->ApplyBackground();
    }
    /**
     * @event ClearEdit_Btn.click-Left 
     */
    function ClearEdit(UXMouseEvent $e = null)
    {    
        $this->Edit_Background->text = '';
    }    
    /**
     * @event BackgroundMosaicOn_Btn.click-Left 
     */
    function BackgroundMosaicOn(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->platform->mosaic = true;
    }
    /**
     * @event BackgroundMosaicOff_Btn.click-Left 
     */
    function BackgroundMosaicOff(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->platform->mosaic = false;        
    }
    /**
     * @event BackgroundStretchOn_Btn.click-Left 
     */
    function BackgroundStretchOn(UXMouseEvent $e = null)
    {
        $this->form('maingame')->platform->stretch = true;
    }
    /**
     * @event BackgroundStretchOff_Btn.click-Left 
     */
    function BackgroundStretchOff(UXMouseEvent $e = null)
    {
        $this->form('maingame')->platform->stretch = false;        
    }
    /**
     * @event ApplyHBarColorActor_Btn.click-Left 
     */
    function ApplyHealthBarColorActor(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->health_bar_gg->color = $this->Edit_HealthBarColor_Actor->value;
    }
    /**
     * @event ApplyHBarColorEnemy_Btn.click-Left 
     */
    function ApplyHealthBarColorEnemy(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->health_bar_enemy->color = $this->Edit_HealthBarColor_Enemy->value;        
    }
    /**
     * @event ResetHBarColorActor_Btn.click-Left 
     */
    function ResetHealthBarColorActor(UXMouseEvent $e = null)
    {    
        $this->Edit_HealthBarColor_Actor->value = UXColor::of('#990000');
        $this->ApplyHealthBarColorActor();
    }
    /**
     * @event ResetHBarColorEnemy_Btn.click-Left 
     */
    function ResetHealthBarColorEnemy(UXMouseEvent $e = null)
    {    
        $this->Edit_HealthBarColor_Enemy->value = UXColor::of('#990000');
        $this->ApplyHealthBarColorActor();        
    }
}
