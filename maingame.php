<?php
namespace app\forms;

use std, gui, framework, app;
use action\Geometry;
use script\MediaPlayerScript;

class maingame extends AbstractForm
{
    /**
     * @event show 
     */
    function MainGame(UXWindowEvent $e = null)
    {    
        $this->GetHealth();
        $this->OpenMainAmbient();
    }
    function OpenMainAmbient()
    {
        if ($this->fragment_opt->content->sound->visible)
        {
             Media::open('res://.data/audio/game/krip.mp3', false, 'main_ambient');
        }        
    }
    function PlayMainAmbient()
    {
        if ($this->fragment_opt->content->sound->visible)
        {
             Media::play('main_ambient');      
        }         
    }
    function StopMainAmbient()
    {
        if ($this->fragment_opt->content->sound->visible)
        {
             Media::pause('main_ambient');             
        }            
    }
    function ResetGameClient()
    {  
        if ($this->fight_label->visible) {$this->fight_label->hide();} 
        if ($this->leave_btn->visible) {$this->leave_btn->hide();}        
        if ($this->fragment_act_fail->visible) {$this->fragment_act_fail->hide();}     
        if ($this->fragment_enm_fail->visible) {$this->fragment_enm_fail->hide();}    
        if ($this->pda_icon->visible) {$this->pda_icon->hide();}            
        
        if ($this->item_vodka_0000->visible) 
        {
            $this->fragment_inv->content->DespawnVodka();
            $this->item_vodka_0000->enabled = true;  
        }
                               
        $this->fragment_inv->content->ResetOutfitCondition();                
        $this->GetHealth();  
        
        $this->fragment_pda->content->fragment_contacts->content->AddEnemyContacts();   
        $this->fragment_pda->content->fragment_tasks->content->AddTask();                
         
        $this->dlg_btn->show();
        $this->idle_static_actor->show(); $this->actor->x = 112;
        $this->idle_static_enemy->show(); $this->enemy->x = 1312; 
        $this->actor->opacity = 100; 
        $this->enemy->opacity = 100;                 
        $this->fragment_dlg->content->answer_1_new->show(); 
        $this->fragment_dlg->content->answer_desc->text = "Даю тебе зелье натурала!";    
        $this->fragment_dlg->content->ClearDialog();
        $this->fragment_menu->content->Btn__NewGame();
        $this->fragment_pda->content->fragment_stat->content->ResetFinalText();
        Media::stop('fight_sound');
        Media::stop('main_ambient');
    }
    /**
     * @event keyDown-Esc 
     */
    function EscBtn(UXKeyEvent $e = null)
    {    
        if ($this->fragment_inv->visible) 
        {
            $this->HideInventory(); 
            if ($this->fragment_opt->content->sound->visible){Media::open('res://.data/audio/inv_close.mp3', true);}
            return;
        }     
        if ($this->fragment_pda->visible) {$this->HidePda(); return;}
        if ($this->fragment_dlg->visible) {$this->HideDialog(); return;}   
        if ($this->fragment_exit->visible) {$this->HideExitDialog(); return;}    
        if ($this->fragment_opt->visible) {return;}        
        $this->ShowMenu();
        $this->StopMainAmbient();
    }
    function ShowMenu()
    {
        $this->fragment_menu->show();
        if ($this->fragment_opt->content->sound->visible)
        {
            if ($this->fragment_opt->content->mutesound->visible)
            {
                 Media::pause("fight_sound"); 
            }
            else 
            {
                 Media::play("menu_sound");
                 Media::pause("fight_sound");        
            }  
        }
    }
    /**
     * @event keyDown-P 
     */    
    function ShowPda()
    {
        if($this->fragment_menu->visible)
        {
            return;
        }
        if($this->fragment_opt->visible)
        {
            return;
        }        
        if ($this->fragment_inv->visible)
        {
            return;
        }
        if($this->fragment_dlg->visible)
        {
            return;
        }         
        if ($this->fragment_act_fail->visible || $this->fragment_enm_fail->visible)
        {
            return;
        }
        $this->ResetFragmentsVisible();
        $this->fragment_pda->show();      
    }
    /**
     * @event keyDown-I 
     */
    function ShowInventory(UXKeyEvent $e = null)
    {       
        if($this->fragment_menu->visible)
        {
            return;
        }
        if($this->fragment_opt->visible)
        {
            return;
        }             
        if ($this->fragment_pda->visible)
        {
            return;
        }  
        if($this->fragment_dlg->visible)
        {
            return;
        }           
        if ($this->fragment_act_fail->show || $this->fragment_enm_fail->show)
        {
            return;
        }               
              
        if ($this->fragment_opt->content->sound->visible){ Media::open('res://.data/audio/inv_open.mp3', true);} 
        $this->ResetFragmentsVisible();       
        $this->fragment_inv->show();   
    }    
    /**
     * @event keyDown-F4 
     */
    function ShowExitDialog_KF4(UXKeyEvent $e = null)
    {          
        if($this->fragment_menu->visible)
        {
            return;
        }    
        if ($this->fragment_inv->visible)
        {
            return;
        }
        if ($this->fragment_pda->visible)
        {
            return;
        } 
        if($this->fragment_opt->visible)
        {
            return;
        }    
        if($this->fragment_dlg->visible)
        {
            return;
        }           
        if ($this->fragment_act_fail->visible || $this->fragment_enm_fail->visible)
        {
            return;
        }                           
        $this->ResetFragmentsVisible();    
        $this->ShowExitDialog();   
    }
    function ShowExitDialog()
    {
        $this->fragment_exit->toggle() == $this->fragment_exit->visible;  
    }
    function ResetFragmentsVisible()
    {
        if ($this->fragment_menu->visible) {$this->fragment_menu->hide();}
        if ($this->fragment_inv->visible) {$this->fragment_inv->hide();}
        if ($this->fragment_pda->visible) {$this->fragment_pda->hide();}  
        if ($this->fragment_opt->visible) {$this->fragment_opt->hide();}      
        if ($this->fragment_dlg->visible) {$this->fragment_dlg->hide();}      
        if ($this->fragment_exit->visible) {$this->fragment_exit->hide();}   
        if ($this->fragment_act_fail->visible) {$this->fragment_act_fail->hide();}     
        if ($this->fragment_enm_fail->visible) {$this->fragment_enm_fail->hide();}                              
    }
    /**
     * @event dlg_btn.click-Left 
     */
    function ShowDialog(UXMouseEvent $e = null)
    {          
        $this->ResetFragmentsVisible();
        $this->fragment_dlg->show();       
    }
    /**
     * @event actor.click-2x
     */
    function DamageEnemyBtn(UXMouseEvent $e = null)
    {    
        $this->DamageEnemy();
    }
    /**
     * @event enemy.click-2x
     */
    function DamageActorBtn(UXMouseEvent $e = null)
    {    
        $this->DamageActor();
    }
    /**
     * @event leave_btn.click-Left 
     */
    function LeaveBtn(UXMouseEvent $e = null) //после появления fail или win
    {    
        if ($this->skull_actor->visible)
        {
            $this->fragment_act_fail->show();
            Media::pause('main_ambient');    
        }
        if ($this->skull_enemy->visible)
        {
            $this->fragment_enm_fail->show();
            Media::pause('main_ambient');             
        }   
    }
    /**
     * @event button.click-Left 
     */
    function OLD_DELETE_PLS_2(UXMouseEvent $e = null)
    {    
        $this->ResetGameClient();
    }
    /**
     * @event buttonAlt.click-Left 
     */
    function OLD_DELETE_PLS_3(UXMouseEvent $e = null)
    {    
        $this->fragment_dlg->content->Talk_3();
    }
    /**
     * @event item_vodka_0000.click-2x
     */
    function VodkaAttack(UXMouseEvent $e = null)
    {    
        if ($this->item_vodka_0000->x != 1312)
        {
             Animation::moveTo($this->item_vodka_0000, 2000, 1270, 640); 
             $this->item_vodka_0000->dragging->disable();
        }
        if ($this->item_vodka_0000->x == 1270)
        {
            $this->item_vodka_0000->x += 42; 
            if (Geometry::intersect($this->item_vodka_0000, $this->enemy))
            {
                $this->DamageEnemy();
                Animation::displace($this->item_vodka_0000, 500, -42, $y); 
            }
        }
    }
    /**
     * @event item_vodka_0000.click-Right 
     */
    function VodkaDraggingEnable(UXMouseEvent $e = null)
    {    
        $this->item_vodka_0000->dragging->enable();
        if ($this->item_vodka_0000->x == 1270 || $this->item_vodka_0000->x == 1312)
        {
            Animation::displace($this->item_vodka_0000, 500, -1030, $y); 
        }
    }
    function HideExitDialog()
    {
        if ($this->fragment_exit->visible) {$this->fragment_exit->hide();}  
    }
    function HideDialog()
    {
        if ($this->fragment_dlg->visible) {$this->fragment_dlg->hide();}         
    }
    function HideInventory()
    {
        if ($this->fragment_inv->visible)
        {
            $this->fragment_inv->content->HideOutfitMaket();
            $this->fragment_inv->content->HideVodkaMaket(); 
            $this->fragment_inv->content->HideUIText();                   
            $this->fragment_inv->hide();                
        }
        
    }
    function HidePda()
    {
        if ($this->fragment_pda->visible) {$this->fragment_pda->hide();}        
    }
    function HideOpt()
    {
        if ($this->fragment_opt->visible) {$this->fragment_opt->hide();}          
    }    
    function GetHealth() 
    {
        $this->health_bar_gg->width = 264;
        $this->health_bar_gg->text = "100%";
        $this->health_bar_gg->show();
        
        $this->fragment_inv->content->health_bar_gg->show();
        $this->fragment_inv->content->health_bar_gg->width = 416; //100%
        $this->fragment_inv->content->health_bar_gg->text = "100%";               
                
        $this->health_bar_enemy->show();                
        $this->health_bar_enemy->width = 264;    
        $this->health_bar_enemy->text = "100%"; 
        
        if ($this->skull_actor->visible || $this->skull_enemy->visible)
        {
            $this->skull_actor->hide();
            $this->skull_enemy->hide();
        }    
        if ($this->fragment_inv->content->skull_actor->visible)  
        {
            $this->fragment_inv->content->skull_actor->hide();
        }
    }    
    function DamageEnemy()
    { 
        if ($this->health_bar_enemy->width != 24)
        {
            $this->health_bar_enemy->width -= 50;        
            if ($this->fragment_opt->content->sound->visible)
            {
                Media::open('res://.data/audio/hit_sound/hit_alex.mp3', true, 'hit_alex');
                Media::open('res://.data/audio/hit_sound/kulak_ebanul.mp3', true, 'hit_alex_damage');
            }        
        }
        else     
        {
            $this->skull_enemy->show();
            $this->health_bar_enemy->hide();
            $this->leave_btn->show();
            $this->dlg_btn->hide();                       
            if ($this->fragment_opt->content->sound->visible){Media::open('res://.data/audio/hit_sound/die_alex.mp3', true, 'die_alex'); }   
            $this->EnemyFail();         
            return;
        }   
        if ($this->health_bar_enemy->width == 214)
        {
            $this->health_bar_enemy->text = "75%";
        }
        if ($this->health_bar_enemy->width == 164)
        {
            $this->health_bar_enemy->text = "55%";
        }      
        if ($this->health_bar_enemy->width == 114)
        {
            $this->health_bar_enemy->text = "50%";
        }  
        if ($this->health_bar_enemy->width == 64)
        {
            $this->health_bar_enemy->text = "33%";
        }    
        if ($this->health_bar_enemy->width == 14)
        {
            $this->health_bar_enemy->text = "1%";
            $this->health_bar_enemy->width += 10;
        }                    
    }
    function DamageActor()
    { 
        if ($this->health_bar_gg->width != 24)
        {
            $this->health_bar_gg->width -= 50;  
            Animation::fadeIn($this->hitmark_static, 250);  
            $this->hitmark_static->show();
            Animation::fadeOut($this->hitmark_static, 500);
            if ($this->fragment_opt->content->sound->visible)
            {
                Media::open('res://.data/audio/hit_sound/hit_vovchik.mp3', true, 'hit_actor'); 
                Media::open('res://.data/audio/hit_sound/kulak_ebanul.mp3', true, 'hit_actor_damage'); 
            }        
        }
        else     
        {
            $this->skull_actor->show();
            $this->health_bar_gg->hide();
            $this->fragment_inv->content->health_bar_gg->hide();
            $this->fragment_inv->content->skull_actor->show();
            $this->leave_btn->show();
            $this->dlg_btn->hide();             
            if ($this->hitmark_static->visible)
            {
                $this->hitmark_static->hide();
            }
            if ($this->fragment_opt->content->sound->visible){Media::open('res://.data/audio/hit_sound/die_vovchik.mp3', true, 'die_actor');  }  
            $this->ActorFail();          
            return;
        }   
        if ($this->health_bar_gg->width == 214)
        {
            $this->health_bar_gg->text = "75%";
            $this->fragment_inv->content->health_bar_gg->width -= 100;           
            $this->fragment_inv->content->health_bar_gg->text = "75%";
        }
        if ($this->health_bar_gg->width == 164)
        {
            $this->health_bar_gg->text = "55%";
            $this->fragment_inv->content->health_bar_gg->width -= 50;            
            $this->fragment_inv->content->health_bar_gg->text = "55%";   
            $this->fragment_inv->content->SetOutfitCondition();      
        }      
        if ($this->health_bar_gg->width == 114)
        {
            $this->health_bar_gg->text = "50%";
            $this->fragment_inv->content->health_bar_gg->width -= 40;            
            $this->fragment_inv->content->health_bar_gg->text = "50%";  
            $this->fragment_inv->content->SetOutfitCondition();          
        }  
        if ($this->health_bar_gg->width == 64)
        {
            $this->health_bar_gg->text = "33%";
            $this->fragment_inv->content->health_bar_gg->width -= 150;            
            $this->fragment_inv->content->health_bar_gg->text = "33%";            
        }    
        if ($this->health_bar_gg->width == 14)
        {
            $this->health_bar_gg->text = "1%";
            $this->fragment_inv->content->health_bar_gg->width -= 50;            
            $this->fragment_inv->content->health_bar_gg->text = "1%"; 
            $this->health_bar_gg->width += 10;   
            $this->fragment_inv->content->SetOutfitCondition();                           
        }                     
    }    
    function ActorFail()
    {
        $this->fight_label->hide();
        $this->fragment_act_fail->show();
        $this->fragment_pda->content->fragment_stat->content->ActorFailText();
        $this->pda_icon->show(); 
        $this->idle_static_actor->show(); $this->actor->x = 112;
        $this->idle_static_enemy->show(); $this->enemy->x = 1312;  
        $this->item_vodka_0000->enabled = false; 
        $this->fragment_pda->content->fragment_tasks->content->DeleteTask();  
        $this->actor->opacity = 0;      
        if ($this->fragment_opt->content->sound->visible){Media::open('res://.data/audio/victory/victory_alex.mp3', true, 'v_enemy');}       
        Media::pause("fight_sound");                       
    }
    function EnemyFail()
    {
        $this->fight_label->hide();
        $this->fragment_enm_fail->show();
        $this->fragment_pda->content->fragment_stat->content->EnemyFailText();   
        $this->pda_icon->show();     
        $this->idle_static_actor->show(); $this->actor->x = 112;
        $this->idle_static_enemy->show(); $this->enemy->x = 1312;  
        $this->item_vodka_0000->enabled = false;  
        $this->fragment_pda->content->fragment_contacts->content->DeleteEnemyContacts();    
        $this->fragment_pda->content->fragment_tasks->content->DeleteTask();     
        $this->enemy->opacity = 0;     
        if ($this->fragment_opt->content->sound->visible){Media::open('res://.data/audio/victory/victory_actor.mp3', true, 'v_actor'); }      
        Media::pause("fight_sound");                                           
    }
    function BugDetectSystem()
    {
        app()->showForm('bugdetect');
        app()->hideForm('maingame');
    }
    /**
     * @event button3.click-Left 
     */
    function OLD_DELETE_PLS_1(UXMouseEvent $e = null)
    {    
        $this->BugDetectSystem();
    }    
}
