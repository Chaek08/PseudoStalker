<?php
namespace app\forms;

use action\Media;
use php\gui\UXAlert;
use php\gui\paint\UXColor;
use php\gui\UXImage;
use php\gui\framework\AbstractForm;
use php\gui\event\UXMouseEvent; 


class sdk_mm_e extends AbstractForm
{
    function ApplyAll()
    {    
        $this->ApplyBackground();
        $this->ApplyMenuBackground();
        $this->ApplyActorModel();
        $this->ApplyEnemyModel();
        $this->ApplyHealthBarColorActor();
        $this->ApplyHealthBarColorEnemy();
    }
    function ResetAll()
    {    
        $this->ResetBackground();
        $this->ResetMenuBackground();
        $this->ResetActorModel();
        $this->ResetEnemyModel();
        $this->ResetHealthBarColorActor();
        $this->ResetHealthBarColorEnemy();
        $this->ResetFightSound();
    }
    function ClearAll()
    {    
        $this->ClearEdit();
        $this->ClearMenuBackground();
        $this->ClearEditActorModel();
        $this->ClearEditEnemyModel();
        $this->ClearFightSound();   
    }    
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
    }
    /**
     * @event ClearEdit_Btn.click-Left 
     */
    function ClearEdit(UXMouseEvent $e = null)
    {    
        $this->Edit_Background->text = '';
    }    
    /**
     * @event ApplyHBarColorActor_Btn.click-Left 
     */
    function ApplyHealthBarColorActor(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->health_bar_gg->color = $this->Edit_HealthBarColor_Actor->value;
        $this->form('maingame')->Inventory->content->health_bar_gg->color = $this->Edit_HealthBarColor_Actor->value;
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
    }
    /**
     * @event ResetHBarColorEnemy_Btn.click-Left 
     */
    function ResetHealthBarColorEnemy(UXMouseEvent $e = null)
    {    
        $this->Edit_HealthBarColor_Enemy->value = UXColor::of('#990000');
    }
    /**
     * @event ApplyActorModel_Btn.click-Left 
     */
    function ApplyActorModel(UXMouseEvent $e = null)
    {
        if ($this->Edit_ActorModel->text == '')
        {
            $this->form('sdk_main')->toast("enter model url");
        }
        else
        {
            $this->form('maingame')->actor->image = new UXImage($this->Edit_ActorModel->text);
            $this->form('maingame')->Inventory->content->inv_maket_visual->image = new UXImage($this->Edit_ActorModel->text);
        }
    }
    /**
     * @event ApplyEnemyModel_Btn.click-Left 
     */
    function ApplyEnemyModel(UXMouseEvent $e = null)
    {    
        if ($this->Edit_EnemyModel->text == '')
        {
            $this->form('sdk_main')->toast("enter model url");
        }
        else
        {
            $this->form('maingame')->enemy->image = new UXImage($this->Edit_EnemyModel->text);  
        }    
    }
    /**
     * @event ApplyMenuBackground_Btn.click-Left 
     */
    function ApplyMenuBackground(UXMouseEvent $e = null)
    {
        if ($this->Edit_Background->text == '')
        {
            $this->form('sdk_main')->toast("enter background url");
        }
        else 
        {
            $this->form('maingame')->MainMenu->content->background->image = new UXImage($this->Edit_MenuBackground->text);
            $this->form('maingame')->Options->content->background->image = new UXImage($this->Edit_MenuBackground->text);
        }
    }    
    /**
     * @event ResetActorModel_Btn.click-Left 
     */
    function ResetActorModel(UXMouseEvent $e = null)
    {
        $this->Edit_ActorModel->text = $this->Edit_ActorModel->promptText;
    }      
    /**
     * @event ResetEnemyModel_Btn.click-Left 
     */
    function ResetEnemyModel(UXMouseEvent $e = null)
    {    
        $this->Edit_EnemyModel->text = $this->Edit_EnemyModel->promptText;
    }
    /**
     * @event ResetMenuBackground_Btn.click-Left 
     */
    function ResetMenuBackground(UXMouseEvent $e = null)
    {
        $this->Edit_MenuBackground->text = $this->Edit_MenuBackground->promptText;
    }
    /**
     * @event ResetFightSound_Btn.click-Left 
     */
    function ResetFightSound(UXMouseEvent $e = null)
    {    
        $this->Edit_FightSound->text = $this->Edit_FightSound->promptText;
    }                     
    /**
     * @event ClearEditAModelEdit_Btn.click-Left 
     */
    function ClearEditActorModel(UXMouseEvent $e = null)
    {
        $this->Edit_ActorModel->text = '';
    }
    /**
     * @event ClearEModelEdit_Btn.click-Left 
     */
    function ClearEditEnemyModel(UXMouseEvent $e = null)
    {    
        $this->Edit_EnemyModel->text = '';
    }
    /**
     * @event ClearMenuBackground_Btn.click-Left 
     */
    function ClearMenuBackground(UXMouseEvent $e = null)
    {
        $this->Edit_MenuBackground->text = '';
    }
    /**
     * @event ClearFightSound_Btn.click-Left 
     */
    function ClearFightSound(UXMouseEvent $e = null)
    {    
        $this->Edit_FightSound->text = '';
    }       
    /**
     * @event ActorModelStretchOn_Btn.click-Left 
     */
    function ActorModelStretchOn(UXMouseEvent $e = null)
    {    
        if ($this->form('maingame')->actor->stretch == false)
        {
            $this->form('maingame')->actor->stretch = true;
        }
    }
    /**
     * @event ActorModelStretchOff_Btn.click-Left 
     */
    function ActorModelStretchOff(UXMouseEvent $e = null)
    {  
        if ($this->form('maingame')->actor->stretch == true)
        {
            $this->form('maingame')->actor->stretch = false;
        }
    }
    /**
     * @event EnemyModelStretchOn_Btn.click-Left 
     */
    function EnemyModelStretchOn(UXMouseEvent $e = null)
    {
        if ($this->form('maingame')->enemy->stretch == false)
        {
            $this->form('maingame')->enemy->stretch = true;
        }
    }
    /**
     * @event EnemyModelStretchOff_Btn.click-Left 
     */
    function EnemyModelStretchOff(UXMouseEvent $e = null)
    {
        if ($this->form('maingame')->enemy->stretch == true)
        {
            $this->form('maingame')->enemy->stretch = false;
        }
    }
    /**
     * @event FightSound_PreviewBtn.click-Left 
     */
    function PreviewFightSound(UXMouseEvent $e = null)
    {
        if ($this->form('maingame')->Options->content->All_Sounds->visible)
        {        
            Media::open($this->Edit_FightSound->text, true, 'PreviewFightSound');
        }
    }
    /**
     * @event FightSound_PreviewBtn.click-Right 
     */
    function StopPreviewFightSound(UXMouseEvent $e = null)
    {
        Media::stop('PreviewFightSound');
    }
}
