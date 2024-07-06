<?php
namespace app\forms;

use app\forms\fail_wnd;
use php\gui\UXImage;
use action\Element;
use php\gui\framework\AbstractForm;
use php\gui\event\UXKeyEvent; 
use php\gui\event\UXMouseEvent; 


class sdk_userdata_e extends AbstractForm
{
    /**
     * @event ApplyAll_Btn.click-Left 
     */
    function ApplyAll(UXMouseEvent $e = null)
    {    
        $this->ApplyActorName();
        $this->ApplyActorIcon();
        $this->ApplyEnemyName();
        $this->ApplyEnemyIcon();
        $this->ApplyEnemyBio();
    }
    /**
     * @event ResetAll_Btn.click-Left 
     */
    function ResetAll(UXMouseEvent $e = null)
    {
        $this->ResetActorName();
        $this->ResetActorIcon();
        $this->ResetActorBio();
        $this->ResetEnemyName();
        $this->ResetEnemyIcon();
        $this->ResetEnemyBio();
    }    
    /**
     * @event ApplyActorName_Btn.click-Left
     */
    function ApplyActorName(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->fragment_pda->content->fragment_ranking->content->actor_in_raiting_name->text = $this->edit_actorname->text;
        
        if ($this->edit_actorname->text == '')
        {
            $this->form('sdk_main')->toast("enter a name");
        }
        else 
        {
            $this->form('maingame')->fragment_pda->content->fragment_stat->content->tab_button->text = $this->edit_actorname->text;            
            //Dialog wnd 
            $this->form('maingame')->fragment_dlg->content->actor_label_1->text = $this->edit_actorname->text;
            $this->form('maingame')->fragment_dlg->content->actor_label_3->text = $this->edit_actorname->text;    
            $this->form('maingame')->fragment_dlg->content->answer_name->text = $this->edit_actorname->text;   
            $this->form('maingame')->fragment_dlg->content->gg_name->text = $this->edit_actorname->text;                             
        }
    }
    /**
     * @event ApplyEnemyName_Btn.click-Left 
     */
    function ApplyEnemyName(UXMouseEvent $e = null)
    {    
        $this->form('maingame')->fragment_pda->content->fragment_ranking->content->goblindav_in_raiting_name->text = $this->edit_enemyname->text;
        
        if ($this->edit_enemyname->text == '')
        {
            $this->form('sdk_main')->toast("enter a name");
        }
        else      
        {
            $this->form('maingame')->fragment_pda->content->fragment_contacts->content->name->text = $this->edit_enemyname->text;
            //Dialog wnd 
            $this->form('maingame')->fragment_dlg->content->alex_label_1->text = $this->edit_enemyname->text;
            $this->form('maingame')->fragment_dlg->content->alex_label_2->text = $this->edit_enemyname->text;    
            $this->form('maingame')->fragment_dlg->content->alex_label_3->text = $this->edit_enemyname->text;   
            $this->form('maingame')->fragment_dlg->content->enemy_name->text = $this->edit_enemyname->text;              
        }   
    }
    /**
     * @event ApplyActorIcon_Btn.click-Left 
     */
    function ApplyActorIcon(UXMouseEvent $e = null)
    {     
        if ($this->Edit_ActorIcon->text == '')
        {
            $this->form('sdk_main')->toast("enter a icon url");
        }
        else 
        {
            $this->form('maingame')->fragment_pda->content->fragment_stat->content->icon->image = new UXImage($this->Edit_ActorIcon->text);
            //Dialog wnd 
            $this->form('maingame')->fragment_dlg->content->icon_gg->image = new UXImage($this->Edit_ActorIcon->text);
        }
    }
    /**
     * @event ApplyEnemyIcon_Btn.click-Left 
     */
    function ApplyEnemyIcon(UXMouseEvent $e = null)
    {    
        if ($this->Edit_EnemyIcon->text == '')
        {
            $this->form('sdk_main')->toast("enter a icon url");
        }
        else 
        {
            $this->form('maingame')->fragment_pda->content->fragment_contacts->content->icon->image = new UXImage($this->Edit_EnemyIcon->text);
            //Dialog wnd 
            $this->form('maingame')->fragment_dlg->content->icon_enemy->image = new UXImage($this->Edit_EnemyIcon->text);            
        }
    }    
    /**
     * @event ApplyEnemyBio_Btn.click-Left 
     */
    function ApplyEnemyBio(UXMouseEvent $e = null)
    {    
        if ($this->Edit_ActorIcon->text == '')
        {
            $this->form('sdk_main')->toast("enter a icon url");
        }    
        else 
        {
            $this->form('maingame')->fragment_pda->content->fragment_contacts->content->bio->text = $this->textArea_EnemyBio->text;            
        }
    } 
    /**
     * @event reset_actorname_btn.click-Left 
     */
    function ResetActorName(UXMouseEvent $e = null)
    {    
        $this->edit_actorname->text = $this->edit_actorname->promptText;
    }
    /**
     * @event reset_enemyname_btn.click-Left 
     */
    function ResetEnemyName(UXMouseEvent $e = null)
    {    
        $this->edit_enemyname->text = $this->edit_enemyname->promptText;
    }
    /**
     * @event reset_actorbio_btn.click-Left 
     */
    function ResetActorBio(UXMouseEvent $e = null)
    {    
        $this->textArea_ActorBio->text = $this->textArea_ActorBio->promptText;
    }
    /**
     * @event reset_enemybio_btn.click-Left 
     */
    function ResetEnemyBio(UXMouseEvent $e = null)
    {    
        $this->textArea_EnemyBio->text = $this->textArea_EnemyBio->promptText;
    }
    /**
     * @event ResetEnemyIcon_Btn.click-Left 
     */
    function ResetEnemyIcon(UXMouseEvent $e = null)
    {    
        $this->Edit_EnemyIcon->text = $this->Edit_EnemyIcon->promptText;
    }
    /**
     * @event ResetActorIcon_Btn.click-Left 
     */
    function ResetActorIcon(UXMouseEvent $e = null)
    {    
        $this->Edit_ActorIcon->text = $this->Edit_ActorIcon->promptText;
    }
}
