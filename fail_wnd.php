<?php
namespace app\forms;

use std, gui, framework, app;


class fail_wnd extends AbstractForm
{
    /**
     * @event exitbtn.click-Left 
     */
    function ExitGameBtn(UXMouseEvent $e = null)
    {
        $this->form('maingame')->fragment_menu->show();
        $this->form('maingame')->ResetGameClient();
        $this->form('mainmenu')->StartMenuSound();     
    }
    /**
     * @event returnbtn.click-Left 
     */
    function ReturnBtn(UXMouseEvent $e = null)
    {
        $this->form('maingame')->fragment_win_fail->hide();   
        if ($this->form('maingame')->fragment_opt->content->all_sounds->visible)
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
        $this->Win_fail_text->image = new UXImage('res://.data/ui/fail_wnd/fail_text.png');
        $this->Win_fail_desc->image = new UXImage('res://.data/ui/fail_wnd/fail_text_desc.png');
        
        $this->Win_object->image = new UXImage('res://.data/ui/fail_wnd/goblindav.png');             
    }
    function SetEnemyFail()
    {        
        $this->Win_fail_text->image = new UXImage('res://.data/ui/fail_wnd/win_text.png');
        $this->Win_fail_desc->image = new UXImage('res://.data/ui/fail_wnd/win_text_desc.png');  
             
        $this->Win_object->image = new UXImage('res://.data/ui/fail_wnd/actor.png');                               
    }

}
