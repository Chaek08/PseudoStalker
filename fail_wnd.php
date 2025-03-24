<?php
namespace app\forms;

use php\gui\text\UXFont;
use php\gui\UXImageView;
use php\gui\UXImage;
use std, gui, framework, app;
use php\gui\event\UXWindowEvent; 
use app\forms\classes\Localization;

class fail_wnd extends AbstractForm
{
    private $localization;

    public function __construct() 
    {
        parent::__construct();

        $this->localization = new Localization($language);
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
        if ($GLOBALS['AllSounds'])
        {        
            Media::play('main_ambient'); 
            
            if ($GLOBALS['ActorFailed'])
            {
                if (Media::isStatus('PLAYING','v_enemy')) Media::stop('v_enemy');
            }
            if ($GLOBALS['EnemyFailed'])
            {
                if (Media::isStatus('PLAYING','v_actor')) Media::stop('v_actor');                
            }                
        }                 
    }
    function UpdateFailState()
    {
        $this->localization->setLanguage($this->form('maingame')->Options->content->Language_Switcher_Combobobx->value);
        if ($GLOBALS['ActorFailed'])
        {
            if (SDK_Mode)
            {
                $this->Win_object->image = new UXImage($this->form('maingame')->Editor->content->f_MgEditor->content->Edit_EnemyModel->text);
                $this->Win_fail_text->graphic = new UXImageView(new UXImage($this->form('maingame')->Editor->content->f_FailEditor->content->EditActorFailIcon->text));
                $this->Win_fail_text->text = uiText($this->form('maingame')->Editor->content->f_FailEditor->content->Win_Fail_Text_Actor_Edit);
                $this->Win_fail_desc->text = uiText($this->form('maingame')->Editor->content->f_FailEditor->content->Win_Fail_Desc_Actor_Edit);                
            }
            else 
            {
                $this->Win_object->image = new UXImage('res://.data/ui/fail_wnd/goblindav.png');
                $this->Win_fail_text->graphic = new UXImageView(new UXImage('res://.data/ui/fail_wnd/actor_fail.png'));
                $this->Win_fail_text->text = $this->localization->get('ActorFail_Label');
                $this->Win_fail_desc->text = $this->localization->get('ActorFail_Desc');     
            }
        }
        if ($GLOBALS['EnemyFailed'])
        {
            if (SDK_Mode)
            {
                $this->Win_object->image = new UXImage($this->form('maingame')->Editor->content->f_MgEditor->content->Edit_ActorModel->text);
                $this->Win_fail_text->graphic = new UXImageView(new UXImage($this->form('maingame')->Editor->content->f_FailEditor->content->EditEnemyFailIcon->text));
                $this->Win_fail_text->text = uiText($this->form('maingame')->Editor->content->f_FailEditor->content->Win_Fail_Text_Enemy_Edit);
                $this->Win_fail_desc->text = uiText($this->form('maingame')->Editor->content->f_FailEditor->content->Win_Fail_Desc_Enemy_Edit);            
            }
            else 
            {
                $this->Win_object->image = new UXImage('res://.data/ui/fail_wnd/actor.png');
                $this->Win_fail_text->graphic = new UXImageView(new UXImage('res://.data/ui/fail_wnd/enemy_fail.png'));
                $this->Win_fail_text->text = $this->localization->get('EnemyFail_Label');
                $this->Win_fail_desc->text = $this->localization->get('EnemyFail_Desc');
            }            
        }        
    }
}
