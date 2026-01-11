<?php
namespace app\forms;

use php\desktop\Mouse;
use app\forms\classes\Environment;
use app\forms\classes\CEnemy;
use Throwable;
use app\forms\classes\CActor;
use app\forms\classes\Weapons\CWeapon_Dev;
use behaviour\custom\ColorAdjustEffectBehaviour;
use php\time\Timer;
use php\gui\UXImageView;
use discord\rpc\DiscordRPC;
use php\gui\UXMediaView;
use php\gui\UXImage;
use php\gui\UXApplication;
use php\concurrent\Future;
use std, gui, framework, app;
use action\Geometry;
use script\MediaPlayerScript;
use php\gui\event\UXKeyEvent; 
use php\gui\event\UXMouseEvent; 
use php\framework\Logger;
use app\forms\classes\Localization;
use php\gui\event\UXEvent; 
use app\forms\classes\ParticleManager;

class maingame extends AbstractForm
{
    private $currentCycle = '';
    private $localization;
    
    public $SDK_FightSound;
    public $SDK_ActorModel;
    public $SDK_EnemyModel;    
    
    public $Environment;
    public $Particles;

    public $GameActor;
    public $GameEnemy;

    public $currentWeapon = null;  
    public $weaponState = [];
    
    public $ItemVodka;

    public function __construct() 
    {
        parent::__construct();

        $this->localization = new Localization($language); 
        
        $this->Particles = new ParticleManager($this);        
               
        $this->GameActor = new CActor($this);
        $this->GameActor->SetModel($this->actor);
        
        $this->GameActor->SetInteractive(false);
        
        $this->GameEnemy = new CEnemy($this);
        $this->GameEnemy->SetModel($this->enemy);
        
        $this->GameEnemy->SetInteractive(false);
        
        $this->ItemVodka = new CVodka($this, $this->item_vodka_0000, $this->GameActor, $this->GameEnemy); //CItem zavtra
        $this->ItemVodka->disable();
        $this->ItemVodka->hide();
        $this->ItemVodka->resetVisual();
        
        $this->Environment = new Environment($this->Environment_Space);
        $this->Environment->startAmbient();
        $this->Environment->pause();
        $this->Environment->setOnCycleChange(function ($old, $new) use ($this) {
            $brightness = $this->Environment->getEnvironmentBrightness();
            
            $this->GameActor->GetModel()->colorAdjustEffect->brightness   = $brightness;
            $this->GameEnemy->GetModel()->colorAdjustEffect->brightness   = $brightness;
            
            $this->ItemVodka->setBrightness($brightness);
        });
        $this->Environment->fireCycleChangeOnce();   
    }
    
    function getCurrentLanguageFromUI()
    {
        return $this->form('Client')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value;
    }     
    
    function PlayFightSong()
    {
        $path = trim($this->SDK_FightSound);
    
        if ($path == '')
        {
            $path = 'res://.data/audio/fight/fight_sound.mp3';
        }
        
        try
        {
            Media::open($path, false, $this->FightSound);
        }
        catch (\Throwable $e)
        {
            Debug::fail("Fight sound not found: $path", __FILE__, __LINE__);
            return;
        }
    
        if ($GLOBALS['AllSounds'] || $GLOBALS['FightSound'])
        {
            $this->FightSound->play();
        }
    }    
    
    function ResetGameClient(callable $afterReset = null)
    {
        $this->form('Client')->ShowLoadScreen(function () use ($afterReset)
        {
            if ($GLOBALS['QuestStep1']) $GLOBALS['QuestStep1'] = false;
            if ($GLOBALS['QuestCompleted']) $GLOBALS['QuestCompleted'] = false;

            if ($GLOBALS['AllSounds']) $this->form('Client')->StopAllSoundsAsync();
            Media::stop($this->Environment);

            if ($this->fight_image->visible) $this->fight_image->hide();
            if ($this->leave_btn->visible || !$GLOBALS['QuestCompleted']) $this->leave_btn->hide();
            if ($this->form('Client')->Fail->visible) $this->form('Client')->Fail->hide();
            if ($this->blood_ui->visible) $this->blood_ui->hide();

            $this->form('Client')->Inventory->content->DespawnItems();
            $this->form('Client')->Inventory->content->SetItemCondition();
            
            $this->SwitchWeapon('Pm');
            UXApplication::runLater(function ()
            {
                if ($this->currentWeapon && $this->currentWeapon->getType() === 'Pm')
                {
                    $w = $this->currentWeapon;
                    $w->importState(['jammed' => false, 'jamHandled' => false]);
                    $w->setAmmo($w->getMagSize());
                    $this->UpdateMagazine();
                }
    
                $this->SwitchWeapon('AK74');
                UXApplication::runLater(function () {
                    if ($this->currentWeapon && $this->currentWeapon->getType() === 'AK74')
                    {
                        $w = $this->currentWeapon;
                        $w->importState(['jammed' => false, 'jamHandled' => false]);
                        $w->setAmmo($w->getMagSize());
                        $this->UpdateMagazine();
                    }
                });
            });
            
            $this->form('Client')->Inventory->content->InventoryGrid->content->MoveWeaponsToInvSlot();
           
            $this->GameActor->respawn(112, $this->GameActor->GetModel()->y, false);
            $this->GameEnemy->respawn(1312, $this->GameEnemy->GetModel()->y, false);
            
            $this->ItemVodka->disable();

            $this->form('Client')->Pda->content->DefaultState();
            $this->form('Client')->Pda->content->Pda_Contacts->content->UpdateContacts();
            $this->form('Client')->Pda->content->Pda_Tasks->content->UpdateQuestTime();
            $this->form('Client')->Pda->content->Pda_Tasks->content->DeleteTask();
            $this->form('Client')->Pda->content->Pda_Tasks->content->ShowActiveTasks();
            $this->form('Client')->Pda->content->Pda_Tasks->content->StepReset();
            $this->form('Client')->Pda->content->Pda_Tasks->content->Step_DeletePda();
            $this->form('Client')->Pda->content->Pda_Ranking->content->DeathFilterManager();
            $this->form('Client')->Pda->content->Pda_Statistic->content->UpdateRaiting();
            $this->form('Client')->Pda->content->Pda_Statistic->content->UpdateFinalLabel();

            $this->GetHealth();

            if ($GLOBALS['ContinueGameState'])
            {
                $this->form('Client')->MainMenu->content->SwitchGameState();
            }

            if ($this->form('Client')->MainMenu->visible)
            {
                $this->form('Client')->MainMenu->content->InitMainMenu();
            }
            
            if ($this->Environment)
            {
                $this->Environment->stop();
            }
            
            if (empty($GLOBALS['IsSaveLoading']))
            {
                $this->Environment = new Environment($this->Environment_Space);
                $this->Environment->startAmbient();
                $this->Environment->pause();
                $this->Environment->setOnCycleChange(function ($old, $new) use ($this) {
                    $brightness = $this->Environment->getEnvironmentBrightness();
            
                    $this->GameActor->GetModel()->colorAdjustEffect->brightness   = $brightness;
                    $this->GameEnemy->GetModel()->colorAdjustEffect->brightness   = $brightness;
                    $this->item_vodka_0000->colorAdjustEffect->brightness         = $brightness;
                });

            }         

            $this->form('Client')->Dialog->content->StartDialog();
            
            if ($this->form('Client')->ltx['discord_rpc'] == 'on')
            {            
                $GLOBALS['discord']->setState(null);
                $GLOBALS['discord']->updateState();           
            }  
            
            if ($afterReset)
            {
                $afterReset();
            }
        });
    }
    function RenderHud($enable)
    {
        if ($enable) 
        {
            $this->health_static_gg->show();
            if (!$this->GameActor->isDead()) 
            {
                $this->health_bar_gg->show();
                $this->health_bar_gg_b->show();
            }
            $this->health_static_enemy->show();
            if (!$this->GameEnemy->isDead()) 
            {
                $this->health_bar_enemy->show();
                $this->health_bar_enemy_b->show();
            }

            $this->Bleeding();
            
            if ($this->currentWeapon) $this->ui_mag_background->show();
            if ($GLOBALS['NeedToCheckPDA']) $this->pda_icon->show();
            if ($GLOBALS['GodMode']) $this->GodMode_Icon->show();
            if ($this->GameActor->CanInteractive() || $this->GameEnemy->CanInteractive()) $this->fight_image->show();
            if ($this->GameActor->isDead() || $this->GameEnemy->isDead()) $this->leave_btn->show();
        
            $GLOBALS['HudVisible'] = true;
        } 
        else 
        {
            $this->health_static_gg->hide();
            $this->health_bar_gg->hide();
            $this->health_bar_gg_b->hide();
            $this->health_static_enemy->hide();
            $this->health_bar_enemy->hide();
            $this->health_bar_enemy_b->hide();
            
            $this->ui_mag_background->hide();
            
            $this->leave_btn->hide();
            
            if ($this->blood_ui->visible) $this->blood_ui->hide();
            if ($this->GodMode_Icon->visible) $this->GodMode_Icon->hide();
            if ($this->pda_icon->visible) $this->pda_icon->hide();
            if ($this->fight_image->visible) $this->fight_image->hide();
            if ($this->SavedGame_Toast->visible) $this->SavedGame_Toast->hide();
            if ($this->MessageBox->visible) $this->MessageBox->hide();
            if ($this->Task_Step_Label->visible) $this->Task_Step_Label->hide();
        
            $GLOBALS['HudVisible'] = false;
        }
    }

    /**
     * @event leave_btn.click-Left 
     */
    function LeaveBtn(UXMouseEvent $e = null)
    {
        $this->RenderHud(false);
        
        $this->form('Client')->Fail->content->UpdateFailState();
        $this->form('Client')->Fail->show();
        
        if ($this->currentWeapon) $this->currentWeapon->softHide();
        
        if ($this->ItemVodka->isVisible()) $this->ItemVodka->hide();
        if ($this->GameActor->isDead()) $this->GameEnemy->GetModel()->hide();
        if ($this->GameEnemy->isDead()) $this->GameActor->GetModel()->hide();
    }
    
    function SpawnItem()
    {
        $this->ItemVodka->spawn();
    }
    
    /**
     * @event item_vodka_0000.click-2x
     */
    function VodkaAttack(UXMouseEvent $e = null)
    {
        $this->ItemVodka->throwAtEnemy(function ($enemy) {
            $this->Particles->bloodConeAtTarget($enemy);
            $this->DamageEnemy(null, false, false);
        });
    }

    /**
     * @event item_vodka_0000.click-Right 
     */
    function VodkaDraggingEnable(UXMouseEvent $e = null)
    {    
        $this->ItemVodka->returnToActor();
    } 
       
    function GetHealth() 
    {
        if (!$GLOBLAS['QuestStep1'])
        {
            $this->health_bar_gg->width = 264;
            $this->form('Client')->Inventory->content->health_bar_gg->width = 416;
            $this->health_bar_enemy->width = 264;
            
            $this->health_bar_gg->text = "100%";
            $this->form('Client')->Inventory->content->health_bar_gg->text = "100%";
            $this->health_bar_enemy->text = "100%";
        }
        if (!$this->GameActor->isDead())
        {
            $this->form('Client')->Inventory->content->health_static_gg->graphic = null;
            $this->form('Client')->Inventory->content->health_bar_gg->show();
            $this->form('Client')->Inventory->content->health_bar_gg_b->show();
            
            $this->health_static_gg->graphic = null;
        }
        else 
        {
            $this->form('Client')->Inventory->content->health_static_gg->graphic = new UXImageView(new UXImage('res://.data/ui/maingame/skull_new.png'));
            $this->form('Client')->Inventory->content->health_bar_gg->hide();
            $this->form('Client')->Inventory->content->health_bar_gg_b->hide();
            
            $this->health_bar_gg->hide();
            $this->health_bar_gg_b->hide();
            $this->health_static_gg->graphic = new UXImageView(new UXImage('res://.data/ui/maingame/skull_new.png'));
        }
        if (!$this->GameEnemy->isDead())
        {
            $this->health_static_enemy->graphic = null;
        }
        else 
        {
            $this->health_bar_enemy->hide();
            $this->health_bar_enemy_b->hide();
            $this->health_static_enemy->graphic = new UXImageView(new UXImage('res://.data/ui/maingame/skull_new.png'));
        }
    }
    function GodMode()
    {
        $baseY = 96;
        
        $nextY = $baseY;
    
        if ($this->currentWeapon !== null)
        {
            $this->ui_mag_background->y = $baseY;
            $nextY = $baseY + 64;
        }
    
        if ($GLOBALS['GodMode'])
        {
            if ($GLOBALS['HudVisible']) $this->GodMode_Icon->show();
            $this->GodMode_Icon->y = $nextY;
            $nextY += 64;
        }
        else
        {
            $this->GodMode_Icon->hide();
        }
    
        $this->blood_ui->y = $nextY;
    }
    
    private $coverTimer;
    
    /**
     * @event enemy.click-2x
     */       
    function DamageEnemy(UXMouseEvent $e = null, bool $spawnParticles = true,  bool $damageByMouse = true)
    { 
        if (!$this->GameEnemy->CanInteractive())
        {
            return;
        }
        
        $minWidth     = 54;
        $maxWidth     = 264;
        $missChance   = 75;
        $damageMinPct = 8;
        $damageMaxPct = 20;
    
        if ($this->health_bar_enemy->width != $minWidth)
        {
            $didMiss = rand(1, 100) <= $missChance;
    
            if (!$didMiss)
            {
                $curW   = $this->health_bar_enemy->width;
                $curPct = round((($curW - $minWidth) / ($maxWidth - $minWidth)) * 99) + 1;
                $curPct = max(1, min(100, $curPct));
    
                $dmgPct = rand($damageMinPct, $damageMaxPct);
    
                $newPct = max(1, $curPct - $dmgPct);
    
                $target = (int) round($minWidth + (($maxWidth - $minWidth) * ($newPct - 1) / 99));
    
                $this->form('Client')->animateResizeWidth($this->health_bar_enemy, $target, 3, function() {
                    $minW = 54; $maxW = 264;
                    $cur  = $this->health_bar_enemy->width;
                    $pct  = round((($cur - $minW) / ($maxW - $minW)) * 99) + 1;
                    $pct  = max(1, min(100, $pct));
                    $this->health_bar_enemy->text = $pct . "%";
                });
            }
    
            if ($spawnParticles)
            {
                if ($damageByMouse)
                {
                    $enemy  = $this->GameEnemy->GetModel();
                    
                    $localX = $e->x;
                    $localY = $e->y;                    
                    
                    $originX = $enemy->x + $localX;
                    $originY = $enemy->y + $localY;                    
            
                    $floorY = $enemy->y + $enemy->height - 20;

                    $this->Particles->bloodBurstAtPoint($originX, $originY, $floorY, 4, 7);
                }
                else
                {
                    $this->Particles->bloodConeAtTarget($this->GameEnemy->GetModel());
                }
            }

            if ($GLOBALS['AllSounds'])
            {
                $randEbanul = rand(0, 5);
                $this->form('Client')->playSoundAsync("res://.data/audio/fight/hit_sounds/kulak_ebanul/kulak_ebanul_{$randEbanul}.mp3", true, 'hit_enemy_damage');
                
                $playHitChance = 90;
                if (rand(1, 100) <= $playHitChance)
                {
                    $randHit = rand(1, 8);
                    $this->form('Client')->playSoundAsync("res://.data/audio/fight/hit_sounds/enemy/hit_{$randHit}.mp3", true, 'hit_enemy');
                }
                
                $playCoverChance = 20;
                if (rand(1, 100) <= 20)
                {
                    if ($this->coverTimer)
                    {
                        $this->coverTimer->cancel();
                        $this->coverTimer = null;
                    }
                    $this->coverTimer = Timer::after(2500, function () {
                        $randCover = rand(1, 5);
                        $this->GameEnemy->playSound("res://.data/audio/fight/cover_sounds/enemy/cover_fire_{$randCover}.mp3", 900);
                        $this->coverTimer = null;
                    });
                }
            }
        }
        else
        {
            $this->health_static_enemy->graphic = new UXImageView(new UXImage('res://.data/ui/maingame/skull_new.png'));
            $this->health_bar_enemy->hide();
            $this->health_bar_enemy_b->hide();
            $this->Talk_Label->hide();
    
            if ($spawnParticles)
            {
                $this->Particles->bloodConeAtTarget($this->GameEnemy->GetModel());
            }

            if ($GLOBALS['AllSounds'])
            {
                $randEbanul = rand(0, 5);
                $this->form('Client')->playSoundAsync("res://.data/audio/fight/hit_sounds/kulak_ebanul/kulak_ebanul_{$randEbanul}.mp3", true, 'hit_enemy_damage');
                
                $randDie = rand(1, 7);
                $this->form('Client')->playSoundAsync("res://.data/audio/fight/death_sounds/enemy/death_{$randDie}.mp3", true, 'die_enemy');
            }
    
            $this->GameEnemy->death();
            //$GLOBALS['EnemyFailed'] = true;
            $this->finalizeBattle();
            return;
        }                    
    }
    
    public $lastHitTime = 0;
    public $hitmarkLevel = 1;
    public $hitmarkVisibleUntil = 0;
       
    /**
     * @event actor.click-2x
     */    
    function DamageActor(UXMouseEvent $e = null)
    { 
        if (!$this->GameActor->CanInteractive()) 
        {
            return;
        }            
        $minWidth       = 54;
        $maxWidthMain   = 264;
        $maxWidthInv    = 416;
        $missChance     = 75;
        $damageMinPct   = 8;
        $damageMaxPct   = 20;
    
        if ($this->health_bar_gg->width != $minWidth)
        {
            if (!$GLOBALS['GodMode'])
            {
                $didMiss = rand(1, 100) <= $missChance;
    
                if (!$didMiss)
                {
                    $currentW = $this->health_bar_gg->width;
                    $currentPct = round((($currentW - $minWidth) / ($maxWidthMain - $minWidth)) * 99) + 1;
                    if ($currentPct < 1)   $currentPct = 1;
                    if ($currentPct > 100) $currentPct = 100;
    
                    $dmgPct = rand($damageMinPct, $damageMaxPct);
    
                    $newPct = max(1, $currentPct - $dmgPct);
    
                    $targetMain = (int) round($minWidth + (($maxWidthMain - $minWidth) * ($newPct - 1) / 99));
                    $targetInv  = (int) round($minWidth + (($maxWidthInv  - $minWidth) * ($newPct - 1) / 99));
    
                    $this->form('Client')->animateResizeWidth($this->health_bar_gg, $targetMain, 3, function() {
                        $minW = 54; $maxW = 264;
                        $cur = $this->health_bar_gg->width;
                        $pct = round((($cur - $minW) / ($maxW - $minW)) * 99) + 1;
                        if ($pct < 1)   $pct = 1;
                        if ($pct > 100) $pct = 100;
                        $this->health_bar_gg->text = $pct . "%";
                        $this->Bleeding();
                    });
    
                    $invBar = $this->form('Client')->Inventory->content->health_bar_gg;
                    $invBar->width = $targetInv;
                    $invPct = round((($targetInv - $minWidth) / ($maxWidthInv - $minWidth)) * 99) + 1;
                    $invPct = max(1, min(100, $invPct));
                    $invBar->text = $invPct . "%";
                }
            }
            
            $now = Time::millis();
            $timeDiff = $now - $this->lastHitTime;
            $this->lastHitTime = $now;
    
            if ($timeDiff < 500)
            {
                if ($this->hitmarkLevel < 4)
                {
                    $this->hitmarkLevel++;
                }
            }
    
            Timer::after(1500, function () {
                $sinceLastHit = Time::millis() - $this->lastHitTime;
                if ($sinceLastHit >= 1500 && $this->hitmarkLevel > 1)
                {
                    $this->hitmarkLevel--;
                }
            });
    
            switch ($this->hitmarkLevel)
            {
                case 1: $this->HitMark->image = new UXImage("res://.data/ui/maingame/hitmark/hitmark_1.png"); break;
                case 2: $this->HitMark->image = new UXImage("res://.data/ui/maingame/hitmark/hitmark_2.png"); break;
                case 3: $this->HitMark->image = new UXImage("res://.data/ui/maingame/hitmark/hitmark_3.png"); break;
                case 4: $this->HitMark->image = new UXImage("res://.data/ui/maingame/hitmark/hitmark_4.png"); break;
            }
    
            $this->HitMark->opacity = 0;
            $this->HitMark->visible = true;
            Animation::fadeIn($this->HitMark, 100);
            $this->hitmarkVisibleUntil = Time::millis() + 500;
    
            Timer::after(500, function () {
                if (Time::millis() >= $this->hitmarkVisibleUntil)
                {
                    Animation::fadeOut($this->HitMark, 300);
                    Timer::after(300, function () { $this->hitmarkLevel = 1; });
                }
            });
            
            $actor  = $this->GameActor->GetModel();
                    
            $localX = $e->x;
            $localY = $e->y;                    
                    
            $originX = $actor->x + $localX;
            $originY = $actor->y + $localY;                    
            
            $floorY = $actor->y + $actor->height - 20;

            $this->Particles->bloodBurstAtPoint($originX, $originY, $floorY, 4, 7);    
                    
            if ($GLOBALS['AllSounds'])
            {
                $randEbanul = rand(0, 5);            
                $this->form('Client')->playSoundAsync("res://.data/audio/fight/hit_sounds/kulak_ebanul/kulak_ebanul_{$randEbanul}.mp3", true, 'hit_actor_damage');
                         
                $playHitChance = 90;
                if (rand(1, 100) <= $playHitChance)
                {                            
                    $randHit = rand(1, 3);
                    $this->form('Client')->playSoundAsync("res://.data/audio/fight/hit_sounds/actor/hit_{$randHit}.mp3", true, 'hit_actor');
                }
                
                $playCoverChance = 40;
                if (rand(1, 100) <= 20)
                {
                    if ($this->coverTimer)
                    {
                        $this->coverTimer->cancel();
                        $this->coverTimer = null;
                    }
                    $this->coverTimer = Timer::after(2500, function () {
                        $randCover = rand(1, 2);
                        $this->GameActor->playSound("res://.data/audio/fight/cover_sounds/actor/cover_fire_{$randCover}.mp3", 900);    
                        $this->coverTimer = null;
                    });
                }               
            }
        }
        else
        {
            $this->health_static_gg->graphic = new UXImageView(new UXImage('res://.data/ui/maingame/skull_new.png'));
            $this->health_bar_gg->hide();
            $this->health_bar_gg_b->hide();
            $this->Bleeding();
            $this->form('Client')->Inventory->content->health_bar_gg->hide();
            $this->form('Client')->Inventory->content->health_bar_gg_b->hide();
            $this->form('Client')->Inventory->content->health_static_gg->graphic = new UXImageView(new UXImage('res://.data/ui/maingame/skull_new.png'));
            $this->Talk_Label->hide();
    
            if ($this->blood_ui->visible) $this->blood_ui->hide();
            if ($this->HitMark->visible)  $this->HitMark->hide();
    
            $this->Particles->bloodConeAtTarget($this->GameActor->GetModel());
    
            if ($GLOBALS['AllSounds'])
            {
                $randEbanul = rand(0, 5);
                $this->form('Client')->playSoundAsync("res://.data/audio/fight/hit_sounds/kulak_ebanul/kulak_ebanul_{$randEbanul}.mp3", true, 'hit_actor_damage');

                $randDie = rand(1, 4);
                $this->form('Client')->playSoundAsync("res://.data/audio/fight/death_sounds/actor/death_{$randDie}.mp3", true, 'die_actor');                
            }
            
            $this->GameActor->death();
            //$GLOBALS['ActorFailed'] = true;
            $this->finalizeBattle();
            return;
        }
    }
   
    function Bleeding()
    {
        $minHPWidth = 54;
        $maxHPWidth = 264;
        
        $curW = $this->health_bar_gg->width;
    
        if ($curW >= $maxHPWidth)
        {
            $this->blood_ui->hide();
            return;
        }
    
        if ($this->GameActor->isDead())
        {
            $this->blood_ui->hide();
            return;
        }
        else
        {
            $this->blood_ui->show();
        }
    
        $hpPercent = round((($curW - $minHPWidth) / ($maxHPWidth - $minHPWidth)) * 100);
        $hpPercent = max(1, min(100, $hpPercent));
    
        if ($hpPercent >= 60)
        {
            $this->blood_ui->image = new UXImage('res://.data/ui/maingame/blood_mini.png');
        }
        elseif ($hpPercent >= 30)
        {
            $this->blood_ui->image = new UXImage('res://.data/ui/maingame/blood_medium.png');
        }
        else
        {
            $this->blood_ui->image = new UXImage('res://.data/ui/maingame/blood_ultra.png');
        }       
    }
    function finalizeBattle()
    {
        $GLOBALS['NeedToCheckPDA'] = true;
        
        $this->form('Client')->Pda->content->Pda_Statistic->content->UpdateFinalLabel();
    
        $this->fight_image->hide();
        $this->fight_image->blinkAnim->disable();
        
        $this->leave_btn->show();
                
        //if ($GLOBALS['ActorFailed']) $this->GameActor->GetModel()->hide();
        //if ($GLOBALS['EnemyFailed']) $this->GameEnemy->GetModel()->hide();     
        
        $this->ItemVodka->disable();
        $this->ItemVodka->setOpacity(0);
        $this->ItemVodka->hide();
        
       // $this->GameActor->SetInteractive(false);
       // $this->GameEnemy->SetInteractive(false);
        
        if ($GLOBALS['AllSounds']) $this->form('Client')->StopAllSoundsAsync();
        
        if ($this->GameActor->isDead())
        {
            $this->GameEnemy->SetInteractive(false);
            
            if ($this->currentWeapon) $this->UnequipCurrentWeapon();
              
            $this->form('Client')->Pda->content->Pda_Tasks->content->Step2_Failed();
            
            if ($GLOBALS['AllSounds']) $this->form('Client')->playSoundAsync('res://.data/audio/victory/victory_alex.mp3', true, 'v_enemy');
        }
        if ($this->GameEnemy->isDead())
        {
            $this->GameActor->SetInteractive(false);
            
            $this->form('Client')->Pda->content->Pda_Tasks->content->Step2_Complete();
            
            if ($GLOBALS['AllSounds']) $this->form('Client')->playSoundAsync('res://.data/audio/victory/victory_actor.mp3', true, 'v_actor');
        }
        
        $this->form('Client')->Pda->content->Pda_Tasks->content->Step_UpdatePda();
        
        $this->form('Client')->Pda->content->Pda_Statistic->content->UpdateRaiting();
        
        if ($this->form('Client')->ltx['discord_rpc'] == 'on')
        {        
            $GLOBALS['discord']->setState(null);
            $GLOBALS['discord']->updateState();    
        }    
    }
    protected $isHovered = false;
    protected $isLabelVisible = false;    
    /**
     * @event enemy.mouseEnter
     */
    function EnemyHoverEnter(UXMouseEvent $e = null)
    {
        if ($GLOBALS['QuestStep1']) return;
        
        $this->isHovered = true;

        Timer::after(300, function () {
            if ($this->isHovered && !$this->isLabelVisible)
            {
                $this->Talk_Label->opacity = 0;
                $this->Talk_Label->visible = true;
                Animation::fadeIn($this->Talk_Label, 300);
                $this->isLabelVisible = true;
            }
        });
    }
    /**
     * @event enemy.mouseExit
     */
    function EnemyHoverExit(UXMouseEvent $e = null)
    {
        $this->isHovered = false;

        if ($this->isLabelVisible)
        {
            Animation::fadeOut($this->Talk_Label, 300, function () {
                $this->Talk_Label->visible = false;
                $this->isLabelVisible = false;
            });
        }
    }    
    function ShowTaskStep()
    {
        $this->Task_Step_Label->visible = true;      
        
        Timer::after(4000, function () {
            UXApplication::runLater(function () {
                $this->Task_Step_Label->visible = false;

                if ($GLOBALS['QuestStep1'] && !$GLOBALS['QuestCompleted'])
                {
                    $this->fight_image->opacity = 0;
                    $this->fight_image->visible = true;
                    Animation::fadeIn($this->fight_image, 400);
                    $this->fight_image->blinkAnim->enable();
                }

                if ($GLOBALS['QuestCompleted'])
                {
                    $this->localization->setLanguage($this->getCurrentLanguageFromUI());
                    $this->Task_Step_Label->text = $this->localization->get('No_Active_Task');
                }
            });
        });        
    }
    
    function ShowMessageBox()
    {
        $this->MessageBox->opacity = 1;
        $this->MessageBox->visible = true;
        $this->MessageBox->content->UpdateMessageBox();

        Timer::after(3000, function () {
            Animation::fadeOut($this->MessageBox, 500);
        });
    }  
    
    public function UnequipCurrentWeapon(): void
    {
        if ($this->currentWeapon)
        {
            $this->weaponState[$this->currentWeapon->getType()] = $this->currentWeapon->exportState();
            $this->currentWeapon->detach();
            $this->currentWeapon = null;
            $this->UpdateMagazine();
        }
    }
    
    public function SwitchWeapon(?string $weaponType): void
    {
        if (!$this->GameActor->GetModel()->visible) return;
        if ($weaponType === null) { $this->UnequipCurrentWeapon(); return; }
        if ($this->currentWeapon && $this->currentWeapon->getType() === $weaponType) return;
    
        $inv = $this->form('Client')->Inventory->content->InventoryGrid->content;
        $flag = ($weaponType === 'Pm') ? 'pmInWeaponSlot' : (($weaponType === 'AK74') ? 'AK74InWeaponSlot' : null);
        if (!$flag || empty($inv->$flag)) return;
    
        if ($this->currentWeapon) { $this->UnequipCurrentWeapon(); }
    
        $weapon = CWeaponFactory::create($weaponType, $this);
        if (!$weapon)
        {
            Debug::fail("Weapon '$weaponType' not created", __FILE__, __LINE__);
            return;
        }
    
        if (isset($this->weaponState[$weaponType]))
        {
            $weapon->importState($this->weaponState[$weaponType]);
        }
    
        $weapon->attach();
        $this->currentWeapon = $weapon;
        if (isset($GLOBALS['ShadowsSwitcher_IsOn']) && !$GLOBALS['ShadowsSwitcher_IsOn'])
        {
            $this->currentWeapon->disableShadow();
        }
        else
        {
            $this->currentWeapon->enableShadow();
        }        
        
        $this->UpdateMagazine();
    }
    
    public function Shoot(): void
    {
        if (empty($GLOBALS['QuestStep1'])) return;
        if (!$this->currentWeapon) return;
        $this->currentWeapon->shoot();
    }
    
    public function ReloadWeapon(): void
    {
        if (!$this->currentWeapon) return;
        $this->currentWeapon->reload();
    }
    
    public function UpdateMagazine(): void
    {
        $this->ui_mag_background->hide();
        $this->ui_mag_background->text = null;
        $this->ui_mag_background->graphic = null;
        
        $this->GodMode();        
    
        if (!$this->currentWeapon) return;
    
        if (!empty($GLOBALS['HudVisible'])) $this->ui_mag_background->show();
    
        $imgPath = $this->currentWeapon->hudMagImage();
        $this->ui_mag_background->graphic = new UXImageView(new UXImage($imgPath));
    
        $currentAmmo = $this->currentWeapon->getAmmo();
        $totalAmmo   = $this->currentWeapon->getTotalAmmoFromInventory();
        $this->ui_mag_background->text = $currentAmmo . '/' . $totalAmmo;
    }
    
    public function showJamHintUI(string $textKey): void
    {
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
    
        $lbl = $this->Task_Step_Label;
        $prev = $lbl->text ?? null;
    
        $lbl->visible = true;
        $lbl->text = $this->localization->get($textKey);
    
        Timer::after(4000, function () use ($prev) {
            UXApplication::runLater(function () use ($prev){
                $this->Task_Step_Label->visible = false;
                if ($prev !== null)
                {
                    $this->Task_Step_Label->text = $prev;
                }
            });
        });
    }
}
