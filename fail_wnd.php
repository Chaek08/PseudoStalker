<?php
namespace app\forms;

use php\gui\text\UXFont;
use php\gui\UXImageView;
use php\gui\UXImage;
use std, gui, framework, app;


class fail_wnd extends AbstractForm
{
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
        if ($this->form('maingame')->SDK_Mode->visible)
        {
            $this->Win_object->image = new UXImage($this->form('sdk_main')->f_MgEditor->content->Edit_EnemyModel->text);
            $this->Win_fail_text->graphic = new UXImageView(new UXImage($this->form('sdk_main')->f_FailEditor->content->EditActorFailIcon->text));
            $this->Win_fail_text->text = uiText($this->form('sdk_main')->f_FailEditor->content->Win_Fail_Text_Actor_Edit);
            $this->Win_fail_desc->text = uiText($this->form('sdk_main')->f_FailEditor->content->Win_Fail_Desc_Actor_Edit);
        }
        else
        {
            $this->Win_object->image = new UXImage('res://.data/ui/fail_wnd/goblindav.png');
            $this->Win_fail_text->graphic = new UXImageView(new UXImage('res://.data/ui/fail_wnd/actor_fail.png'));
            $this->Win_fail_text->text = "Ну ты лох блять...";
            $this->Win_fail_desc->text = "Я пытался победить гоблиндава, но он оказался сильнее...\nЧто-ж, не получилось, не фортануло.";
        }
    }
    function SetEnemyFail()
    {        
        if ($this->form('maingame')->SDK_Mode->visible)
        {
            $this->Win_object->image = new UXImage($this->form('sdk_main')->f_MgEditor->content->Edit_ActorModel->text);
            $this->Win_fail_text->graphic = new UXImageView(new UXImage($this->form('sdk_main')->f_FailEditor->content->EditEnemyFailIcon->text));
            $this->Win_fail_text->text = uiText($this->form('sdk_main')->f_FailEditor->content->Win_Fail_Text_Enemy_Edit);
            $this->Win_fail_desc->text = uiText($this->form('sdk_main')->f_FailEditor->content->Win_Fail_Desc_Enemy_Edit);
        }
        else
        {
            $this->Win_object->image = new UXImage('res://.data/ui/fail_wnd/actor.png');
            $this->Win_fail_text->graphic = new UXImageView(new UXImage('res://.data/ui/fail_wnd/enemy_fail.png'));
            $this->Win_fail_text->text = "Харош!!!";
            $this->Win_fail_desc->text = "ЕБУЧИЙ гоблиндав повержен.\nЭто был нелёгкий бой, но я проявил максимальный энтузиазм и уничтожил паразита!";
        }             
    }
}
