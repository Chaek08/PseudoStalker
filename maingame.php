<?php
namespace app\forms;

use Throwable;
use app\forms\ui_test;
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

class maingame extends AbstractForm
{
    private $currentCycle = '';
    private $localization;
    
    public $SDK_FightSound;
    public $SDK_ActorModel;
    public $SDK_EnemyModel;    


    public $GameActor;
    //weapons
    public $WeaponDev;

    public function __construct() 
    {
        parent::__construct();

        $this->localization = new Localization($language);
        
        $this->GameActor = new CActor();
        $this->GameActor->SetModel($this->actor);
        
        $this->GameActor->SetInteractive(false);
        //
        $this->WeaponDev = new CWeapon_Dev();
        
        //wip
        $this->GameActor->SetActiveWeapon($this->WeaponDev);
    }
    function getCurrentLanguageFromUI()
    {
        return $this->form('Client')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value;
    }     
    
    function InitEnvironmentTimer($timeFromTasks = null)
    {    
        if (defined('UseLegacyEnvironment') && UseLegacyEnvironment == true)
        {
            $this->platform->show();
            $this->Environment_Background->hide();
            return;
        }
        
        $this->EnvironmentTimer->stop();    
    
        if ($timeFromTasks != null)
        {
            //Logger::info("Timer not started (custom time used: $timeFromTasks)");
            $this->UpdateEnvironment($timeFromTasks);
            return;
        }
        
        $this->EnvironmentTimer->on("action", function()
        {
            $this->UpdateEnvironment();
            //Logger::info("Timer updated: " . Time::now()->toString('HH:mm:ss'));
        });
        
        $this->EnvironmentTimer->start();
        
    }    
    function UpdateEnvironment($timeFromTasks = null)
    {        
        if (defined('UseLegacyEnvironment') && UseLegacyEnvironment == true)
        {
            return;
        }
        
        $this->Environment->view = $this->Environment_Background;

        $timeStr = $timeFromTasks ?? Time::now()->toString('HH:mm');
        $newCycle = $this->getTimeCycleByString($timeStr);

        $backgroundPaths = [
            'morning' => "./gamedata/textures/environment/morning.mp4",
            'day' => "./gamedata/textures/environment/day.mp4",
            'evening' => "./gamedata/textures/environment/evening.mp4",
            'night' => "./gamedata/textures/environment/night.mp4"
        ];

        $brightnessByCycle = [
            'morning' => -0.1,
            'day' => 0.0,
            'evening' => -0.2,
            'night' => -0.4
        ];

        if ($newCycle != $this->currentCycle)
        {
            if ($this->currentCycle == '')
            {
                Logger::info("Set cycle: " . $newCycle);
            }
            else
            {
                Logger::info("Cycle changed: " . $this->currentCycle . " -> " . $newCycle);
            }

            $this->currentCycle = $newCycle;

            Media::stop($this->Environment);

            $backgroundPath = $backgroundPaths[$newCycle];
            Media::open($backgroundPath, false, $this->Environment);

            $brightness = $brightnessByCycle[$newCycle];
            $this->GameActor->GetModel()->colorAdjustEffect->brightness = $brightness;
            $this->enemy->colorAdjustEffect->brightness = $brightness;
            $this->item_vodka_0000->colorAdjustEffect->brightness = $brightness;       
        }
        if (!$this->form('Client')->MainMenu->visible)
        {
            $this->PlayEnvironment();
        }
    }
    function PlayEnvironment()
    {
        Media::play($this->Environment);
        
        if ($GLOBALS['AllSounds'])
        {
            $this->Environment->volume = 65;
        }
    }
    function getTimeCycleByString($timeStr)
    {
        $hourStr = substr($timeStr, 0, 2);
        $hour = (int)$hourStr;

        if ($hour >= 5 && $hour < 11)
        {
            return 'morning';
        }
        elseif ($hour >= 11 && $hour < 18)
        {
            return 'day';
        }
        elseif ($hour >= 18 && $hour < 21)
        {
            return 'evening';
        }
        else
        {
            return 'night';
        }
    }    
    function PlayFightSong()
    {    
        if ($GLOBALS['AllSounds'] || $GLOBALS['FightSound'])
        {
            $path = trim($this->SDK_FightSound);

            if ($path != '')
            {
                Media::open($path, true, $this->FightSound);
            } 
            else
            {
                Media::open('res://.data/audio/fight/fight_sound.mp3', true, $this->FightSound);
            }
        }
    }      
    function ResetGameClient(callable $afterReset = null)
    {
        $this->form('Client')->ShowLoadScreen(function () use ($afterReset)
        {
            if ($GLOBALS['QuestStep1']) $GLOBALS['QuestStep1'] = false;
            if ($GLOBALS['QuestCompleted']) $GLOBALS['QuestCompleted'] = false;
            if ($GLOBALS['ActorFailed']) $GLOBALS['ActorFailed'] = false;
            if ($GLOBALS['EnemyFailed']) $GLOBALS['EnemyFailed'] = false;

            if ($GLOBALS['AllSounds']) $this->form('Client')->StopAllSoundsAsync();
            Media::stop($this->Environment);

            if ($this->fight_image->visible) $this->fight_image->hide();
            if ($this->leave_btn->visible || !$GLOBALS['QuestCompleted']) $this->leave_btn->hide();
            if ($this->form('Client')->Fail->visible) $this->form('Client')->Fail->hide();
            if ($this->blood_ui->visible) $this->blood_ui->hide();

            $this->form('Client')->Inventory->content->DespawnItems();
            $this->form('Client')->Inventory->content->SetItemCondition();
            
            //$this->form('Client')->Inventory->content->InventoryGrid->content->lockInventory(false);
            
            $this->ak74Ammo = 30;
            $this->pmAmmo = 8;
            
            foreach ($this->weaponData as &$data)
            {
                $data['jammed'] = false;
                $data['jamHandled'] = false;
            }
            
            $this->form('Client')->Inventory->content->InventoryGrid->content->MoveWeaponsToInvSlot();
           
            $this->GameActor->GetModel()->show();
            $this->enemy->show();
            $this->enemy->x = 1312;

            $this->GameActor->GetModel()->x = 112;
            $this->GameActor->SetInteractive(false);
            
            $this->item_vodka_0000->enabled = false;

            $this->form('Client')->Pda->content->DefaultState();
            $this->form('Client')->Pda->content->Pda_Contacts->content->UpdateContacts();
            $this->form('Client')->Pda->content->Pda_Tasks->content->UpdateQuestTime();
            $this->form('Client')->Pda->content->Pda_Tasks->content->DeleteTask();
            $this->form('Client')->Pda->content->Pda_Tasks->content->ShowActiveTasks();
            $this->form('Client')->Pda->content->Pda_Tasks->content->StepReset();
            $this->form('Client')->Pda->content->Pda_Tasks->content->Step_DeletePda();
            $this->form('Client')->Pda->content->Pda_Ranking->content->DeathFilter();
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
            else
            {
                $this->PlayEnvironment();
            }
            
            $this->InitEnvironmentTimer();
            $this->UpdateEnvironment();            

            $this->form('Client')->Dialog->content->StartDialog();
            
            $GLOBALS['discord']->setState(null);
            $GLOBALS['discord']->updateState();             
            
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
            if (!$GLOBALS['ActorFailed']) 
            {
                $this->health_bar_gg->show();
                $this->health_bar_gg_b->show();
            }
            $this->health_static_enemy->show();
            if (!$GLOBALS['EnemyFailed']) 
            {
                $this->health_bar_enemy->show();
                $this->health_bar_enemy_b->show();
            }

            $this->Bleeding();
            
            if ($this->CurrentWeaponType) $this->ui_mag_background->show();
            if ($GLOBALS['NeedToCheckPDA']) $this->pda_icon->show();
            if ($GLOBALS['GodMode']) $this->GodMode_Icon->show();
            if ($this->GameActor->CanInteractive()) $this->fight_image->show();
            if ($GLOBALS['ActorFailed'] || $GLOBALS['EnemyFailed']) $this->leave_btn->show();
        
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

            if ($this->blood_ui->visible) $this->blood_ui->hide();
            if ($this->GodMode_Icon->visible) $this->GodMode_Icon->hide();
            if ($this->pda_icon->visible) $this->pda_icon->hide();
            if ($this->fight_image->visible) $this->fight_image->hide();
            if ($this->SavedGame_Toast->visible) $this->SavedGame_Toast->hide();
            if ($this->leave_btn->visible) $this->leave_btn->hide();
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
        
        $this->form('Client')->Fail->show();
        
        if ($this->CurrentWeaponType == 'Pm')
        {
            $this->WeaponPm->hide();
        }
        if ($this->CurrentWeaponType == 'AK74')
        {
            $this->WeaponAK74->hide();
        }
        
        if ($this->item_vodka_0000->visible) $this->item_vodka_0000->hide();
        if ($GLOBALS['ActorFailed']) $this->enemy->hide();
        if ($GLOBALS['EnemyFailed']) $this->GameActor->GetModel()->hide();
    }
    function SpawnItem()
    {
        $actor = $this->GameActor->GetModel();
        $vodka = $this->item_vodka_0000;

        $floorOffset = -10;

        $spawnX = $actor->x + ($actor->width * 1.2);
        $spawnY = $actor->y + $actor->height - $vodka->height + $floorOffset;

        $vodka->x = $spawnX;
        $vodka->y = $spawnY;
        $vodka->opacity = 100;
        $vodka->show();
    }  
    /**
     * @event item_vodka_0000.click-2x
     */
    function VodkaAttack(UXMouseEvent $e = null)
    {
        $vodka = $this->item_vodka_0000;
        $enemy = $this->enemy;

        $targetX = $enemy->x + ($enemy->width / 2) - ($vodka->width / 2);
        $targetY_Head = $enemy->y;

        $floorOffset = -10;
        $floorY = $enemy->y + $enemy->height - $vodka->height + $floorOffset;

        $startX = $vodka->x;
        $startY = $vodka->y;

        $dx = $targetX - $startX;
        $dy = $targetY_Head - $startY;

        $distance = sqrt($dx * $dx + $dy * $dy);

        $speed = 0.9;
        $duration = (int)($distance / $speed);

        $initialEnemyX = $enemy->x;
        $initialEnemyY = $enemy->y;

        Animation::moveTo($vodka, $duration, $targetX, $targetY_Head, function () use ($vodka, $enemy, $floorY, $initialEnemyX, $initialEnemyY) {
            $enemyStillHere = $enemy->x === $initialEnemyX && $enemy->y === $initialEnemyY;

            if ($enemyStillHere && Geometry::intersect($vodka, $enemy))
            {
                for ($i = 0; $i < rand(2, 4); $i++)
                {
                    $scatterX = rand(-25, 25);
                    $scatterY = rand(-25, 25);

                    $particle = new UXImageView();
                    $particle->image = new UXImage("res://.data/ui/particles/blood.png");
                    $particle->scale = $this->form('Client')->MainGame->scale;
                    $particle->width = 86;
                    $particle->height = 86;

                    $hitX = $enemy->x + ($enemy->width / 2) - ($particle->width / 2);
                    $hitY = $enemy->y - 10;

                    $particle->x = $hitX + $scatterX;
                    $particle->y = $hitY + $scatterY;
                    $particle->opacity = 1.0;

                    $this->add($particle);

                    Animation::fadeOut($particle, 300, function () use ($particle) {
                        $particle->free();
                    });
                } 
            
                $this->DamageEnemy(null, false);

                Animation::displace($vodka, 300, -150, -10, function () use ($vodka, $floorY) {
                    Animation::moveTo($vodka, 300, $vodka->x, $floorY);
                });
            }
            else
            {
                Animation::moveTo($vodka, 400, $vodka->x, $floorY);
            }
        });
    }
    /**
     * @event item_vodka_0000.click-Right 
     */
    function VodkaDraggingEnable(UXMouseEvent $e = null)
    {    
        $vodka = $this->item_vodka_0000;
        $actor = $this->GameActor->GetModel();

        $targetX = $actor->x + ($actor->width * 1.2) - ($vodka->width / 2);
        $targetY = $vodka->y;

        $dx = $targetX - $vodka->x;
        $dy = 0;
        $distance = sqrt($dx * $dx + $dy * $dy);

        $speed = 1.3;
        $duration = (int)($distance / $speed);

        Animation::moveTo($vodka, $duration, $targetX, $targetY);
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
        if (!$GLOBALS['ActorFailed'])
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
        if (!$GLOBALS['EnemyFailed'])
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

        if ($this->WeaponPm || $this->WeaponAK74)
        {
            $this->ui_mag_background->y = $baseY;
            $nextY = $baseY + 64;
        }
        else
        {
            $nextY = $baseY;
        }

        if ($GLOBALS['GodMode'])
        {
            $this->GodMode_Icon->show();
            $this->GodMode_Icon->y = $nextY;
            $nextY += 64;
        }
        else
        {
            $this->GodMode_Icon->hide();
        }

        $this->blood_ui->y = $nextY;
    }
    function SpawnParticle($target)
    {
        if ($target == $this->GameActor->GetModel())
        {
            $healthBar = $this->health_bar_actor;
        }
        else 
        {
            $healthBar = $this->health_bar_enemy;
        }    
    
        $cursorX = $this->form('Client')->CustomCursor->x;
        $cursorY = $this->form('Client')->CustomCursor->y;
    
        $bloodCount = rand(4, 6);
    
        array_map(function() use ($cursorX, $cursorY, $healthBar) {
            $scatterX = rand(-35, 35);
            $scatterY = rand(-35, 35);
    
            $this->spawnParticleAsync(
                function() use ($cursorX, $cursorY, $scatterX, $scatterY)
                {
                    $particle = new UXImageView();
                    $particle->enabled = false;
                    $particle->opacity = 1;
                    $particle->image = new UXImage("res://.data/ui/particles/blood.png");
                    $particle->scale = $this->form('Client')->MainGame->scale;
                    $particle->width = 86;
                    $particle->height = 86;
    
                    $particle->x = $cursorX - ($particle->width / 2) + $scatterX;
                    $particle->y = $cursorY - ($particle->height / 2) + $scatterY;
    
                    return $particle;
                },
                function($particle) use ($healthBar)
                {
                    $delay = ($healthBar->width - 30 <= 54) ? 600 : 300;
    
                    Timer::after($delay, function () use ($particle) {
                        Animation::fadeOut($particle, 300, function () use ($particle) {
                            $particle->free();
                        });
                    });
                },
                true
            );
        }, range(1, $bloodCount));
    }
   
    /**
     * @event enemy.click-2x
     */       
    function DamageEnemy(UXMouseEvent $e = null, bool $spawnParticles = true)
    { 
        if (!$this->GameActor->CanInteractive()) 
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
                $this->SpawnParticle($enemy); 
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
                
                $playCoverChance = 35;
                if (rand(1, 100) <= $playCoverChance)
                {            
                    Timer::after(2500, function() use ($randCover) {
                        $randCover = rand(1, 5);
                        $this->form('Client')->playSoundAsync("res://.data/audio/fight/cover_sounds/enemy/cover_fire_{$randCover}.mp3", true, 'hit_cover_enemy');
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
                $this->SpawnParticle($enemy); 
            }
    
            if ($GLOBALS['AllSounds'])
            {
                $randEbanul = rand(0, 5);
                $this->form('Client')->playSoundAsync("res://.data/audio/fight/hit_sounds/kulak_ebanul/kulak_ebanul_{$randEbanul}.mp3", true, 'hit_enemy_damage');
                
                $randDie = rand(1, 7);
                $this->form('Client')->playSoundAsync("res://.data/audio/fight/death_sounds/enemy/death_{$randDie}.mp3", true, 'die_enemy');
            }
    
            $GLOBALS['EnemyFailed'] = true;
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
    
            $this->SpawnParticle($actor);
    
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
                
                $playCoverChance = 35;
                if (rand(1, 100) <= $playCoverChance)
                {            
                    Timer::after(1500, function() use ($randCover) {
                        $randCover = rand(1, 5);
                        $this->form('Client')->playSoundAsync("res://.data/audio/fight/cover_sounds/actor/cover_fire_{$randCover}.mp3", true, 'hit_cover_actor');
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
    
            $this->SpawnParticle($actor);
    
            if ($GLOBALS['AllSounds'])
            {
                $randEbanul = rand(0, 5);
                $this->form('Client')->playSoundAsync("res://.data/audio/fight/hit_sounds/kulak_ebanul/kulak_ebanul_{$randEbanul}.mp3", true, 'hit_actor_damage');

                $randDie = rand(1, 4);
                $this->form('Client')->playSoundAsync("res://.data/audio/fight/death_sounds/actor/death_{$randDie}.mp3", true, 'die_actor');                
            }
    
            $GLOBALS['ActorFailed'] = true;
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
    
        if ($GLOBALS['ActorFailed'])
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
        
        $this->form('Client')->Fail->content->UpdateFailState();
        $this->form('Client')->Pda->content->Pda_Statistic->content->UpdateFinalLabel();
    
        $this->fight_image->hide();
        $this->fight_image->blinkAnim->disable();
        $this->leave_btn->show();
        
        if ($GLOBALS['ActorFailed']) $this->GameActor->GetModel()->hide();
        if ($GLOBALS['EnemyFailed']) $this->enemy->hide();       
        
        //$this->form('Client')->Inventory->content->InventoryGrid->content->lockInventory(true);
        
        $this->item_vodka_0000->enabled = false;
        $this->item_vodka_0000->opacity = 0;
        
        //$this->GameActor->ToggleInteractive(false);
        $this->GameActor->SetInteractive(false);
        $this->enemy->enabled = false; //для него может быть потом отдельный класс, подобный CActor
        
        if ($GLOBALS['AllSounds']) $this->form('Client')->StopAllSoundsAsync();
        
        if ($GLOBALS['ActorFailed'])
        {
            $this->GameActor->GetModel()->hide();
            if ($this->CurrentWeaponType == 'Pm')
            {
                $this->WeaponPm->hide();
            }
            if ($this->CurrentWeaponType == 'AK74')
            {
                $this->WeaponAK74->hide();
            }      
              
            $this->form('Client')->Pda->content->Pda_Tasks->content->Step2_Failed();
            
            if ($GLOBALS['AllSounds']) $this->form('Client')->playSoundAsync('res://.data/audio/victory/victory_alex.mp3', true, 'v_enemy');
        }
        if ($GLOBALS['EnemyFailed'])
        {
            $this->enemy->hide();
            
            $this->form('Client')->Pda->content->Pda_Tasks->content->Step2_Complete();
            
            if ($GLOBALS['AllSounds']) $this->form('Client')->playSoundAsync('res://.data/audio/victory/victory_actor.mp3', true, 'v_actor');
        }
        
        $this->form('Client')->Pda->content->Pda_Tasks->content->Step_UpdatePda();
        
        $this->form('Client')->Pda->content->Pda_Statistic->content->UpdateRaiting();
        
        $GLOBALS['discord']->setState(null);
        $GLOBALS['discord']->updateState();        
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
    
    public $WeaponPm;
    public $WeaponAK74;
    
    public $pmAmmo = 8;
    public $ak74Ammo = 30;
    
    private $tempTaskStep;
     
    private $weaponData = [
        'Pm' => [
            'ammoProp' => 'pmAmmo',
            'maxAmmo' => 8,
            'soundShot' => 'res://.data/audio/weapon/t_pm_shot.mp3',
            'soundEmpty' => 'res://.data/audio/weapon/pistol_empty.mp3',
            'particleOffset' => [158, 93],
            'jammed' => false,
            'jamHandled' => false,
        ],
        'AK74' => [
            'ammoProp' => 'ak74Ammo',
            'maxAmmo' => 30,
            'soundShot' => 'res://.data/audio/weapon/ak74_shot_0.mp3',
            'soundEmpty' => 'res://.data/audio/weapon/gen_empty.mp3',
            'particleOffset' => [256, 96],
            'jammed' => false,
            'jamHandled' => false,
        ],
    ];    
    
    function spawnParticleAsync(callable $factory, callable $afterAdd = null, bool $toClient = false)
    {
        (new Thread(function() use ($factory, $afterAdd, $toClient) {
            $particle = $factory();
    
            UXApplication::runLater(function() use ($particle, $afterAdd, $toClient) {
                if ($toClient)
                {
                    $this->form('Client')->add($particle);
                }
                else
                {
                    $this->add($particle);
                }
    
                if ($afterAdd)
                {
                    $afterAdd($particle);
                }
            });
        }))->start();
    }


    function Shoot()
    {
        if (!$GLOBALS['QuestStep1']) return;
    
        if (!$this->CurrentWeaponType || $this->isReloading)
        {
            return;
        }
    
        $weaponType = $this->CurrentWeaponType;
    
        if (!isset($this->weaponData[$weaponType]))
        {
            return;
        }
    
        $data = &$this->weaponData[$weaponType];
        $ammoProp = $data['ammoProp'];
    
        if ($this->$ammoProp < $data['maxAmmo'] && rand(1, 60) == 1)
        {
            $data['jammed'] = true;
        }
    
        if ($data['jammed'] && !$data['jamHandled'])
        {
            $data['jamHandled'] = true;
    
            if ($GLOBALS['AllSounds'])
            {
                $this->form('Client')->playSoundAsync($data['soundEmpty'], true, strtolower($weaponType) . '_jam');
            }
    
            $this->tempTaskStep = $this->Task_Step_Label->text;
            $this->Task_Step_Label->visible = true;
    
            $this->localization->setLanguage($this->getCurrentLanguageFromUI());
            $this->Task_Step_Label->text = $this->localization->get('GunJmammed');
    
            Timer::after(4000, function () {
                UXApplication::runLater(function () {
                    $this->Task_Step_Label->visible = false;
                    $this->Task_Step_Label->text = $this->tempTaskStep;
                });
            });
            return;
        }
    
        if ($this->$ammoProp <= 0 || $data['jammed'])
        {
            if ($GLOBALS['AllSounds'])
            {
                $this->form('Client')->playSoundAsync($data['soundEmpty'], true, strtolower($weaponType) . '_empty');
            }
            return;
        }
    
        $this->$ammoProp--;
        $this->UpdateMagazine();
    
        UXApplication::runLater(function() use ($data) {
    
            if ($GLOBALS['AllSounds'])
            {
                $this->form('Client')->playSoundAsync($data['soundShot'], true, strtolower($this->CurrentWeaponType) . '_shot');
            }
    
            [$offsetX, $offsetY] = $data['particleOffset'];
            $this->spawnParticleAsync(
                function() use ($offsetX, $offsetY) {
                    $shootParticle = new UXImageView;
                    $shootParticle->image = new UXImage('res://.data/ui/particles/shoot.png');
                    $shootParticle->width = 128;
                    $shootParticle->height = 128;
                    $shootParticle->opacity = 1;
                    $shootParticle->x = $this->GameActor->GetModel()->x + $offsetX;
                    $shootParticle->y = $this->GameActor->GetModel()->y + $offsetY;
    
                    (new BloomEffectBehaviour())->apply($shootParticle);
    
                    return $shootParticle;
                },
                function($shootParticle) {
                    Animation::fadeOut($shootParticle, 150, function () use ($shootParticle) {
                        if ($shootParticle->parent) {
                            $shootParticle->parent->remove($shootParticle);
                        }
                        $shootParticle->free();
                    });
                }
            );
    
            $enemy = $this->enemy;
            if ($enemy->visible)
            {
                $this->DamageEnemy(null, false);
            
                $bloodCount = rand(4, 7);
            
                array_map(function() use ($enemy, $offsetX, $offsetY)
                {
                    $scatterX = rand(-35, 35);
                    $scatterY = rand(-35, 35);
            
                    $this->spawnParticleAsync(
                        function() use ($enemy, $scatterX, $scatterY, $offsetY)
                        {
                            $bloodParticle = new UXImageView();
                            $bloodParticle->image = new UXImage("res://.data/ui/particles/blood.png");
                            $bloodParticle->scale = $this->form('Client')->MainGame->scale;
                            $bloodParticle->width = 86;
                            $bloodParticle->height = 86;
            
                            $hitX = $enemy->x + ($enemy->width / 2) - ($bloodParticle->width / 2);
            
                            $hitY = $this->GameActor->GetModel()->y + $offsetY;
            
                            $bloodParticle->x = $hitX + $scatterX;
                            $bloodParticle->y = $hitY + $scatterY;
                            $bloodParticle->opacity = 1.0;
            
                            return $bloodParticle;
                        },
                        function($bloodParticle)
                        {
                            Animation::fadeOut($bloodParticle, 400, function () use ($bloodParticle) {
                                $bloodParticle->free();
                            });
                        }
                    );
                }, range(1, $bloodCount));
            }
        });
    }

    private $AttachmentTimer = null;
    function AttachWeapon(string $weaponType)
    {
        if ($this->AttachmentTimer)
        {
            $this->AttachmentTimer->cancel();
            $this->AttachmentTimer = null;
        }
    
        $weaponProperty = "Weapon$weaponType";
        $this->$weaponProperty = new UXImageView;
    
        switch ($weaponType)
        {
            case 'Pm':
                $this->$weaponProperty->image = new UXImage('res://.data/ui/weapons/wpn_pm.png');
                $offsetX = 112;
                $offsetY = 152;
                if ($GLOBALS['AllSounds'])
                {
                    $this->form('Client')->playSoundAsync('res://.data/audio/weapon/pm_draw.mp3', true, 'pm_draw');
                }
                break;
    
            case 'AK74':
                $this->$weaponProperty->image = new UXImage('res://.data/ui/weapons/wpn_ak74.png');
                $offsetX = 24;
                $offsetY = 144;
                if ($GLOBALS['AllSounds'])
                {
                    $this->form('Client')->playSoundAsync('res://.data/audio/weapon/ak74_draw.mp3', true, 'ak74_draw');
                }
                break;
    
            default:
                return;
        }
    
        $this->add($this->$weaponProperty);
    
        $colorAdjustEffect = new ColorAdjustEffectBehaviour();
        $colorAdjustEffect->brightness = $this->GameActor->GetModel()->colorAdjustEffect->brightness;
        $colorAdjustEffect->apply($this->$weaponProperty);
    
        $this->UpdateMagazine();
    
        $this->$weaponProperty->on('mouseDown', function(UXMouseEvent $e) {
            $this->Shoot();
        });
    
        $model = $this->GameActor->GetModel();
        $this->$weaponProperty->x = $model->x + $offsetX;
        $this->$weaponProperty->y = $model->y + $offsetY;
    
        $this->AttachmentTimer = Timer::every(1, function() use ($weaponProperty, $offsetX, $offsetY) {
            if (!empty($this->$weaponProperty) && $this->GameActor && $this->GameActor->GetModel())
            {
                $model = $this->GameActor->GetModel();
                $this->$weaponProperty->x = $model->x + $offsetX;
                $this->$weaponProperty->y = $model->y + $offsetY;
    
                if ($this->$weaponProperty->colorAdjustEffect)
                {
                    $this->$weaponProperty->colorAdjustEffect->brightness = $model->colorAdjustEffect->brightness;
                }
            }
        });
    }
    
    function DetachWeapon(string $weaponType)
    {
        if ($GLOBALS['AllSounds'])
        {
            $this->form('Client')->playSoundAsync('res://.data/audio/weapon/generic_close.mp3', true, 'generic_close');
        }
    
        if ($this->AttachmentTimer)
        {
            $this->AttachmentTimer->cancel();
            $this->AttachmentTimer = null;
        }
    
        $weaponProperty = "Weapon$weaponType";
        if (!empty($this->$weaponProperty))
        {
            $this->remove($this->$weaponProperty);
            $this->$weaponProperty = null;
        }
    
        $this->UpdateMagazine();
    }
    
    public $CurrentWeaponType;
    function SwitchWeapon(string $weaponType)
    {
        if ($this->CurrentWeaponType == $weaponType)
        {
            return;
        }    
        
        if ($GLOBALS['ActorFailed']) return;        
    
        $slotFlagMap = [
            'Pm' => 'pmInWeaponSlot',
            'AK74' => 'AK74InWeaponSlot',
        ];

        if (!isset($slotFlagMap[$weaponType]))
        {
            return;
        }

        $flagName = $slotFlagMap[$weaponType];
        $inv = $this->form('Client')->Inventory->content->InventoryGrid->content;

        if (empty($inv->$flagName))
        {
            return;
        }    

        if ($this->CurrentWeaponType)
        {
            $this->DetachWeapon($this->CurrentWeaponType);
            $this->CurrentWeaponType = null;
        }

        $this->AttachWeapon($weaponType);
        $this->CurrentWeaponType = $weaponType;
    }    
    
    private $isReloading = false;
    function ReloadWeapon()
    {
        if ($this->isReloading) return;
        if (!$this->CurrentWeaponType) return;
        
        if ($GLOBALS['ActorFailed']) return;        

        switch ($this->CurrentWeaponType)
        {
            case 'Pm':
                $this->ReloadActiveWeapon(
                    "Pm",
                    8,
                    "pmAmmo",
                    "pmAmmoCount",
                    "res://.data/audio/weapon/pm_reload.mp3",
                    2000
                );
                break;

            case 'AK74':
                $this->ReloadActiveWeapon(
                    "AK74",
                    30,
                    "ak74Ammo",
                    "akAmmoCount",
                    "res://.data/audio/weapon/ak74_reload.mp3",
                    1000
                );
                break;
        }
    }

    function ReloadActiveWeapon($weaponKey, $magSize, $ammoVar, $ammoCountField, $soundPath, $delay)
    {
        $inv = $this->form('Client')->Inventory->content->InventoryGrid->content;
        $totalAmmo = $inv->$ammoCountField;      

        $jammed     = $this->weaponData[$weaponKey]['jammed'] ?? false;
        $jamHandled = $this->weaponData[$weaponKey]['jamHandled'] ?? false;

        if ($this->$ammoVar >= $magSize && !$jammed) return;

        if ($totalAmmo <= 0 && !$jammed) return;

        if ($GLOBALS['AllSounds'])
        {
            $this->form('Client')->playSoundAsync($soundPath, true, $weaponKey . "_reload");
        }

        $neededAmmo = $magSize - $this->$ammoVar;
        if ($neededAmmo < 0) $neededAmmo = 0;

        $this->isReloading = true;

        Timer::after($delay, function() use ($neededAmmo, $ammoVar, $ammoCountField, $inv, $weaponKey, $jammed) {

            UXApplication::runLater(function() use ($neededAmmo, $ammoVar, $ammoCountField, $inv, $weaponKey, $jammed) {

                $totalAmmo = $inv->$ammoCountField;

                if ($totalAmmo > 0)
                {
                    if ($totalAmmo < $neededAmmo)
                    {
                        $this->$ammoVar += $totalAmmo;
                        $totalAmmo = 0;
                    }
                    else
                    {
                        $this->$ammoVar += $neededAmmo;
                        $totalAmmo -= $neededAmmo;
                    }
                    $inv->$ammoCountField = $totalAmmo;
                }

                $updateFn = "updateAmmo" . strtoupper($ammoVar) . "Count";
                if (method_exists($inv, $updateFn))
                {
                    $inv->$updateFn();
                }

                $this->UpdateMagazine();

                $this->isReloading = false;

                $this->weaponData[$weaponKey]['jammed'] = false;
                $this->weaponData[$weaponKey]['jamHandled'] = false;       
            });
        });
    }

    function UpdateMagazine()
    {
        $this->ui_mag_background->hide();
        $this->ui_mag_background->text = null;
        $this->ui_mag_background->graphic = null;
        
        $currentAmmo = null;
        $totalAmmo   = null;
    
        if ($this->WeaponPm)
        {
            if ($GLOBALS['HudVisible']) $this->ui_mag_background->show();
                
            $this->ui_mag_background->graphic = new UXImageView(new UXImage('res://.data/ui/weapons/mag_9_18.png'));
            
            $this->form('Client')->Inventory->content->InventoryGrid->content->updateAmmo9x18Count();
            $totalAmmo   = $this->form('Client')->Inventory->content->InventoryGrid->content->pmAmmoCount;
            $currentAmmo = $this->pmAmmo;
        }

        if ($this->WeaponAK74)
        {
            if ($GLOBALS['HudVisible']) $this->ui_mag_background->show();

            $this->ui_mag_background->graphic = new UXImageView( new UXImage('res://.data/ui/weapons/mag_5_45_hud.png'));

            $this->form('Client')->Inventory->content->InventoryGrid->content->updateAmmo5x45Count();
            $totalAmmo   = $this->form('Client')->Inventory->content->InventoryGrid->content->akAmmoCount;
            $currentAmmo = $this->ak74Ammo;
        }

        $this->ui_mag_background->text = $currentAmmo . '/' . $totalAmmo;
        
        $this->GodMode(); //апдейт позиции ебанных иконок
    }
}
