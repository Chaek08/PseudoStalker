<?php
namespace app\forms;

use std, gui, framework, app;
use action\Geometry;
use script\MediaPlayerScript;
use php\gui\event\UXKeyEvent; 
use php\gui\event\UXMouseEvent; 

class maingame extends AbstractForm
{
    /**
     * @event show 
     */
    function InitClient(UXWindowEvent $e = null)
    {    
        $this->GetVersion();
        $this->OpenMainAmbient();
        
        if ($this->SDK_Mode->visible)
        {
            $this->MainMenu->content->opensdk_btn->show();
            $this->Editor->content->GetSDKVersion();           
        }
        else
        {
            $this->Editor->free(); 
            $this->MainMenu->content->opensdk_btn->free();
        }
    }
    function GetVersion()
    {
        if ($this->SDK_Mode->visible)
        {
            Element::setText($this->version_detail, "Editor Build 593, August 8 2024");
            Element::setText($this->MainMenu->content->version_detail, "v1.2 + SDK");
        }
        else
        {
            Element::setText($this->version_detail, "Build 593, August 8 2024"); //start date 24.12.2022
            Element::setText($this->MainMenu->content->version_detail, "v1.2");
        }
        if ($this->debug_build->visible)
        {
            $this->version->show();
            $this->version_detail->show();            
        }
        else
        {
            $this->MainMenu->content->version->show();
            $this->MainMenu->content->version_detail->show();            
        }        
    }     
    function LoadScreen()
    {
        $this->LoadScreen->show();
        $this->CustomCursor->hide();
        
        Animation::fadeTo($this->LoadScreen, 650, 1, function()
        {
           Animation::fadeIn($this->LoadScreen, 1);
           $this->LoadScreen->hide(); 
           $this->CustomCursor->show();       
        });            
    }     
    function OpenMainAmbient()
    {
        if ($this->Options->content->all_sounds->visible) Media::open('res://.data/audio/game/krip1.mp3', false, 'main_ambient');       
    }
    function PauseMainAmbient()
    {
        if ($this->Options->content->all_sounds->visible) Media::pause('main_ambient');   
    }    
    function PlayMainAmbient()
    {
        if ($this->Options->content->all_sounds->visible)
        {
             if (!$this->fight_image->visible)
             {
                 Media::play('main_ambient');                 
             }      
        }         
    }
    function StopAllSounds()
    {
        if ($this->Options->content->all_sounds->visible)
        { 
            if (Media::isStatus('PLAYING', 'fight_sound')) Media::stop('fight_sound');
            if (Media::isStatus('PLAYING', 'main_ambient')) Media::stop('main_ambient');
            if (Media::isStatus('PLAYING', 'v_enemy')) Media::stop('v_enemy');
            if (Media::isStatus('PLAYING', 'v_actor')) Media::stop('v_actor');
            if (Media::isStatus('PLAYING', 'hit_alex')) Media::stop('hit_alex');
            if (Media::isStatus('PLAYING', 'hit_alex_damage')) Media::stop('hit_alex_damage');      
            if (Media::isStatus('PLAYING', 'hit_actor')) Media::stop('hit_actor');
            if (Media::isStatus('PLAYING', 'hit_actor_damage')) Media::stop('hit_actor_damage');
            if (Media::isStatus('PLAYING', 'die_alex')) Media::stop('die_alex');
            if (Media::isStatus('PLAYING', 'die_actor')) Media::stop('die_actor');                                                                                                                                      
        }        
    }  
    /**
     * @event ReplayBtn.click-Left 
     */
    function ReplayFightSong(UXMouseEvent $e = null)
    {    
        if ($this->form('maingame')->Options->content->all_sounds->visible)
        {
            if ($this->form('maingame')->Options->content->mute_fight_sound->visible) {} else
            {
                if ($this->form('maingame')->SDK_Mode->visible)
                {
                    Media::open($this->form('maingame')->Editor->content->f_MgEditor->content->Edit_FightSound->text, true, "fight_sound");
                }
                else 
                {
                    Media::open('res://.data/audio/fight/fight_baza.mp3', true, "fight_sound");
                }               
            }     
        }      
    }      
    function ResetGameClient()
    {  
        $this->LoadScreen();
        $this->GetHealth();  
        $this->StopAllSounds();
         
        if ($this->fight_image->visible) $this->fight_image->hide();
        if ($this->leave_btn->visible) $this->leave_btn->hide();
        if ($this->Fail->visible) $this->Fail->hide();
        if ($this->blood_ui->visible) $this->blood_ui->hide();
        if ($this->ReplayBtn->visible) $this->ReplayBtn->hide();
                              
        if ($this->item_vodka_0000->visible) 
        {
            $this->Inventory->content->DespawnVodka();
        }
        $this->Inventory->content->SetItemCondition();     
        
        $this->idle_static_actor->show();
        $this->idle_static_enemy->show();    
        $this->actor->show();  
        $this->enemy->show();  
        $this->actor->x = 112;
        $this->enemy->x = 1312;        
                     
        $this->Pda->content->DefaultState();        
        $this->Pda->content->Pda_Contacts->content->AddEnemyContacts();   
        $this->Pda->content->Pda_Tasks->content->DeleteTask();    
        $this->Pda->content->Pda_Tasks->content->ShowActiveTasks();        
        $this->Pda->content->Pda_Tasks->content->StepReset();       
        $this->Pda->content->Pda_Tasks->content->Step_UpdatePda();             
        $this->Pda->content->Pda_Statistic->content->UpdateRaiting();  
                  
        $this->dlg_btn->show();         
        $this->Dialog->content->StartDialog();
        $this->MainMenu->content->NewGameMenu();
        $this->Pda->content->Pda_Statistic->content->ResetFinalText();
    }
    /**
     * @event keyDown-Esc 
     */
    function EscBtn(UXKeyEvent $e = null)
    {    
        if ($this->LoadScreen->visible) return;
        
        if ($this->SDK_Mode->visible)
        {
            if ($this->Editor->visible)
            {
                return;
            }
        }
             
        if ($this->MainMenu->visible) { $this->MainMenu->content->NewGameBtn(); return; }
        if ($this->Inventory->visible) { $this->HideInventory(); return; }
        if ($this->Dialog->visible) { $this->HideDialog(); Media::stop('voice_talk3'); return; }
        if ($this->Pda->visible) { $this->HidePda(); return; }
        if ($this->ExitDialog->visible) { $this->HideExitDialog(); return; }
        if ($this->Options->visible) return; 
        
        $this->ShowMenu();
        $this->PauseMainAmbient();
    }
    function ShowMenu()
    {
        $this->MainMenu->show();
        if ($this->Options->content->all_sounds->visible)
        {
            if ($this->Options->content->mute_menu_sound->visible)
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
        if ($this->LoadScreen->visible) return;  
        if ($this->Pda->visible) return; 
        if ($this->MainMenu->visible) return;
        if ($this->Options->visible) return;      
        if ($this->Inventory->visible) return;
        if ($this->Dialog->visible) return;
        if ($this->Fail->visible) return;
        
        if ($this->SDK_Mode->visible)
        {
            if ($this->Editor->visible) return;
        }
        
        $this->Pda->content->SetPDAOpacity();
        $this->Pda->show();
        if ($this->Pda->content->Pda_Statistic->visible)
        {
            if ($this->pda_icon->visible) $this->pda_icon->hide();
        }          
    }
    /**
     * @event keyDown-I 
     */
    function ShowInventory(UXKeyEvent $e = null)
    {       
        if ($this->LoadScreen->visible) return;  
        if ($this->Inventory->visible) return;
        if ($this->MainMenu->visible) return;
        if ($this->Options->visible) return;
        if ($this->Pda->visible) return;
        if ($this->Dialog->visible) return;          
        if ($this->Fail->visible) return;
        
        if ($this->SDK_Mode->visible)
        {
            if ($this->Editor->visible)
            {
                return;
            }
        }
        
        $this->Inventory->show();
        if ($this->Options->content->all_sounds->visible) Media::open('res://.data/audio/inv_open.mp3', true);
    }    
    /**
     * @event keyDown-F4 
     */
    function ShowExitDialog(UXKeyEvent $e = null)
    {          
        if ($this->LoadScreen->visible) return;  
        if ($this->MainMenu->visible) return;
        if ($this->ExitDialog->visible) return;  
        if ($this->Inventory->visible) return;
        if ($this->Pda->visible) return;
        if ($this->Options->visible) return;  
        if ($this->Dialog->visible) return;
        if ($this->Fail->visible) return;
        
        if ($this->SDK_Mode->visible)
        {
            if ($this->Editor->visible)
            {
                return;
            }
        }
        
        $this->ExitDialog->show();
    }
    /**
     * @event dlg_btn.click-Left 
     */
    function ShowDialog(UXMouseEvent $e = null)
    {          
        $this->Dialog->show();  
        $this->Dialog->content->StartDialog();          
        $this->Dialog->content->VoiceStart();    
    }
    function HideExitDialog()
    {
        if ($this->ExitDialog->visible) $this->ExitDialog->hide();
    }
    function HideDialog()
    {
        if ($this->Dialog->visible)
        {  
            $this->Dialog->content->ClearDialog();
            $this->Dialog->content->ResetAnswerVisible();
            $this->Dialog->content->StopVoice();
            $this->Dialog->hide();                           
        } 
    }
    function HideInventory()
    {
        if ($this->Inventory->visible)
        {
            $this->Inventory->content->HideOutfitMaket();
            $this->Inventory->content->HideVodkaMaket(); 
            $this->Inventory->content->HideUIText(); 
            $this->Inventory->content->HideCombobox();                              
            $this->Inventory->hide();
                
            if ($this->Options->content->all_sounds->visible) Media::open('res://.data/audio/inv_close.mp3', true);         
        }
        
    }
    function HidePda()
    {
        if ($this->Pda->visible)
        {
            $this->Pda->hide();
            $this->Pda->content->DefaultState();             
        }        
    }
    function HideOpt()
    {
        if ($this->Options->visible) $this->Options->hide();      
    }
    /**
     * @event leave_btn.click-Left 
     */
    function LeaveBtn(UXMouseEvent $e = null)
    {    
        $this->Fail->show();
        
        if ($this->Options->content->all_sounds->visible)
        {
            Media::pause('main_ambient');
        }          
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
    function GetHealth() 
    {
        $this->health_bar_gg->width = 264;
        $this->health_bar_gg->text = "100%";
        $this->health_bar_gg->show();
        $this->health_bar_gg_b->show();
        
        $this->Inventory->content->health_bar_gg->show();
        $this->Inventory->content->health_bar_gg_b->show();        
        $this->Inventory->content->health_bar_gg->width = 416; //100%
        $this->Inventory->content->health_bar_gg->text = "100%";               
                
        $this->health_bar_enemy->show();     
        $this->health_bar_enemy_b->show();           
        $this->health_bar_enemy->width = 264;    
        $this->health_bar_enemy->text = "100%"; 
        
        if ($this->skull_actor->visible || $this->skull_enemy->visible)
        {
            $this->skull_actor->hide();
            $this->skull_enemy->hide();
            $this->Pda->content->Pda_Ranking->content->DeathFilter();
        }    
        if ($this->Inventory->content->skull_actor->visible)  
        {
            $this->Inventory->content->skull_actor->hide();
        }
    }
    /**
     * @event actor.click-2x
     */       
    function DamageEnemy(UXMouseEvent $e = null)
    { 
        if ($this->health_bar_enemy->width != 34)
        {
            $this->health_bar_enemy->width -= 50;
             
            if ($this->Options->content->all_sounds->visible)
            {
                Media::open('res://.data/audio/hit_sound/hit_alex.mp3', true, 'hit_alex');
                Media::open('res://.data/audio/hit_sound/kulak_ebanul.mp3', true, 'hit_alex_damage');
            }        
        }
        else     
        {
            $this->skull_enemy->show();
            $this->health_bar_enemy->hide();
            $this->health_bar_enemy_b->hide();
            $this->leave_btn->show();
            $this->dlg_btn->hide();
            
            if ($this->Options->content->all_sounds->visible) Media::open('res://.data/audio/hit_sound/die_alex.mp3', true, 'die_alex');
             
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
            $this->health_bar_enemy->width += 20;
        }                    
    }
    /**
     * @event enemy.click-2x
     */    
    function DamageActor(UXMouseEvent $e = null)
    { 
        if ($this->health_bar_gg->width != 34)
        {
            $this->health_bar_gg->width -= 50;  
            Animation::fadeIn($this->hitmark_static, 250);  
            $this->hitmark_static->show();
            Animation::fadeOut($this->hitmark_static, 500);
            if ($this->Options->content->all_sounds->visible)
            {
                Media::open('res://.data/audio/hit_sound/hit_vovchik.mp3', true, 'hit_actor'); 
                Media::open('res://.data/audio/hit_sound/kulak_ebanul.mp3', true, 'hit_actor_damage'); 
            }        
        }
        else     
        {
            $this->skull_actor->show();
            $this->health_bar_gg->hide();
            $this->health_bar_gg_b->hide();
            $this->Bleeding();
            $this->Inventory->content->health_bar_gg->hide();
            $this->Inventory->content->health_bar_gg_b->hide();            
            $this->Inventory->content->skull_actor->show();
            $this->leave_btn->show();
            $this->dlg_btn->hide();
                       
            if ($this->hitmark_static->visible)
            {
                $this->hitmark_static->hide();
            }
            
            if ($this->Options->content->all_sounds->visible) Media::open('res://.data/audio/hit_sound/die_vovchik.mp3', true, 'die_actor');
            
            $this->ActorFail();
            return;
        }   
        if ($this->health_bar_gg->width == 214)
        {
            $this->health_bar_gg->text = "75%";
            $this->Inventory->content->health_bar_gg->width -= 100;           
            $this->Inventory->content->health_bar_gg->text = "75%";
            $this->Bleeding(); 
        }
        if ($this->health_bar_gg->width == 164)
        {
            $this->health_bar_gg->text = "55%";
            $this->Inventory->content->health_bar_gg->width -= 50;            
            $this->Inventory->content->health_bar_gg->text = "55%";   
            $this->Inventory->content->SetItemCondition();
        }      
        if ($this->health_bar_gg->width == 114)
        {
            $this->health_bar_gg->text = "50%";
            $this->Inventory->content->health_bar_gg->width -= 40;     
            $this->Inventory->content->health_bar_gg->text = "50%";         
            $this->Inventory->content->SetItemCondition();
            $this->Bleeding();  
        }  
        if ($this->health_bar_gg->width == 64)
        {
            $this->health_bar_gg->text = "33%";
            $this->Inventory->content->health_bar_gg->width -= 150;            
            $this->Inventory->content->health_bar_gg->text = "33%";                          
        }    
        if ($this->health_bar_gg->width == 14)
        {
            $this->health_bar_gg->text = "1%";
            $this->Inventory->content->health_bar_gg->width -= 40;            
            $this->Inventory->content->health_bar_gg->text = "1%"; 
            $this->health_bar_gg->width += 20;
            $this->Bleeding();   
            $this->Inventory->content->SetItemCondition();                           
        }                     
    }    
    function Bleeding()
    {
        if ($this->skull_actor->visible)
        {
            $this->blood_ui->hide();
        }
        else 
        {
            $this->blood_ui->show();
        }
        
        if ($this->health_bar_gg->width == 214)
        {
            $this->blood_ui->image = new UXImage('res://.data/ui/maingame/blood_mini.png');
        }
        if ($this->health_bar_gg->width == 114)
        {
            $this->blood_ui->image = new UXImage('res://.data/ui/maingame/blood_medium.png');            
        }
        if ($this->health_bar_gg->width == 34)
        {
            $this->blood_ui->image = new UXImage('res://.data/ui/maingame/blood_ultra.png');            
        }        
    }
    function FailAction()
    {
        $this->ReplayBtn->hide();
        $this->fight_image->hide();
        $this->Fail->content->InitFailWnd();  
        $this->Fail->show();
        $this->item_vodka_0000->enabled = false;
        
        $this->idle_static_actor->show();
        $this->idle_static_enemy->show();
        $this->actor->x = 112;
        $this->enemy->x = 1312;
              
        $this->Pda->content->Pda_Ranking->content->DeathFilter();   
        $this->Pda->content->Pda_Tasks->content->Step_UpdatePda();
        $this->Pda->content->Pda_Statistic->content->UpdateRaiting();
        
        $this->StopAllSounds();                   
    }
    function ActorFail()
    {
        $this->FailAction();
    
        $this->actor->hide();      
           
        $this->Fail->content->SetActorFail();
        $this->Pda->content->Pda_Statistic->content->ActorFailText();
        $this->Pda->content->Pda_Tasks->content->Step2_Failed();         
        
        if ($this->Options->content->all_sounds->visible)
        {
            Media::open('res://.data/audio/victory/victory_alex.mp3', true, 'v_enemy');
        }
    }
    function EnemyFail()
    {
        $this->FailAction();   
    
        $this->enemy->hide();
                
        $this->Fail->content->SetEnemyFail();
        $this->Pda->content->Pda_Statistic->content->EnemyFailText();    
        $this->Pda->content->Pda_Tasks->content->Step2_Complete();
        
        $this->Pda->content->Pda_Contacts->content->DeleteEnemyContacts();            
        
        if ($this->Options->content->all_sounds->visible)
        {
            Media::open('res://.data/audio/victory/victory_actor.mp3', true, 'v_actor');
        }
    }
    /**
     * @event keyDown-Q 
     */
    function OpenConsole(UXKeyEvent $e = null)
    {    
        if ($this->Console->toggle()) $this->Console->visible;
    }
}
