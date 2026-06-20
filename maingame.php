<?php
namespace app\forms;

use php\desktop\Mouse;
use app\forms\classes\CEnemy;
use app\forms\classes\CActor;
use Throwable;
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
use app\forms\classes\Environment\EnvironmentBase;
use app\forms\classes\Environment\EnvironmentBrightness;
use app\forms\classes\ParticleManager;
use app\forms\classes\UI\HitMark;
use app\forms\classes\UIProgressBarAnimator;
use app\forms\classes\PseudoSound;

class maingame extends AbstractForm
{
    private $currentCycle = '';
    private $localization;
    
    protected $HitMark;
    
    public $SDK_FightSound;
    public $SDK_ActorModel;
    public $SDK_EnemyModel;    
    
    public $Environment;
    public $EnvironmentBrightness;
    
    public $Particles;

    public $GameActor;
    public $GameEnemy;
    
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
        
        $this->GameActor->onHpChanged(function ($entity) {
            $this->Bleeding();
            $this->updateActorHealthUI($entity);
        });
        
        $this->GameActor->onDeath(function () {
            $this->onActorDeath();
        });
        
        $this->GameEnemy->onHpChanged(function ($entity) {
            $this->updateEnemyHealthUI($entity);
        });
        
        $this->GameEnemy->onDeath(function () {
            $this->onEnemyDeath();
        });
        
        $this->HitMark = new HitMark($this->HitMark_Visual);
        
        $this->ItemVodka = new CVodka($this, $this->item_vodka_0000, $this->GameActor, $this->GameEnemy); //CItem zavtra
        $this->ItemVodka->disable();
        $this->ItemVodka->hide();
        $this->ItemVodka->resetVisual(); 
    }
    
    function getCurrentLanguageFromUI()
    {
        return $this->form('Client')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value;
    }     
    
    function InitEnvironment()
    {
        $this->EnvironmentBrightness = new EnvironmentBrightness();    
        $this->Environment = new Environment($this->Environment_Space, $this->EnvironmentBrightness);
           
        $this->Environment->forceBrightnessNow();
        $this->Environment->startAmbient();
        $this->Environment->pause();
        
        $this->EnvironmentBrightness->register($this->GameActor->GetModel());
        
        $this->EnvironmentBrightness->register($this->GameEnemy->GetModel());
        
        $this->EnvironmentBrightness->register($this->ItemVodka->GetModel());
    }
    
    function PlayFightSong()
    {
        $path = trim($this->SDK_FightSound);
    
        if ($path == '')
        {
            $path = 'res://.data/audio/fight/fight_sound.mp3';
        }
        
        PseudoSound::play($path, 'fight_sound', true, PseudoSound::TYPE_MUSIC);
        PseudoSound::muteChannel('fight_sound', false);
        
        if ($GLOBALS['AllSounds'] && $GLOBALS['FightSound'])
        {
            PseudoSound::unmuteChannel('fight_sound');
        }
    }    
    
    function ResetGameClient(callable $afterReset = null)
    {
        $this->form('Client')->ShowLoadScreen(function () use ($afterReset)
        {
            if ($GLOBALS['QuestStep1']) $GLOBALS['QuestStep1'] = false;
            if ($GLOBALS['QuestCompleted']) $GLOBALS['QuestCompleted'] = false;
            
            //вроде дестрой был, но хуй знает
            PseudoSound::stopChannelInstant('fight_sound');
            PseudoSound::stopChannelInstant('menu_sound');

            if ($this->fight_image->visible) $this->fight_image->hide();
            if ($this->leave_btn->visible || !$GLOBALS['QuestCompleted']) $this->leave_btn->hide();
            if ($this->form('Client')->Fail->visible) $this->form('Client')->Fail->hide();
            if ($this->blood_ui->visible) $this->blood_ui->hide();

            $this->form('Client')->Inventory->content->DespawnItems();
            $this->form('Client')->Inventory->content->SetItemCondition();
            
            $this->GameActor->SwitchWeapon('Pm');
            UXApplication::runLater(function () {
                $w = $this->GameActor->getWeapon();
                if ($w && $w->getType() === 'Pm')
                {
                    $w->importState(['jammed' => false, 'jamHandled' => false]);
                    $w->setAmmo($w->getMagSize());
                }
                $this->UpdateMagazine();
                
                $this->GameActor->SwitchWeapon('AK74');
            
                UXApplication::runLater(function () {
                    $w2 = $this->GameActor->getWeapon();
                    if ($w2 && $w2->getType() === 'AK74')
                    {
                        $w2->importState(['jammed' => false, 'jamHandled' => false]);
                        $w2->setAmmo($w2->getMagSize());
                    }
                    $this->UpdateMagazine();
                });
            
            });
            $this->form('Client')->Inventory->content->MoveWeaponsToWeaponSlot();
                   
            $this->GameActor->respawn(112, $this->GameActor->GetModel()->y, false);
            $this->GameEnemy->respawn(1312, $this->GameEnemy->GetModel()->y, false);
            
            $this->form('Client')->Inventory->content->health_bar_gg->show();
            $this->form('Client')->Inventory->content->health_bar_gg_b->show();
            $this->form('Client')->Inventory->content->health_static_gg->show();
            $this->form('Client')->Inventory->content->health_static_gg->graphic = null;
            
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
                try
                {
                    $this->Environment->stop();
                }
                catch (Throwable $e) {}
            
                $this->Environment = null;
            }
            
            if (empty($GLOBALS['IsSaveLoading']))
            {
                $this->InitEnvironment();
            }
            
            $this->form('Client')->Dialog->content->StartDialog();
            
            if ($this->form('Client')->ltx->r_bool('discord_rpc'))
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
            
            if ($this->GameActor->getWeapon()) $this->ui_mag_background->show();
            if ($GLOBALS['NeedToCheckPDA']) $this->pda_icon->show();
            if ($this->GameActor->isGodMode()) $this->GodMode_Icon->show();
            if ($this->GameActor->CanInteractive() || $this->GameEnemy->CanInteractive()) $this->fight_image->show();
            if ($this->GameActor->isDead() || $this->GameEnemy->isDead()) $this->leave_btn->show();
        
            $GLOBALS['HudVisible'] = true;
            
            $this->Bleeding();
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
            if ($this->GameActor->isGodMode()) $this->GodMode_Icon->hide();
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
        
        $w = $this->GameActor->getWeapon();
        if ($w) $w->softHide();
        
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
        $this->updateActorHealthUI($this->GameActor);
        $this->updateEnemyHealthUI($this->GameEnemy);
    }
    
    function GodMode()
    {
        $baseY = 96;
        $nextY = $baseY;
    
        if ($this->GameActor->getWeapon() !== null)
        {
            $this->ui_mag_background->y = $baseY;
            $nextY = $baseY + 64;
        }
    
        if ($this->GameActor->isGodMode())
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
    
    function setGodMode(CEntity $entity, bool $state): void
    {
        $entity->SetGodMode($state);
    
        if ($entity == $this->GameActor)
        {
            $this->updateGodModeUI($state);
        }
    }
    
    private function updateGodModeUI(bool $state): void
    {
        if ($state)
        {
            if ($GLOBALS['HudVisible']) $this->GodMode_Icon->show();
        }
        else
        {
            $this->GodMode_Icon->hide();
        }
    
        $this->GodMode();
    }    
    
    private $enemyCoverTimer; //2 отдельных таймера, дабы избежать гонки их же
    private $actorCoverTimer;
    
    /**
     * @event enemy.click-2x
     */       
    function DamageEnemy(UXMouseEvent $e = null, bool $spawnParticles = true, bool $damageByMouse = true)
    {
        if (!$this->GameEnemy->CanInteractive())
        {
            return;
        }
    
        $missChance = 75;
        $damageMin  = 8;
        $damageMax  = 20;
    
        if (rand(1, 100) > $missChance)
        {
            $damage = rand($damageMin, $damageMax);
            $this->GameEnemy->applyDamage($damage);
        }
    
        if ($spawnParticles)
        {
            if ($damageByMouse && $e)
            {
                $enemy = $this->GameEnemy->GetModel();
    
                $originX = $enemy->x + $e->x;
                $originY = $enemy->y + $e->y;
                $floorY  = $enemy->y + $enemy->height - 20;
    
                $this->Particles->bloodBurstAtPoint($originX, $originY, $floorY, 4, 7);
            }
            else
            {
                $this->Particles->bloodConeAtTarget($this->GameEnemy->GetModel());
            }
        }
    
        $rand = rand(0, 5);
    
        PseudoSound::playAsync("res://.data/audio/fight/hit_sounds/kulak_ebanul/kulak_ebanul_{$rand}.mp3", true, 'hit_enemy_damage');
    
        if (rand(1, 100) <= 25)
        {
            $randHit = rand(1, 8);
    
            PseudoSound::playAsync("res://.data/audio/fight/hit_sounds/enemy/hit_{$randHit}.mp3", true, 'hit_enemy');
        }
    
        if (rand(1, 100) <= 20)
        {
            if ($this->enemyCoverTimer)
            {
                $this->enemyCoverTimer->cancel();
                $this->enemyCoverTimer = null;
            }
    
            $this->enemyCoverTimer = Timer::after(2500, function ()
                {
                    $randCover = rand(1, 5);
    
                    $this->GameEnemy->playSound("res://.data/audio/fight/cover_sounds/enemy/cover_fire_{$randCover}.mp3", 900);
    
                    $this->enemyCoverTimer = null;
                }
            );
        }
    }
    
    /**
     * @event actor.click-2x
     */
    function DamageActor(UXMouseEvent $e = null)
    {
        if (!$this->GameActor->CanInteractive())
        {
            return;
        }
    
        $missChance = 75;
    
        if (rand(1, 100) > $missChance)
        {
            $damage = rand(8, 20);
            $this->GameActor->applyDamage($damage);
        }
    
        $this->HitMark->play();
    
        $actor = $this->GameActor->GetModel();
    
        $originX = $actor->x + $e->x;
        $originY = $actor->y + $e->y;
        $floorY  = $actor->y + $actor->height - 20;
    
        $this->Particles->bloodBurstAtPoint($originX, $originY, $floorY, 4, 7);
    
        $randEbanul = rand(0, 5);
        
        PseudoSound::playAsync("res://.data/audio/fight/hit_sounds/kulak_ebanul/kulak_ebanul_{$randEbanul}.mp3", true, 'hit_actor_damage');
    
        if (rand(1, 100) <= 25)
        {
            $randHit = rand(1, 3);
    
            PseudoSound::playAsync("res://.data/audio/fight/hit_sounds/actor/hit_{$randHit}.mp3", true, 'hit_actor');
        }
    
        if (rand(1, 100) <= 40)
        {
            if ($this->actorCoverTimer)
            {
                $this->actorCoverTimer->cancel();
                $this->actorCoverTimer = null;
            }
    
            $this->actorCoverTimer = Timer::after(2500, function ()
                {
                    $randCover = rand(1, 2);
    
                    $this->GameActor->playSound("res://.data/audio/fight/cover_sounds/actor/cover_fire_{$randCover}.mp3", 900);
    
                    $this->actorCoverTimer = null;
                }
            );
        }
    }
   
    function Bleeding()
    {
        if ($this->GameActor->isDead())
        {
            $this->blood_ui->hide();
            return;
        }
    
        $hpPercent = $this->GameActor->getHpPercent();
    
        if ($hpPercent >= 100)
        {
            $this->blood_ui->hide();
            return;
        }
        
        if ($GLOBALS['HudVisible'])
        {
            $this->blood_ui->show();
        }
    
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
    
    private function updateEnemyHealthUI($enemy): void
    {
        $pct = $enemy->getHpPercent();
    
        $min = 54;
        $max = 264;
        
        $pct = max(0, min(100, $pct));
    
        $target = (int)($min + ($max - $min) * ($pct / 100));
    
        UIProgressBarAnimator::resizeWidth(
            $this->health_bar_enemy,
            $target,
            300,
            function () use ($pct) {
                $this->health_bar_enemy->text = $pct . '%';
            }
        );
    }
    
    private function updateActorHealthUI($actor): void
    {
        $pct = $actor->getHpPercent();
    
        $min = 54;
        $maxMain = 264;
        $maxInv  = 416;
        
        $pct = max(0, min(100, $pct));
            
        $targetMain = (int)($min + ($maxMain - $min) * ($pct / 100));
        $targetInv  = (int)($min + ($maxInv  - $min) * ($pct / 100));
    
        UIProgressBarAnimator::resizeWidth(
            $this->health_bar_gg,
            $targetMain,
            300,
            function () use ($pct) {
                $this->health_bar_gg->text = $pct . '%';
            }
        );
    
        $invBar = $this->form('Client')->Inventory->content->health_bar_gg;
    
        UIProgressBarAnimator::resizeWidth(
            $invBar,
            $targetInv,
            300,
            function () use ($invBar, $pct) {
                $invBar->text = $pct . '%';
            }
        );
    }
    
    private function onEnemyDeath(): void
    {
        $this->health_static_enemy->graphic = new UXImageView(new UXImage('res://.data/ui/maingame/skull_new.png'));
    
        $this->health_bar_enemy->hide();
        $this->health_bar_enemy_b->hide();
        $this->Talk_Label->hide();
    
        $randDie = rand(1, 7);
        PseudoSound::play("res://.data/audio/fight/death_sounds/enemy/death_{$randDie}.mp3", 'die_enemy', false, null, true);
    
        $this->finalizeBattle();
    }
    
    private function onActorDeath(): void
    {
        $this->health_static_gg->graphic = new UXImageView(new UXImage('res://.data/ui/maingame/skull_new.png'));
    
        $this->health_bar_gg->hide();
        $this->health_bar_gg_b->hide();
    
        $this->form('Client')->Inventory->content->health_bar_gg->hide();
        $this->form('Client')->Inventory->content->health_bar_gg_b->hide();
        $this->form('Client')->Inventory->content->health_static_gg->graphic = new UXImageView(new UXImage('res://.data/ui/maingame/skull_new.png'));
    
        $this->blood_ui->hide();
        if ($this->HitMark->isVisible()) $this->HitMark->hide();
    
        $randDie = rand(1, 4);
        PseudoSound::play("res://.data/audio/fight/death_sounds/actor/death_{$randDie}.mp3", 'die_actor', false, null, true);
    
        $this->finalizeBattle();
    }
    
    
    function finalizeBattle()
    {
        $GLOBALS['NeedToCheckPDA'] = true;
        
        $this->form('Client')->Pda->content->Pda_Statistic->content->UpdateFinalLabel();
    
        $this->fight_image->hide();
        $this->fight_image->blinkAnim->disable();
        
        $this->leave_btn->show();
        
        $this->ItemVodka->disable();
        $this->ItemVodka->setOpacity(0);
        $this->ItemVodka->hide();
        
        //НЕ НУЖНО ВСЕ ЗВУКИ ОСТАНАВЛИВАТЬ, МЫ ВЕДЬ НЕ ВЫХОДИМ В МЕНЮ, А ПРОСТО ЗАКАНЧИВАЕМ БОЙ
        
        PseudoSound::muteChannel('fight_sound'); //мы не стопаем канал сразу, чтобы не вызывать лагов. Но, главное, не забыть стопнуть уже при ресет гейм клиент
        
        if ($this->GameActor->isDead())
        {
            $this->GameEnemy->SetInteractive(false);
            
            if ($this->GameActor->getWeapon()) $this->GameActor->UnequipCurrentWeapon();
              
            $this->form('Client')->Pda->content->Pda_Tasks->content->Step2_Failed();
            
            PseudoSound::play('res://.data/audio/victory/victory_alex.mp3', 'v_enemy', false, null, true);
        }
        if ($this->GameEnemy->isDead())
        {
            $this->GameActor->SetInteractive(false);
            
            $this->form('Client')->Pda->content->Pda_Tasks->content->Step2_Complete();
            
            PseudoSound::play('res://.data/audio/victory/victory_actor.mp3', 'v_actor', false, null, true);
        }
        
        $this->form('Client')->Pda->content->Pda_Tasks->content->Step_UpdatePda();
        
        $this->form('Client')->Pda->content->Pda_Statistic->content->UpdateRaiting();
        
        if ($this->form('Client')->ltx->r_bool('discord_rpc'))
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
    
    public function UpdateMagazine(): void
    {
        $this->ui_mag_background->hide();
        $this->ui_mag_background->text = null;
        $this->ui_mag_background->graphic = null;
    
        $this->GodMode();        
    
        $w = $this->GameActor->getWeapon();
        if (!$w) return;
    
        if (!empty($GLOBALS['HudVisible'])) $this->ui_mag_background->show();
    
        $imgPath = $w->hudMagImage();
        $this->ui_mag_background->graphic = new UXImageView(new UXImage($imgPath));
    
        $currentAmmo = $w->getAmmo();
    
        if ($w->hasUnlimitedAmmo())
        {
            $this->ui_mag_background->text = $currentAmmo . '/--';
        }
        else
        {
            $totalAmmo   = $w->getTotalAmmoFromInventory();
            $this->ui_mag_background->text = $currentAmmo . '/' . $totalAmmo;
        }
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
    
    public function performSave(string $saveName, bool $autoRewrite = false)
    {
        if (!$GLOBALS['ContinueGameState'] || !$saveName) return;
    
        static $lastToastId = 0;
    
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
    
        $saveUI = $this->form('Client')->MainMenu->content->UISaveWnd->content;
    
        if ($autoRewrite)
        {
            $GLOBALS['AutoRewriteSave'] = true;
        }
    
        $saveUI->Edit_SaveName->text = $saveName;
        $saveUI->BtnSaveGame();
    
        $GLOBALS['AutoRewriteSave'] = false;
    
        $this->SavedGame_Toast->opacity = 0;
        $this->SavedGame_Toast->visible = true;
        $this->SavedGame_Toast->text = $this->localization->get('SavedGameToast') . ' ' . $saveName;
    
        Animation::fadeIn($this->SavedGame_Toast, 300);
    
        $lastToastId++;
        $currentId = $lastToastId;
    
        Timer::after(2300, function () use ($currentId) {
            if ($currentId == $GLOBALS['lastToastId']) {
                Animation::fadeOut($this->SavedGame_Toast, 300);
            }
        });
    
        $GLOBALS['lastToastId'] = $lastToastId;
    }    
    
    public function performLoad(string $saveName)
    {
        if (!$GLOBALS['ContinueGameState']) return;
    
        $loadWnd = $this->form('Client')->MainMenu->content->UILoadWnd->content;
        $savesList = $loadWnd->saves_list;
    
        foreach ($savesList->items->toArray() as $index => $item)
        {
            if ($item === $saveName)
            {
                $savesList->selectedIndex = $index;
                $loadWnd->BtnLoadSave();
                return;
            }
        }
    
        Log::result("Save '$saveName' not found.");
    }      
}
