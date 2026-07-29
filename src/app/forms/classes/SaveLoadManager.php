<?php
namespace app\forms\classes;

use php\lang\ThreadPool;
use app\forms\classes\UI\InventoryActions;
use Throwable;
use app\forms\classes\Log;
use php\gui\UXApplication;
use php\time\Timer;
use php\framework\Logger;
use php\io\Stream;
use app\forms\classes\DimasCryptoZlodey;
use app\forms\classes\Debug;

class SaveLoadManager
{
    protected $saveDir;
    protected $formCallable;
    protected $weaponData;
    private $savePool;

    public function __construct($formCallable, &$weaponData, $saveDir = "./userdata/savedgames/")
    {
        $this->formCallable = $formCallable;
        $this->weaponData   = &$weaponData;
        $this->saveDir      = rtrim($saveDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        
        $this->savePool     = ThreadPool::createFixed(1);
    }
    
    public function getSaveDir(): string
    {
        return $this->saveDir;
    }    
    
    public function getAllSaves(): array
    {
        if (!is_dir($this->saveDir))
        {
            return [];
        }
    
        $files = scandir($this->saveDir);
    
        $saves = [];
    
        foreach ($files as $file)
        {
            if (substr($file, -4) === '.sav')
            {
                $filePath = $this->saveDir . $file;
    
                if (file_exists($filePath))
                {
                    $saves[] = [
                        'name' => substr($file, 0, -4),
                        'time' => filemtime($filePath)
                    ];
                }
            }
        }
    
        return $saves;
    }
    
    public function getLastSaveName(): ?string
    {
        $saves = $this->getAllSaves();
    
        if (empty($saves))
        {
            return null;
        }
    
        usort($saves, function($a, $b) {
            return $b['time'] <=> $a['time'];
        });
    
        return $saves[0]['name'];
    }

    protected function callForm($formName)
    {
        return call_user_func($this->formCallable, $formName);
    }

    protected function keyExists(array $array, array $keys): bool
    {
        foreach ($keys as $key)
        {
            if (!is_array($array) || !array_key_exists($key, $array))
            {
                return false;
            }
            $array = $array[$key];
        }
        return true;
    }

    public function collectSaveData()
    {
        $c  = $this->callForm('Client');
        $mg = $c->MainGame->content;
    
        $actor = $mg->GameActor;

        $currentWeapon = $actor->getWeapon();
    
        $data = [
            'client_version' => client_version,
    
            'ammo' => [
                'pm_total'   => $c->Inventory->content->getItem('ammo_9x18')->getCount(),
                'ak74_total' => $c->Inventory->content->getItem('ammo_5x45')->getCount(),
            ],
    
            'weapons' => [
                'current' => $currentWeapon ? $currentWeapon->getType() : null,
                'list' => [
                    'wpn_pm'   => $actor->getWeaponObject('wpn_pm')->exportState(),
                    'wpn_ak74' => $actor->getWeaponObject('wpn_ak74')->exportState(),
                ],
            ],
    
            'health' => [
                'actor' => [
                    'hp' => $c->MainGame->content->GameActor->getHP(),
                ],
                'enemy' => [
                    'hp' => $c->MainGame->content->GameEnemy->getHP(),
                ],
            ],
            'objects_position' => [
                'actor' => [
                    'x' => $c->MainGame->content->actor->position[0],
                    'y' => $c->MainGame->content->actor->position[1],
                    'is_wearing' => $c->MainGame->content->GameActor->isWearingOutfit(),
                ],
                'enemy' => [
                    'x' => $c->MainGame->content->enemy->position[0],
                    'y' => $c->MainGame->content->enemy->position[1],
                ],
                'item_vodka_0000' => [
                    'x' => $c->MainGame->content->item_vodka_0000->position[0],
                    'y' => $c->MainGame->content->item_vodka_0000->position[1],
                ],
            ],
            'quest_time' => [
                'date' => $c->Pda->content->Pda_Tasks->content->time_quest_date->text,
                'hm'   => $c->Pda->content->Pda_Tasks->content->time_quest_hm->text,
            ],
            'vodka_exist'      => $c->MainGame->content->item_vodka_0000->visible,
            'medkit_count'     => $c->Inventory->content->getItem('medkit')->getCount(),
            'quest_step1'      => isset($GLOBALS['QuestStep1']) ? $GLOBALS['QuestStep1'] : false,
            'quest_completed'  => isset($GLOBALS['QuestCompleted']) ? $GLOBALS['QuestCompleted'] : false,
            'actors_state' => [
                'actor' => [
                    'dead' => $c->MainGame->content->GameActor->isDead(),
                ],
                'enemy' => [
                    'dead' => $c->MainGame->content->GameEnemy->isDead(),
                ],
            ],
            'need_to_check_pda'      => isset($GLOBALS['NeedToCheckPDA']) ? $GLOBALS['NeedToCheckPDA'] : false,
            'menubackground_playpos' => $c->MainMenu->content->MainMenuBackground->positionMs,
            'menusound_playpos'      => isset($c->MainMenu->content->menuPlayer) ? $c->MainMenu->content->menuPlayer->positionMs : 0,
            'environment_state'      => $c->MainGame->content->Environment->getState(),
            'fightsound_playpos'     => isset($c->MainGame->content->fightPlayer) ? $c->MainGame->content->fightPlayer->positionMs : 0,
        ];
    
        return $data;
    }


    public function validateSave(array $data, string $saveName): array
    {
        $requiredKeys = [
            'client_version',
        
            'health',
            'health.actor',
            'health.actor.hp',
            'health.enemy',
            'health.enemy.hp',
        
            'objects_position',
            'objects_position.actor',
            'objects_position.actor.x',
            'objects_position.actor.y',
            'objects_position.actor.is_wearing',
            'objects_position.enemy',
            'objects_position.enemy.x',
            'objects_position.enemy.y',
            'objects_position.item_vodka_0000',
            'objects_position.item_vodka_0000.x',
            'objects_position.item_vodka_0000.y',
        
            'quest_time',
            'quest_time.date',
            'quest_time.hm',
        
            'vodka_exist',
            'medkit_count',
            'quest_step1',
            'quest_completed',
            'actors_state',
            'actors_state.actor',
            'actors_state.actor.dead',
            'actors_state.enemy',
            'actors_state.enemy.dead',
            'need_to_check_pda',
        
            'menubackground_playpos',
            'menusound_playpos',
            'fightsound_playpos',
                        
            'environment_state',
            'environment_state.location_index',
            'environment_state.cycle',
            'environment_state.background_path',
            'environment_state.ambient_path',
            'environment_state.ambient_position',
            
            'ammo',
            'ammo.pm_total',
            'ammo.ak74_total',
        
            'weapons',
            'weapons.list',
            'weapons.list.wpn_pm',
            'weapons.list.wpn_pm.ammo',
            'weapons.list.wpn_pm.jammed',
            'weapons.list.wpn_pm.jamHandled',
            'weapons.list.wpn_ak74',
            'weapons.list.wpn_ak74.ammo',
            'weapons.list.wpn_ak74.jammed',
            'weapons.list.wpn_ak74.jamHandled',
        ];

        $missing = [];
    
        foreach ($requiredKeys as $key)
        {
            $parts = explode('.', $key);
            if (!$this->keyExists($data, $parts))
            {
                Log::error("Corrupt save '{$saveName}': missing key '$key'");
                $missing[] = $key;
            }
        }
    
        if (!empty($missing))
        {
            return ['ok' => false, 'error' => 'corrupt', 'missing' => $missing];
        }
    
        if (!isset($data['client_version']) || $data['client_version'] !== client_version)
        {
            Log::error("Version mismatch in save '{$saveName}': expected " . client_version . ", got " . ($data['client_version'] ?? 'null'));
                
            return ['ok' => false, 'error' => 'version'];
        }
    
        return ['ok' => true];
    }
    
    public function save($saveName)
    {
        if ($saveName === '') return;
    
        $client = $this->callForm('Client');
        $diskIo = $client->MainGame->content->ui_disk_io ?? null;
        
        if ($client->MainGame->content->isMP)
        {
            Log::info('Saving/Loading is disabled in multiplayer.');
            
            return;
        }        
    
        if ($diskIo)
        {
            $diskIo->visible = true;
        }
    
        $data = $this->collectSaveData();
    
        $this->savePool->execute(function () use ($saveName, $data, $diskIo) {
    
            try {
                if (!is_dir($this->saveDir))
                {
                    mkdir($this->saveDir, 0777, true);
                }
    
                $path = $this->saveDir . $saveName . '.sav';
    
                $json = json_encode($data);
                $encrypted = DimasCryptoZlodey::encryptData($json);
    
                Stream::putContents($path, $encrypted);
    
                uiLater(function () use ($saveName) {
                    if (defined('Debug_Build') && Debug_Build)
                    {
                        Log::info("Saved game: " . $saveName);
                    }
                });
    
            } finally {
    
                if ($diskIo) {
                    uiLater(function () use ($diskIo) {
                        $diskIo->visible = false;
                    });
                }
            }
        });
    }

    public function load($saveName)
    {
        $client = $this->callForm('Client');
        $diskIo = $client->MainGame->content->ui_disk_io ?? null;
        
        if ($client->MainGame->content->isMP)
        {
            Log::info('Saving/Loading is disabled in multiplayer.');
            
            return;
        }        
    
        if ($diskIo)
        {
            $diskIo->visible = true;
        }
    
        try {
            $path = $this->saveDir . $saveName . '.sav';
            if (!file_exists($path)) return null;
    
            $raw  = Stream::getContents($path);
            try
            {
                $data = json_decode(DimasCryptoZlodey::decryptData($raw), true);
                return $data ?: null;
            }
            catch (\Throwable $e)
            {
                return null;
            }
        } finally {
            if ($diskIo)
            {
                Timer::after(6000, function () use ($diskIo) {
                    uiLater(function () use ($diskIo) {
                        $diskIo->visible = false;
                    });
                });
            }
        }
    }


    public function applySaveData(array $saveData, string $saveName): void
    {
        $form = $this->callForm('Client');

        $form->MainGame->content->ResetGameClient(function () use ($saveData, $saveName, $form) {
        
        if ($GLOBALS['AllSoundSwitcher_IsOn']) $GLOBALS['AllSounds'] = false;
        
        $form->Pda->content->Pda_Tasks->content->UpdateData();
        $form->Pda->content->Pda_Tasks->content->time_quest_date->text = $saveData['quest_time']['date'];
        $form->Pda->content->Pda_Tasks->content->time_quest_hm->text   = $saveData['quest_time']['hm'];        

        $form->MainGame->content->item_vodka_0000->visible = $saveData['vodka_exist'];

        if ($form->MainGame->content->ItemVodka->isVisible())
        {
            $inv = $form->Inventory->content;
        
            if ($inv->getSelectedItem() === $inv->getItem('vodka'))
            {
                $inv->DropItem();
            }
        }    

        $GLOBALS['QuestStep1']     = $saveData['quest_step1'];
        $GLOBALS['QuestCompleted'] = $saveData['quest_completed'];
        $GLOBALS['NeedToCheckPDA'] = $saveData['need_to_check_pda'];
        
        if (isset($saveData['objects_position']['actor']))
        {
            $form->MainGame->content->GameActor->respawn($saveData['objects_position']['actor']['x'], $saveData['objects_position']['actor']['y'], false);
        }
        if (isset($saveData['objects_position']['enemy']))
        {
            $form->MainGame->content->GameEnemy->respawn($saveData['objects_position']['enemy']['x'], $saveData['objects_position']['enemy']['y'], false);
        }
        
        if (isset($saveData['ammo']))
        {
            $inv = $form->Inventory->content;
        
            $pmAmmo = $inv->getItem('ammo_9x18');
            $akAmmo = $inv->getItem('ammo_5x45');
        
            $pmAmmo->setCount($saveData['ammo']['pm_total'] ?? 0);
            $akAmmo->setCount($saveData['ammo']['ak74_total'] ?? 0);
        }
        
        if (isset($saveData['weapons']))
        {
            $actor = $form->MainGame->content->GameActor;
        
            $list = $saveData['weapons']['list'] ?? [];
        
            foreach ($list as $type => $state)
            {
                $weapon = $actor->getWeaponObject($type);
        
                if ($weapon)
                {
                    $weapon->importState($state);
                }
            }
        
            $desired = $saveData['weapons']['current'] ?? null;
        
            $actor->SwitchWeapon($desired);
        
            $form->MainGame->content->UpdateMagazine();
        }

        if (isset($saveData['objects_position']['item_vodka_0000']))
        {
            $form->MainGame->content->item_vodka_0000->position = [$saveData['objects_position']['item_vodka_0000']['x'], $saveData['objects_position']['item_vodka_0000']['y']];
        } 
 
        $actorHp   = $saveData['health']['actor']['hp'] ?? 100;
        $enemyHp   = $saveData['health']['enemy']['hp'] ?? 100;
        
        $actorDead = $saveData['actors_state']['actor']['dead'] ?? false;
        $enemyDead = $saveData['actors_state']['enemy']['dead'] ?? false;
        
        $form->MainGame->content->GameActor->restoreState($actorHp, $actorDead, true);
        $form->MainGame->content->GameEnemy->restoreState($enemyHp, $enemyDead, false);
        
        if ($actorDead || $enemyDead)
        {
            if ($saveData['quest_step1'] == true)
            {
                $form->Dialog->content->Talk_Final();
            }
            
            $form->Pda->content->Pda_Ranking->content->DeathFilterManager();
            
            if ($form->Fail->visible)
            {
                $form->Fail->content->ReturnBtn();
            }
            
            if ($saveData['need_to_check_pda'] == false)
            {
                $form->Pda->content->Pda_Tasks->content->Step_DeletePda();
            }
        }
        
        if ($actorDead)
        {
            $form->MainGame->content->onActorDeath();
        }
        if ($enemyDead)
        {
            $form->MainGame->content->onEnemyDeath();
        }        
        
        if ($GLOBALS['AllSoundSwitcher_IsOn']) $GLOBALS['AllSounds'] = true;         
        
        if ($GLOBALS['QuestStep1'] && !$GLOBALS['QuestCompleted'])
        {
            if ($form->MainGame->content->fightPlayer)
            {
                $form->MainGame->content->fightPlayer->stop();
                $form->MainGame->content->fightPlayer = null;
            }
        
            $form->Dialog->content->Talk_Final();
            $form->MainGame->content->fight_image->show();
        }   
        
        if ($form->MainGame->content->MessageBox->visible) $form->MainGame->content->MessageBox->hide();
        if ($form->MainGame->content->Task_Step_Label->visible) $form->MainGame->content->Task_Step_Label->hide();    
        
        $medkit = $form->Inventory->content->getItem('medkit');
        $medkit->setCount($saveData['medkit_count'] ?? 0);
        $form->Inventory->content->updateItemCount($medkit);
    
        if (isset($saveData['objects_position']['actor']['is_wearing']))
        {
            $isWearing = $saveData['objects_position']['actor']['is_wearing'];
        
            $inv = $form->Inventory->content;
        
            $inv->setSelectedItem($inv->getItem('outfit'));
        
            if ($isWearing)
            {
                $inv->getActions()->putOnItem();
            }
            else
            {
                $inv->getActions()->takeOffItem();
            }
        }
        
        if (isset($saveData['environment_state']) && is_array($saveData['environment_state']))
        {
            $env = $form->MainGame->content->Environment;
            $env->restoreState($saveData['environment_state'], $saveData['quest_time']['hm']);
        }
        
        if (isset($saveData['menusound_playpos']))
        {
            $player = $form->MainMenu->content->menuPlayer;
        
            if ($player)
            {
                $player->positionMs = (int)$saveData['menusound_playpos'];
            }
        }
        
        if (isset($saveData['fightsound_playpos']))
        {
            $player = $form->MainGame->content->fightPlayer;
        
            if ($player)
            {
                $player->positionMs = (int)$saveData['fightsound_playpos'];
            }
        }
        
        if (isset($saveData['menubackground_playpos']))
        {
            $bg = $form->MainMenu->content->MainMenuBackground;
        
            if ($bg)
            {
                $bg->positionMs = (int)$saveData['menubackground_playpos'];
            }
        }        
        
        });
        
        Log::info("Loaded save: " . $saveName);      
                           
    }

    public function restoreGame(string $saveName): void
    {
        $this->savePool->execute(function () use ($saveName) {
    
            $saveData = $this->load($saveName);
    
            if ($saveData === null)
            {
                return;
            }
    
            $result = $this->validateSave($saveData, $saveName);
    
            if (!$result['ok'])
            {
                return;
            }
    
            uiLater(function () use ($saveData, $saveName) {
                $this->applySaveData($saveData, $saveName);
            });
        });
    }
    
    public function __destruct()
    {
        if ($this->savePool)
        {
            $this->savePool->shutdown();
        }
    }    
}
