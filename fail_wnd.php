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
        if ($this->form('maingame')->fragment_opt->content->sound->visible)
        {        
            Media::play('main_ambient');    
        }                 
    }
    function SetActorFail()
    {
        $this->Win_fail_text->text = 'Ну ты лох блять...';       
        $this->Win_fail_text->graphic = new UXImageView(new UXImage('res://.data/ui/fail_wnd/actor_fail.png'));
        $this->Win_object->image = new UXImage('res://.data/ui/maingame/sprite/goblindav.png');
        
        $this->Win_fail_desc->text = $this->a_fail->text; 
    }
    function SetEnemyFail()
    {
        $this->Win_fail_text->text = 'Александр, харош!!!';
        $this->Win_fail_text->graphic = new UXImageView(new UXImage('res://.data/ui/fail_wnd/enemy_fail.png'));  
        $this->Win_object->image = new UXImage('res://.data/ui/maingame/sprite/actor.png');        
        
        $this->Win_fail_desc->text = $this->e_fail->text;                
    }

}
