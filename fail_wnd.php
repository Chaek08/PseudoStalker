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
    public $SDK_FailTextActor;
    public $SDK_FailTextIconActor;
    public $SDK_FailDescActor;
    public $SDK_FailTextEnemy;
    public $SDK_FailTextIconEnemy;
    public $SDK_FailDescEnemy;

    /**
     * @event exitbtn.click-Left 
     */
    function ExitGameBtn(UXMouseEvent $e = null)
    {
        $this->form('Client')->ShowMenu();
        
        $this->form('Client')->MainGame->content->ResetGameClient();
    }
    /**
     * @event returnbtn.click-Left 
     */
    function ReturnBtn(UXMouseEvent $e = null)
    {
        $Client = $this->form('Client');
        
        $Client->MainGame->content->RenderHud(true);
        $Vodka = $Client->MainGame->content->ItemVodka;
        
        $Client->Fail->hide();
        
        if (!$Client->Inventory->content->Inv_Vodka->visible) //ПРОВЕРИТЬ
        {
            $Vodka->show();
        }
        
        if ($Client->MainGame->content->GameActor->isDead()) $Client->MainGame->content->GameEnemy->GetModel()->show();
        if ($Client->MainGame->content->GameEnemy->isDead()) $Client->MainGame->content->GameActor->GetModel()->show();
        
        if (!$Client->MainGame->content->GameActor->isDead())
        {
            $w = $Client->MainGame->content->GameActor->getWeapon();
            if ($w) $w->softShow();
        }     
    }
    
    function UpdateFailState()
    {
        $Client = $this->form('Client');
        if ($Client->MainGame->content->GameActor->isDead())
        {
            $enemy_model = trim($this->form('Client')->MainGame->content->SDK_EnemyModel);
            $actor_failtext = trim($this->SDK_FailTextActor);
            $actor_failtexticon = trim($this->SDK_FailTextIconActor);
            $actor_faildesc = trim($this->SDK_FailDescActor);
                
            $this->Win_object->image = $actor_model != '' ? new UXImage($actor_model) : $this->form('Client')->MainGame->content->enemy->image;
            $this->Win_fail_text->text = $actor_failtext != '' ? $actor_failtext : Localization::get('ActorFail_Label');
            $this->Win_fail_text->graphic = new UXImageView(new UXImage($actor_failtexticon != '' ? $actor_failtexticon : 'res://.data/ui/fail_wnd/actor_fail.png'));
            $this->Win_fail_desc->text = $actor_faildesc != '' ? $actor_faildesc : Localization::get('ActorFail_Desc');
        }
        if ($Client->MainGame->content->GameEnemy->isDead())
        {
            $actor_model = trim($this->form('Client')->MainGame->content->SDK_ActorModel);
            $enemy_failtext = trim($this->SDK_FailTextEnemy);
            $enemy_failtexticon = trim($this->SDK_FailTextIconEnemy);
            $enemy_faildesc = trim($this->SDK_FailDescEnemy);    
            
            $this->Win_object->image = $actor_model != '' ? new UXImage($actor_model) : $this->form('Client')->MainGame->content->actor->image;
            $this->Win_fail_text->text = $enemy_failtext != '' ? $enemy_failtext : Localization::get('EnemyFail_Label');
            $this->Win_fail_text->graphic = new UXImageView(new UXImage($enemy_failtexticon != '' ? $enemy_failtexticon : 'res://.data/ui/fail_wnd/enemy_fail.png'));
            $this->Win_fail_desc->text = $enemy_faildesc != '' ? $enemy_faildesc : Localization::get('EnemyFail_Desc');                       
        }        
    }
}
