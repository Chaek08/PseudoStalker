<?php
namespace app\forms;

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

    public function __construct() 
    {
        parent::__construct();

        $this->localization = new Localization($language);
    }
    function getCurrentLanguageFromUI()
    {
        return $this->form('Client')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value;
    }     
    
    function InitEnvironmentTimer($timeFromTasks = null)
    {    
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
            $this->actor->colorAdjustEffect->brightness = $brightness;
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
            $this->Environment->volume = 100;
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
                Media::open('res://.data/audio/fight/fight_sound_20_05_2025.mp3', true, $this->FightSound);
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

            if ($GLOBALS['AllSounds']) $this->form('Client')->StopAllSounds();
            Media::stop($this->Environment);

            if ($this->fight_image->visible) $this->fight_image->hide();
            if ($this->leave_btn->visible || !$GLOBALS['QuestCompleted']) $this->leave_btn->hide();
            if ($this->form('Client')->Fail->visible) $this->form('Client')->Fail->hide();
            if ($this->blood_ui->visible) $this->blood_ui->hide();

            $this->form('Client')->Inventory->content->DespawnItems();
            $this->form('Client')->Inventory->content->SetItemCondition();
            
            $this->form('Client')->Inventory->content->InventoryGrid->content->lockInventory(false);
            
            $this->form('Client')->Inventory->content->InventoryGrid->content->selectedItem = $this->form('Client')->Inventory->content->InventoryGrid->content->Inv_Outfit;
            $this->form('Client')->Inventory->content->InventoryGrid->content->PutOnItem();

            $this->actor->show();
            $this->enemy->show();
            $this->actor->x = 112;
            $this->enemy->x = 1312;

            $this->idle_static_actor->show();
            $this->idle_static_enemy->show();
            $this->idle_static_actor->x = $this->actor->x;
            $this->idle_static_enemy->x = $this->enemy->x;

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
            
            if ($GLOBALS['NeedToCheckPDA']) $this->pda_icon->show();
            if ($GLOBALS['GodMode']) $this->GodMode_Icon->show();
            if (!$this->idle_static_actor->visible) $this->fight_image->show();
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
        
        if ($this->item_vodka_0000->visible) $this->item_vodka_0000->hide();
        if ($GLOBALS['ActorFailed']) $this->enemy->hide();
        if ($GLOBALS['EnemyFailed']) $this->actor->hide();
    }
    function SpawnItem()
    {
        $actor = $this->actor;
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
        $actor = $this->actor;

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
        if ($GLOBALS['GodMode'])
        {
            $this->GodMode_Icon->show();
            $this->blood_ui->y += 60;
        }
        else
        {
            $this->GodMode_Icon->hide();
            if ($this->blood_ui->y != 96)
            {
                $this->blood_ui->y -= 60;
            }            
        }
    }
    function SpawnParticle($target)
    {

        if ($target == $this->actor)
        {
            $healthBar = $this->health_bar_actor;
        }
        else
        {
            $healthBar = $this->health_bar_enemy;
        }    

        $cursorX = $this->form('Client')->CustomCursor->x;
        $cursorY = $this->form('Client')->CustomCursor->y;

        for ($i = 0; $i < 3; $i++)
        {
            $scatterX = rand(-35, 35);
            $scatterY = rand(-35, 35);

            $particle = new UXImageView();
            $particle->enabled = false;
            $particle->opacity = 1;
            $particle->image = new UXImage("res://.data/ui/particles/blood.png");
            $particle->scale = $this->form('Client')->MainGame->scale;
            $particle->width = 86;
            $particle->height = 86;

            $particle->x = $cursorX - ($particle->width / 2) + $scatterX;
            $particle->y = $cursorY - ($particle->height / 2) + $scatterY;

            $this->form('Client')->add($particle);

            $delay = ($healthBar->width - 30 <= 54) ? 600 : 300;

            Timer::after($delay, function () use ($particle) {
                Animation::fadeOut($particle, 300, function () use ($particle) {
                    $particle->free();
                });
            });
        }
    }    
    /**
     * @event enemy.click-2x
     */       
    function DamageEnemy(UXMouseEvent $e = null, bool $spawnParticles = true)
    { 
        if ($this->health_bar_enemy->width != 54)
        {
            $target = $this->health_bar_enemy->width - 30;
            $this->form('Client')->animateResizeWidth($this->health_bar_enemy, $target, 3, function() {
                if ($this->health_bar_enemy->width == 234)
                {
                    $this->health_bar_enemy->text = "75%";
                }
                if ($this->health_bar_enemy->width == 204)
                {
                    $this->health_bar_enemy->text = "55%";
                }      
                if ($this->health_bar_enemy->width == 174)
                {
                    $this->health_bar_enemy->text = "50%";
                }  
                if ($this->health_bar_enemy->width == 144)
                {
                    $this->health_bar_enemy->text = "33%";
                }
                if ($this->health_bar_enemy->width == 84)
                {
                    $this->health_bar_enemy->text = "15%";
                }                
                if ($this->health_bar_enemy->width == 54)
                {
                    $this->health_bar_enemy->text = "1%";
                }
            });
            
            if ($spawnParticles) 
            {
                $this->SpawnParticle($enemy); 
            }

            if ($GLOBALS['AllSounds'])
            {
                Media::open('res://.data/audio/hit_sound/hit_vovchik.mp3', true, 'hit_actor');
                Media::open('res://.data/audio/hit_sound/kulak_ebanul.mp3', true, 'hit_actor_damage');
            }        
        }
        else     
        {
            $this->health_static_enemy->graphic = new UXImageView(new UXImage('res://.data/ui/maingame/skull_new.png'));
            $this->health_bar_enemy->hide();
            $this->health_bar_enemy_b->hide();
            $this->Talk_Label->hide();
        
            $this->SpawnParticle($enemy);
        
            if ($GLOBALS['AllSounds']) 
            {
                Media::open('res://.data/audio/hit_sound/hit_vovchik.mp3', true, 'hit_actor');
                Media::open('res://.data/audio/hit_sound/kulak_ebanul.mp3', true, 'hit_actor_damage');
                            
                Media::open('res://.data/audio/hit_sound/die_vovchik.mp3', true, 'die_actor');                
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
        if ($this->health_bar_gg->width != 54)
        {
            if (!$GLOBALS['GodMode'])
            {
                $target = $this->health_bar_gg->width - 30;
                $this->form('Client')->animateResizeWidth($this->health_bar_gg, $target, 3, function() {
                    if ($this->health_bar_gg->width == 234)
                    {
                        $this->health_bar_gg->text = "75%";
                        $this->form('Client')->Inventory->content->health_bar_gg->width -= 100;           
                        $this->form('Client')->Inventory->content->health_bar_gg->text = "75%"; 
                    }
                    if ($this->health_bar_gg->width == 204)
                    {
                        $this->health_bar_gg->text = "55%";
                        $this->form('Client')->Inventory->content->health_bar_gg->width -= 50;            
                        $this->form('Client')->Inventory->content->health_bar_gg->text = "55%";
                    }      
                    if ($this->health_bar_gg->width == 174)
                    {
                        $this->health_bar_gg->text = "50%";
                        $this->form('Client')->Inventory->content->health_bar_gg->width -= 40;     
                        $this->form('Client')->Inventory->content->health_bar_gg->text = "50%";         
                    }  
                    if ($this->health_bar_gg->width == 144)
                    {
                        $this->health_bar_gg->text = "33%";
                        $this->form('Client')->Inventory->content->health_bar_gg->width -= 100;            
                        $this->form('Client')->Inventory->content->health_bar_gg->text = "33%";
                    }                
                    if ($this->health_bar_gg->width == 84)
                    {
                        $this->health_bar_gg->text = "15%";
                        $this->form('Client')->Inventory->content->health_bar_gg->width -= 40;
                        $this->form('Client')->Inventory->content->health_bar_gg->text = "15%";   
                    }
                    if ($this->health_bar_gg->width == 54)
                    {
                        $this->health_bar_gg->text = "1%";
                        $this->form('Client')->Inventory->content->health_bar_gg->width -= 50;
                        $this->form('Client')->Inventory->content->health_bar_gg->text = "1%";
                    }    
                    $this->Bleeding();
                });                
            }

            $now = Time::millis();
            $timeDiff = $now - $this->lastHitTime;
            $this->lastHitTime = $now;

            if ($timeDiff < 500)
            {
                if ($this->hitmarkLevel < 4) //6
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
                case 1:
                    $this->HitMark->image = new UXImage("res://.data/ui/maingame/hitmark/hitmark_1.png");
                    break;
                case 2:
                    $this->HitMark->image = new UXImage("res://.data/ui/maingame/hitmark/hitmark_2.png");
                    break;
                case 3:
                    $this->HitMark->image = new UXImage("res://.data/ui/maingame/hitmark/hitmark_3.png");
                    break;
                case 4:
                    $this->HitMark->image = new UXImage("res://.data/ui/maingame/hitmark/hitmark_4.png");
                    break;
                /*     
                case 5:
                    $this->HitMark->image = new UXImage("res://.data/ui/maingame/hitmark/hitmark_5.png");
                    break; 
                        
                case 6:
                    $this->HitMark->image = new UXImage("res://.data/ui/maingame/hitmark/hitmark_6.png");
                    break;   
                */                                                    
            }

            $this->HitMark->opacity = 0;
            $this->HitMark->visible = true;

            Animation::fadeIn($this->HitMark, 100);

            $this->hitmarkVisibleUntil = Time::millis() + 500;

            Timer::after(500, function () {
                if (Time::millis() >= $this->hitmarkVisibleUntil)
                {
                    Animation::fadeOut($this->HitMark, 300);
        
                    Timer::after(300, function () {
                        $this->hitmarkLevel = 1;
                    });
                }
            });
            
            $this->SpawnParticle($actor);
        
            if ($GLOBALS['AllSounds'])
            {
                Media::open('res://.data/audio/hit_sound/hit_alex.mp3', true, 'hit_alex');
                Media::open('res://.data/audio/hit_sound/kulak_ebanul.mp3', true, 'hit_alex_damage');
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
            if ($this->HitMark->visible) $this->HitMark->hide();
                
            $this->SpawnParticle($actor);
                  
            if ($GLOBALS['AllSounds'])
            {
                Media::open('res://.data/audio/hit_sound/hit_alex.mp3', true, 'hit_alex');
                Media::open('res://.data/audio/hit_sound/kulak_ebanul.mp3', true, 'hit_alex_damage');
                            
                Media::open('res://.data/audio/hit_sound/die_alex.mp3', true, 'die_alex');
            }
        
            $GLOBALS['ActorFailed'] = true;
            $this->finalizeBattle();
            return;
        }            
    }    
    function Bleeding()
    {
        if ($this->health_bar_gg->width == 264) return;
        
        $GLOBALS['ActorFailed'] ? $this->blood_ui->hide() : $this->blood_ui->show();

        if ($this->health_bar_gg->width == 204 || $this->health_bar_gg->width == 174)
        {
            $this->blood_ui->image = new UXImage('res://.data/ui/maingame/blood_mini.png');
        }
        if ($this->health_bar_gg->width == 144)
        {
            $this->blood_ui->image = new UXImage('res://.data/ui/maingame/blood_medium.png');            
        }
        if ($this->health_bar_gg->width == 84)
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
        
        if ($GLOBALS['ActorFailed']) $this->actor->hide();
        if ($GLOBALS['EnemyFailed']) $this->enemy->hide();
        
        $this->form('Client')->Inventory->content->InventoryGrid->content->lockInventory(true);
        
        $this->item_vodka_0000->enabled = false;
        $this->item_vodka_0000->opacity = 0;
        
        $this->idle_static_actor->show();
        $this->idle_static_enemy->show();
        $this->idle_static_actor->x = $this->actor->x;
        $this->idle_static_enemy->x = $this->enemy->x;
        
        if ($GLOBALS['AllSounds']) $this->form('Client')->StopAllSounds();
        
        if ($GLOBALS['ActorFailed'])
        {
            $this->actor->hide();
            
            $this->form('Client')->Pda->content->Pda_Tasks->content->Step2_Failed();
            
            if ($GLOBALS['AllSounds']) Media::open('res://.data/audio/victory/victory_alex.mp3', true, 'v_enemy');
        }
        if ($GLOBALS['EnemyFailed'])
        {
            $this->enemy->hide();
            
            $this->form('Client')->Pda->content->Pda_Tasks->content->Step2_Complete();
            
            if ($GLOBALS['AllSounds']) Media::open('res://.data/audio/victory/victory_actor.mp3', true, 'v_actor');
        }
        $this->form('Client')->Pda->content->Pda_Tasks->content->Step_UpdatePda();
        
        $this->form('Client')->Pda->content->Pda_Statistic->content->UpdateRaiting();
        
        $GLOBALS['discord']->setState(null);
        $GLOBALS['discord']->updateState();        
    }
    
    protected $isHovered = false;
    protected $isLabelVisible = false;
    /**
     * @event idle_static_enemy.mouseEnter
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
     * @event idle_static_enemy.mouseExit
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
}
