<?php
namespace app\forms;

use php\gui\text\UXFont;
use php\gui\UXImageView;
use php\gui\UXImage;
use std, gui, framework, app;
use php\gui\event\UXWindowEvent; 


class fail_wnd extends AbstractForm
{
    function InitFailWnd()
    {    
        $this->Win_fail_text_Legacy->show();
        $this->Win_fail_desc_Legacy->show();
    }
    /**
     * @event exitbtn.click-Left 
     */
    function ExitGameBtn(UXMouseEvent $e = null)
    {
        $this->form('maingame')->ShowMenu();
        $this->form('maingame')->ResetGameClient();
    }
    /**
     * @event returnbtn.click-Left 
     */
    function ReturnBtn(UXMouseEvent $e = null)
    {
        $this->form('maingame')->Fail->hide();   
        if ($this->form('maingame')->Options->content->All_Sounds->visible)
        {        
            Media::play('main_ambient'); 
            if ($this->form('maingame')->skull_actor->visible)
            {
                if (Media::isStatus('PLAYING','v_enemy')) {Media::stop('v_enemy');}
            }
            if ($this->form('maingame')->skull_enemy->visible)
            {
                if (Media::isStatus('PLAYING','v_actor')) {Media::stop('v_actor');}                
            }                
        }                 
    }
    function SetActorFail()
    {
        $this->Win_object->image = new UXImage('res://.data/ui/fail_wnd/goblindav.png');
        $this->Win_fail_text_Legacy->image = new UXImage('res://.data/ui/fail_wnd/fail_text.png');
        $this->Win_fail_desc_Legacy->image = new UXImage('res://.data/ui/fail_wnd/fail_text_desc.png');
    }
    function SetEnemyFail()
    {        
        $this->Win_object->image = new UXImage('res://.data/ui/fail_wnd/actor.png');
        $this->Win_fail_text_Legacy->image = new UXImage('res://.data/ui/fail_wnd/win_text.png');
        $this->Win_fail_desc_Legacy->image = new UXImage('res://.data/ui/fail_wnd/win_text_desc.png');          
    }
}
