<?php
namespace app\forms;

use php\concurrent\Future;
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
        define('BuildID', 'Build 819, Mar 22 2025'); //start date 24.12.2022
        define('VersionID', 'v1.3 (rc2)');
        
        define('Debug_Build', false);
        define('SDK_Mode', true);
        
        define('ToggleHudFeature', true);
        
        $GLOBALS['AllSounds'] = true;
        $GLOBALS['MenuSound'] = true;
        $GLOBALS['FightSound'] = true;
        
        if (ToggleHudFeature) $GLOBALS['HudVisible'] = true;
     
        $this->GetVersion();
        
        $this->MainMenu->content->InitMainMenu();
        if (SDK_Mode)
        {
            $this->MainMenu->content->opensdk_btn->show();
        }
        else
        {
            $this->Editor->free();
            $this->MainMenu->content->opensdk_btn->free();
        }
        
        $this->MainMenu->content->Options->content->InitOptions();
    }
    function GetVersion()
    {
        if (Debug_Build)
        {
            $this->version->show();
            $this->version_detail->show();
            
            Element::setText($this->version_detail, SDK_Mode ? "Editor " . BuildID : BuildID);
        }
        else
        {
            $this->MainMenu->content->version->show();
            $this->MainMenu->content->version_detail->show();
            
            Element::setText($this->MainMenu->content->version_detail, VersionID . (SDK_Mode ? ' + SDK' : ''));
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
        if ($GLOBALS['AllSounds']) Media::open('res://.data/audio/game/krip1.mp3', false, $this->MainAmbient);       
    }
    function PauseMainAmbient()
    {
        if ($GLOBALS['AllSounds']) Media::pause($this->MainAmbient);
    }    
    function PlayMainAmbient()
    {
        if ($GLOBALS['AllSounds'] && !$this->fight_image->visible) Media::play($this->MainAmbient);
    }
    function PlayFightSong()
    {    
        if ($GLOBALS['AllSounds'] || $GLOBALS['FightSound'])
        {
            if (SDK_Mode)
            {
                Media::open($this->form('maingame')->Editor->content->f_MgEditor->content->Edit_FightSound->text, true, $this->FightSound);
            }
            else
            {   
                Media::open($fightsoundPath = 'res://.data/audio/fight/fight_sound.mp3', true, $this->FightSound);
            }
        }
    }    
    function StopAllSounds()
    {
        if (Media::isStatus('PLAYING', $this->FightSound)) Media::stop($this->FightSound);
        if (Media::isStatus('PLAYING', $this->MainAmbient)) Media::stop($this->MainAmbient);
        if (Media::isStatus('PLAYING', $this->MainMenu->content->MenuSound)) Media::stop($this->MainMenu->content->MenuSound);
        if (Media::isStatus('PLAYING', 'v_enemy')) Media::stop('v_enemy');
        if (Media::isStatus('PLAYING', 'v_actor')) Media::stop('v_actor');
        if (Media::isStatus('PLAYING', 'hit_alex')) Media::stop('hit_alex');
        if (Media::isStatus('PLAYING', 'hit_alex_damage')) Media::stop('hit_alex_damage');      
        if (Media::isStatus('PLAYING', 'hit_actor')) Media::stop('hit_actor');
        if (Media::isStatus('PLAYING', 'hit_actor_damage')) Media::stop('hit_actor_damage');
        if (Media::isStatus('PLAYING', 'die_alex')) Media::stop('die_alex');
        if (Media::isStatus('PLAYING', 'die_actor')) Media::stop('die_actor');
        
        $this->Dialog->content->StopVoice();
    }  
    function ResetGameClient()
    {  
        $this->LoadScreen();
        $this->GetHealth();
        
        if ($GLOBALS['AllSounds']) $this->StopAllSounds();
         
        if ($this->fight_image->visible) $this->fight_image->hide();
        if ($this->leave_btn->visible) $this->leave_btn->hide();
        if ($this->Fail->visible) $this->Fail->hide();
        if ($this->blood_ui->visible) $this->blood_ui->hide();
                              
        if ($this->item_vodka_0000->visible) $this->Inventory->content->DespawnVodka();
        $this->Inventory->content->SetItemCondition();     
        
        $this->idle_static_actor->show();
        $this->idle_static_enemy->show();    
        $this->actor->show();  
        $this->enemy->show();  
        $this->actor->x = 112;
        $this->enemy->x = 1312;    
                     
        if ($GLOBALS['QuestCompleted']) $GLOBALS['QuestCompleted'] = false;
        if ($GLOBALS['ActorFailed']) $GLOBALS['ActorFailed'] = false;
        if ($GLOBALS['EnemyFailed']) $GLOBALS['EnemyFailed'] = false;
        
        if (ToggleHudFeature && $GLOBALS['NeedToCheckPDA']) $GLOBALS['NeedToCheckPDA'] = false;
                             
        $this->Pda->content->DefaultState();
        $this->Pda->content->Pda_Contacts->content->AddEnemyContacts();
        $this->Pda->content->Pda_Tasks->content->UpdateQuestTime();
        $this->Pda->content->Pda_Tasks->content->DeleteTask();
        $this->Pda->content->Pda_Tasks->content->ShowActiveTasks();
        $this->Pda->content->Pda_Tasks->content->StepReset();
        $this->Pda->content->Pda_Tasks->content->Step_UpdatePda();
        $this->Pda->content->Pda_Statistic->content->UpdateRaiting();
                  
        $this->dlg_btn->show();         
        $this->Dialog->content->StartDialog();
        if ($GLOBALS['ContinueGameState']) $this->MainMenu->content->SwitchGameState();
        if ($this->MainMenu->visible) $this->MainMenu->content->InitMainMenu();
        $this->Pda->content->Pda_Statistic->content->ResetFinalText();
    }
    function CheckVisibledFragments()
    {
        if ($this->MainMenu->visible) return true;    
        if ($this->LoadScreen->visible) return true;
        if ($this->Pda->visible) return true;
        if ($this->Options->visible) return true;
        if ($this->Inventory->visible) return true;
        if ($this->Dialog->visible) return true;
        if ($this->Fail->visible) return true;
        
        if (SDK_Mode && $this->Editor->visible) return true;
        
        return false;
    }     
    function ToggleHud()
    {
        if ($GLOBALS['HudVisible'])
        {
            if ($this->skull_actor->visible) $this->skull_actor->hide(); //deprecated in future
            if ($this->skull_enemy->visible) $this->skull_enemy->hide(); //deprecated in future        
        
            $this->health_static_gg->hide();
            $this->health_bar_gg->hide();
            $this->health_bar_gg_b->hide();
        
            $this->health_static_enemy->hide();
            $this->health_bar_enemy->hide();
            $this->health_bar_enemy_b->hide();
            
            if ($this->blood_ui->visible) $this->blood_ui->hide();
            if ($this->pda_icon->visible) $this->pda_icon->hide();
            
            if ($this->fight_image->visible) $this->fight_image->hide();
        
            if ($this->dlg_btn->visible || $this->fight_image->visible) $this->dlg_btn->hide();
            if ($this->leave_btn->visible) $this->leave_btn->hide();
            
            $GLOBALS['HudVisible'] = false;
            return;
        }
        else 
        {
            if (!$this->actor->visible) $this->skull_actor->show(); //deprecated in future
            if (!$this->enemy->visible) $this->skull_enemy->show(); //deprecated in Future
                    
            $this->health_static_gg->show();
            if (!$this->skull_actor->visible) 
            {
                $this->health_bar_gg->show();
                $this->health_bar_gg_b->show();
            }
            $this->health_static_enemy->show();
            if (!$this->skull_enemy->visible) 
            {
                $this->health_bar_enemy->show();
                $this->health_bar_enemy_b->show();
            }
            
            $this->Bleeding(); //блядинг
            if ($GLOBALS['NeedToCheckPDA']) $this->pda_icon->show();
            
            if (!$this->idle_static_actor->visible) $this->fight_image->show();
            
            if ($this->skull_actor->visible || $this->skull_enemy->visible) $this->leave_btn->show();
            if (!$this->leave_btn->visible && $this->idle_static_actor->visible) $this->dlg_btn->show();        
        
            $GLOBALS['HudVisible'] = true;
            return;            
        }
    }
    /**
     * @event keyDown-Esc 
     */
    function EscBtn(UXKeyEvent $e = null)
    {    
        if (ToggleHudFeature) $this->ToggleHud();
            
        if ($this->LoadScreen->visible) return;
        if (SDK_Mode && $this->Editor->visible)
        {
            $this->Editor->content->StartMainGame();
            return;
        } 
        if ($this->MainMenu->visible) 
        {
            if ($this->MainMenu->content->Options->visible)
            {
                $this->MainMenu->content->Options->content->ReturnBtn_MouseDownLeft();
                $this->MainMenu->content->Options->content->ReturnBtn_MouseExit();
                return;
            }
            $this->MainMenu->content->BtnStartGame_MouseDownLeft();
            $this->MainMenu->content->BtnStartGame_MouseExit();
            return;
        }
        if ($this->Inventory->visible)
        {
            $this->HideInventory();
            return;
        }
        if ($this->Dialog->visible)
        {
            $this->HideDialog();
            return;
        }
        if (Media::isStatus('PLAYING', 'voice_talk3'))
        {
            $this->HideDialog();
        }
        if ($this->Pda->visible)
        {
            $this->HidePda();
            return;
        }
        if ($this->ExitDialog->visible) 
        {
            $this->ExitDialog->hide();
            return;
        }
        
        $this->ShowMenu();
        $this->PauseMainAmbient();
    }
    function ShowMenu()
    {
        $this->MainMenu->show();
        Media::play($this->MainMenu->content->MainMenuBackground);
        
        if ($GLOBALS['AllSounds'] || $GLOBALS['FightSound'])
        {
            Media::pause($this->FightSound);
            
            if ($GLOBALS['MenuSound'])
            {
                Media::play($this->MainMenu->content->MenuSound);
            }
        }
    }
    /**
     * @event keyDown-P 
     */    
    function ShowPda()
    {
        if ($this->CheckVisibledFragments()) return;
        
        if (ToggleHudFeature && !$this->ExitDialog->visible) $this->ToggleHud();
        
        $this->Pda->show();
        if ($this->Pda->content->Pda_Statistic->visible && $this->pda_icon->visible) $this->pda_icon->hide();       
    }
    /**
     * @event keyDown-I 
     */
    function ShowInventory(UXKeyEvent $e = null)
    {       
        if ($this->CheckVisibledFragments()) return;
        
        if (ToggleHudFeature && !$this->ExitDialog->visible) $this->ToggleHud();        
        
        $this->Inventory->show();
        if ($GLOBALS['AllSounds']) Media::open('res://.data/audio/inv_open.mp3', true);
    }    
    /**
     * @event keyDown-F4 
     */
    function ShowExitDialog(UXKeyEvent $e = null)
    {          
        if ($this->CheckVisibledFragments()) return;
        
        if (ToggleHudFeature && !$this->ExitDialog->visible) $this->ToggleHud();        
        
        $this->ExitDialog->show();
    }
    /**
     * @event dlg_btn.click-Left 
     */
    function ShowDialog(UXMouseEvent $e = null)
    {          
        if (ToggleHudFeature) $this->ToggleHud();
    
        $this->Dialog->show();
        $this->Dialog->content->StartDialog();          
        $this->Dialog->content->VoiceStart();    
    }
    function HideDialog()
    {
        $this->Dialog->content->ClearDialog();
        $this->Dialog->content->ResetAnswerVisible();
        $this->Dialog->content->StopVoice();
        $this->Dialog->hide();
    }
    function HideInventory()
    {
        $this->Inventory->content->HideOutfitMaket();
        $this->Inventory->content->HideVodkaMaket(); 
        $this->Inventory->content->HideUIText(); 
        $this->Inventory->content->HideCombobox();                              
        $this->Inventory->hide();
                
        if ($GLOBALS['AllSounds']) Media::open('res://.data/audio/inv_close.mp3', true);         
    }
    function HidePda()
    {
        $this->Pda->hide();
        $this->Pda->content->DefaultState();                    
    }
    /**
     * @event leave_btn.click-Left 
     */
    function LeaveBtn(UXMouseEvent $e = null)
    {    
        $this->Fail->show();
        
        if ($GLOBALS['AllSounds']) Media::pause($this->MainAmbient);
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
        
        $this->Inventory->content->health_bar_gg->show();
        $this->Inventory->content->health_bar_gg_b->show();
        $this->Inventory->content->health_bar_gg->width = 416; //100%
        $this->Inventory->content->health_bar_gg->text = "100%";               
                         
        $this->health_bar_enemy->width = 264;
        $this->health_bar_enemy->text = "100%";
        
        if (!ToggleHudFeature || $GLOBALS['HudVisible'])
        {
            $this->health_bar_gg->show();
            $this->health_bar_gg_b->show();
            
            $this->health_bar_enemy->show();  
            $this->health_bar_enemy_b->show();        
        }
        
        if ($this->Inventory->content->skull_actor->visible) $this->Inventory->content->skull_actor->hide();
        if ($this->skull_actor->visible || $this->skull_enemy->visible)
        {
            $this->skull_actor->hide();
            $this->skull_enemy->hide();
            
            $this->Pda->content->Pda_Ranking->content->DeathFilter();
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
             
            if ($GLOBALS['AllSounds'])
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
            
            if ($GLOBALS['AllSounds']) Media::open('res://.data/audio/hit_sound/die_alex.mp3', true, 'die_alex');
             
            $GLOBALS['EnemyFailed'] = true;
            $this->finalizeBattle();
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
            
            if ($GLOBALS['AllSounds'])
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
                       
            if ($this->hitmark_static->visible) $this->hitmark_static->hide();
            
            if ($GLOBALS['AllSounds']) Media::open('res://.data/audio/hit_sound/die_vovchik.mp3', true, 'die_actor');
            
            $GLOBALS['ActorFailed'] = true;
            $this->finalizeBattle();
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
        if ($this->health_bar_gg->width == 264) return;
        
        $this->skull_actor->visible ? $this->blood_ui->hide() : $this->blood_ui->show();
        
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
    function finalizeBattle()
    {
        $GLOBALS['QuestCompleted'] = true;
        if (ToggleHudFeature) $GLOBALS['NeedToCheckPDA'] = true;
        
        $this->Fail->content->UpdateFailState();
        $this->Pda->content->Pda_Statistic->content->UpdateFinalLabel();
    
        $this->fight_image->hide();
        $this->Fail->show();
        $this->item_vodka_0000->enabled = false;
        
        $this->idle_static_actor->show();
        $this->idle_static_enemy->show();
        $this->actor->x = 112;
        $this->enemy->x = 1312;
              
        $this->Pda->content->Pda_Ranking->content->DeathFilter();   
        $this->Pda->content->Pda_Tasks->content->Step_UpdatePda();
        $this->Pda->content->Pda_Statistic->content->UpdateRaiting();
        
        if ($GLOBALS['AllSounds']) $this->StopAllSounds();
        
        if ($GLOBALS['ActorFailed'])
        {
            $this->actor->hide();
            
            $this->Pda->content->Pda_Tasks->content->Step2_Failed();
            
            if ($GLOBALS['AllSounds']) Media::open('res://.data/audio/victory/victory_alex.mp3', true, 'v_enemy');
        }
        if ($GLOBALS['EnemyFailed'])
        {
            $this->enemy->hide();
            
            $this->Pda->content->Pda_Tasks->content->Step2_Complete();
            $this->Pda->content->Pda_Contacts->content->DeleteEnemyContacts();
            
            if ($GLOBALS['AllSounds']) Media::open('res://.data/audio/victory/victory_actor.mp3', true, 'v_actor');
        }
    }
    /**
     * @event keyDown-Q 
     */
    function OpenConsole(UXKeyEvent $e = null)
    {    
        if ($this->Console->toggle()) 
        {
            $this->Console->visible;
        }
    }
    /**
     * @event ForwardSDK_Btn.click-2x
     */
    function ForwardSDK(UXMouseEvent $e = null)
    {    
        $this->MainMenu->content->OpenSdkBtn();
        
        $this->ForwardSDK_Btn->hide();
        
        $this->form('maingame')->MainMenu->content->opensdk_btn->enabled = true;
        $this->form('maingame')->MainMenu->content->opensdk_btn->text = 'Open SDK';
    }
}
