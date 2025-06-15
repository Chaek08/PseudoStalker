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
    
    public $SDK_FailTextActor = '';
    public $SDK_FailTextIconActor = '';
    public $SDK_FailDescActor = '';
    public $SDK_FailTextEnemy = '';
    public $SDK_FailTextIconEnemy = '';
    public $SDK_FailDescEnemy = '';

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
        $this->form('maingame')->ToggleHud();
        $this->form('maingame')->ShowMenu();
        $this->form('maingame')->ResetGameClient();
    }
    /**
     * @event returnbtn.click-Left 
     */
    function ReturnBtn(UXMouseEvent $e = null)
    {
        $this->form('maingame')->ToggleHud();
        
        $this->form('maingame')->Fail->hide();
        if ($GLOBALS['ActorFailed']) $this->form('maingame')->enemy->show();
        if ($GLOBALS['EnemyFailed']) $this->form('maingame')->actor->show();
                   
        if ($GLOBALS['AllSounds'])
        {
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
        $this->localization->setLanguage($this->form('maingame')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value);
        if ($GLOBALS['ActorFailed'])
        {
            $enemy_model = trim($this->form('maingame')->SDK_EnemyModel);
            $actor_failtext = trim($this->SDK_FailTextActor);
            $actor_failtexticon = trim($this->SDK_FailTextIconActor);
            $actor_faildesc = trim($this->SDK_FailDescActor);
                
            $this->Win_object->image = new UXImage($enemy_model != '' ? $enemy_model : 'res://.data/ui/fail_wnd/goblindav.png');
            $this->Win_fail_text->text = $actor_failtext != '' ? $actor_failtext : $this->localization->get('ActorFail_Label');
            $this->Win_fail_text->graphic = new UXImageView(new UXImage($actor_failtexticon != '' ? $actor_failtexticon : 'res://.data/ui/fail_wnd/actor_fail.png'));
            $this->Win_fail_desc->text = $actor_faildesc != '' ? $actor_faildesc : $this->localization->get('ActorFail_Desc');
        }
        if ($GLOBALS['EnemyFailed'])
        {
            $actor_model = trim($this->form('maingame')->SDK_ActorModel);
            $enemy_failtext = trim($this->SDK_FailTextEnemy);
            $enemy_failtexticon = trim($this->SDK_FailTextIconEnemy);
            $enemy_faildesc = trim($this->SDK_FailDescEnemy);    
            
            $this->Win_object->image = new UXImage($actor_model != '' ? $actor_model : 'res://.data/ui/fail_wnd/actor.png');
            $this->Win_fail_text->text = $enemy_failtext != '' ? $enemy_failtext : $this->localization->get('EnemyFail_Label');
            $this->Win_fail_text->graphic = new UXImageView(new UXImage($enemy_failtexticon != '' ? $enemy_failtexticon : 'res://.data/ui/fail_wnd/enemy_fail.png'));
            $this->Win_fail_desc->text = $enemy_faildesc != '' ? $enemy_faildesc : $this->localization->get('EnemyFail_Desc');                       
        }        
    }
}
