<?php
namespace app\forms\classes;

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

    public function __construct($formCallable, &$weaponData, $saveDir = "./userdata/savedgames/")
    {
        $this->formCallable = $formCallable;
        $this->weaponData   = &$weaponData;
        $this->saveDir      = rtrim($saveDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
    
    public function getSaveDir(): string
    {
        return $this->saveDir;
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
    
        $stateList = [
            'Pm'   => $mg->weaponState['Pm']   ?? ['ammo' => 0, 'jammed' => false, 'jamHandled' => false],
            'AK74' => $mg->weaponState['AK74'] ?? ['ammo' => 0, 'jammed' => false, 'jamHandled' => false],
        ];
    
        $currentType = null;
        $w = $mg->GameActor->getWeapon();
        if ($w)
        {
            $currentType = $w->getType();
            $stateList[$currentType] = $w->exportState();
        }
    
        $data = [
            'client_version' => client_version,
    
            'ammo' => [
                'pm_total'   => $c->Inventory->content->InventoryGrid->content->pmAmmoCount,
                'ak74_total' => $c->Inventory->content->InventoryGrid->content->akAmmoCount,
            ],
    
            'weapons' => [
                'current' => $currentType,
                'list'    => $stateList,
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
                    'is_wearing' => $c->Inventory->content->InventoryGrid->content->isWearing,
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
            'medkit_count'     => $c->Inventory->content->InventoryGrid->content->medkitCount,
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
            'need_to_check_pda'=> isset($GLOBALS['NeedToCheckPDA']) ? $GLOBALS['NeedToCheckPDA'] : false,
            'menubackground_playpos' => $c->MainMenu->content->MainMenuBackground->positionMs,
            'menusound_playpos'      => $c->MainMenu->content->MenuSound->positionMs,
            'environment_state'      => $c->MainGame->content->Environment->getState(),            
            'fightsound_playpos'     => $c->MainGame->content->FightSound->positionMs,
        ];
    
        return $data;
    }


    public function validateSave(array $data): array
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
            'weapons.list.Pm',
            'weapons.list.Pm.ammo',
            'weapons.list.Pm.jammed',
            'weapons.list.Pm.jamHandled',
            'weapons.list.AK74',
            'weapons.list.AK74.ammo',
            'weapons.list.AK74.jammed',
            'weapons.list.AK74.jamHandled',
        ];

        $missing = [];
    
        foreach ($requiredKeys as $key)
        {
            $parts = explode('.', $key);
            if (!$this->keyExists($data, $parts))
            {
                Logger::error("Corrupt save: missing key '$key'");
                $missing[] = $key;
            }
        }
    
        if (!empty($missing))
        {
            return ['ok' => false, 'error' => 'corrupt', 'missing' => $missing];
        }
    
        if (!isset($data['client_version']) || $data['client_version'] !== client_version)
        {
            Logger::error("Version mismatch in save: expected " . client_version . ", got " . ($data['client_version'] ?? 'null'));
                
            return ['ok' => false, 'error' => 'version'];
        }
    
        return ['ok' => true];
    }
    
    public function save($saveName)
    {
        if ($saveName === '') return;
    
        $client = $this->callForm('Client');
        $diskIo = $client->MainGame->content->ui_disk_io ?? null;
    
        if ($diskIo)
        {
            $diskIo->visible = true;
        }
    
        try {
            if (!is_dir($this->saveDir))
            {
                mkdir($this->saveDir, 0777, true);
            }
    
            $path = $this->saveDir . $saveName . '.sav';
            $data = $this->collectSaveData();
            $json = json_encode($data);
            $encrypted = DimasCryptoZlodey::encryptData($json);
            Stream::putContents($path, $encrypted);
    
            if (defined('Debug_Build') && Debug_Build)
            {
                Logger::info("Saved game: " . $saveName);
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

    public function load($saveName)
    {
        $client = $this->callForm('Client');
        $diskIo = $client->MainGame->content->ui_disk_io ?? null;
    
        if ($diskIo)
        {
            $diskIo->visible = true;
        }
    
        try {
            $path = $this->saveDir . $saveName . '.sav';
            if (!file_exists($path)) return null;
    
            $raw  = Stream::getContents($path);
            $data = json_decode(DimasCryptoZlodey::decryptData($raw), true);
    
            return $data ?: null;
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
        //убрать эту хуйню!!!!!!!!!!!! йй системой загрузки лвла!!!!!!!!!!!!!!!!!!
        $GLOBALS['IsSaveLoading'] = true;     //убрать эту хуйню!!!!!!!!!!!! йй системой загрузки лвла!!!!!!!!!!!!!!!!!!   
//убрать эту хуйню!!!!!!!!!!!! йй системой загрузки лвла!!!!!!!!!!!!!!!!!!
        $form->MainGame->content->ResetGameClient(function () use ($saveData, $saveName, $form)
        {
            $form->MainMenu->content->UILoadWnd->content->ReturnBtn();
            $form->MainMenu->content->BtnStartGame();
            if ($GLOBALS['AllSoundSwitcher_IsOn']) $GLOBALS['AllSounds'] = false;
    
            $form->Pda->content->Pda_Tasks->content->UpdateData();
            $form->Pda->content->Pda_Tasks->content->time_quest_date->text = $saveData['quest_time']['date'];
            $form->Pda->content->Pda_Tasks->content->time_quest_hm->text   = $saveData['quest_time']['hm'];
    
            $form->MainGame->content->item_vodka_0000->visible = $saveData['vodka_exist'];
            if ($form->MainGame->content->ItemVodka->isVisible())
            {
                if ($form->Inventory->content->InventoryGrid->content->selectedItem = $form->Inventory->content->InventoryGrid->content->Inv_Vodka)
                    $form->Inventory->content->InventoryGrid->content->DropItem();
            }
    
            $GLOBALS['QuestStep1']     = $saveData['quest_step1'];
            $GLOBALS['QuestCompleted'] = $saveData['quest_completed'];
            $GLOBALS['NeedToCheckPDA'] = $saveData['need_to_check_pda'];
    
            if (isset($saveData['objects_position']['actor']))
            {
                $form->MainGame->content->GameActor->respawn(
                    $saveData['objects_position']['actor']['x'],
                    $saveData['objects_position']['actor']['y'],
                    false
                );
            }
            if (isset($saveData['objects_position']['enemy']))
            {
                $form->MainGame->content->GameEnemy->respawn(
                    $saveData['objects_position']['enemy']['x'],
                    $saveData['objects_position']['enemy']['y'],
                    false
                );
            }
    
            UXApplication::runLater(function () use ($saveData, $form) {
    
                UXApplication::runLater(function () use ($saveData, $form) {
    
                    if (isset($saveData['ammo']))
                    {
                        $inv = $form->Inventory->content->InventoryGrid->content;
                        $inv->pmAmmoCount = $saveData['ammo']['pm_total'] ?? 0;
                        $inv->akAmmoCount = $saveData['ammo']['ak74_total'] ?? 0;
                    }
    
                    if (isset($saveData['weapons']))
                    {
                        $wep = $saveData['weapons'];
                        $savedList = is_array($wep['list'] ?? null) ? $wep['list'] : [];
                        $desired   = $wep['current'] ?? null;
                        
                        $mg = $form->MainGame->content;
                        
                        $mg->weaponState = $savedList;
                        
                        if ($desired !== null)
                        {
                            $mg->GameActor->SwitchWeapon($desired);
                        
                            $w = $mg->GameActor->getWeapon();
                            if ($w && isset($savedList[$desired]))
                            {
                                $w->importState($savedList[$desired]);
                            }
                        }
                        else
                        {
                            $mg->GameActor->UnequipCurrentWeapon();
                        }
                        
                        $mg->UpdateMagazine();

                    }

                    $form->MainGame->content->UpdateMagazine();
                });
            });   
    
            if (isset($saveData['objects_position']['item_vodka_0000']))
            {
                $form->MainGame->content->item_vodka_0000->position = [
                    $saveData['objects_position']['item_vodka_0000']['x'],
                    $saveData['objects_position']['item_vodka_0000']['y']
                ];
            }
            
            $actorState = $saveData['actors_state']['actor']['dead'] ?? false;
            $enemyState = $saveData['actors_state']['enemy']['dead'] ?? false;
            
            if ($actorState)
            {
                $form->MainGame->content->GameActor->death();
            }
            else
            {
                $form->MainGame->content->GameActor->revive();
            }
            
            if ($enemyState)
            {
                $form->MainGame->content->GameEnemy->death();
            }
            else
            {
                $form->MainGame->content->GameEnemy->revive();
            }
            
            $actorWasDead = $saveData['actors_state']['actor']['dead'] ?? false;
            $enemyWasDead = $saveData['actors_state']['enemy']['dead'] ?? false;
            
            if ($actorWasDead || $enemyWasDead)
            {
                if ($saveData['quest_step1'] == true)
                {
                    $form->Pda->content->Pda_Tasks->content->Step1_Complete();
                }
            
                $form->MainGame->content->finalizeBattle();
            
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

            if ($GLOBALS['QuestStep1'] && !$GLOBALS['QuestCompleted'])
            {
                $form->Dialog->content->Talk_Final();
                $form->MainGame->content->fight_image->show();
            }
    
            if ($form->MainGame->content->MessageBox->visible) $form->MainGame->content->MessageBox->hide();
            if ($form->MainGame->content->Task_Step_Label->visible) $form->MainGame->content->Task_Step_Label->hide();
    
            if (isset($saveData['health']['actor']['hp']))
            {
                $form->MainGame->content->GameActor->setHp((int)$saveData['health']['actor']['hp']);
            }
            
            if (isset($saveData['health']['enemy']['hp']))
            {
                $form->MainGame->content->GameEnemy->setHp((int)$saveData['health']['enemy']['hp']);
            }
            
            UXApplication::runLater(function () use ($form) { //НАСРАЛ
                $form->MainGame->content->GetHealth();
            });
            
            $form->Inventory->content->InventoryGrid->content->medkitCount = $saveData['medkit_count'];
            $form->Inventory->content->InventoryGrid->content->updateMedkitCount();
    
            if (isset($saveData['objects_position']['actor']['is_wearing']))
            {
                $form->Inventory->content->InventoryGrid->content->isWearing = $saveData['objects_position']['actor']['is_wearing'];
                if ($form->Inventory->content->InventoryGrid->content->isWearing)
                {
                    $form->Inventory->content->InventoryGrid->content->selectedItem = $form->Inventory->content->InventoryGrid->content->Inv_Outfit;
                    $form->Inventory->content->InventoryGrid->content->TakeOffItem();
                }
            }
            
            if ($GLOBALS['AllSoundSwitcher_IsOn']) $GLOBALS['AllSounds'] = true;            
    
            $this->waitAndSetPosition($form->MainMenu->content->MainMenuBackground, $saveData['menubackground_playpos']);
            $this->waitAndSetPosition($form->MainMenu->content->MenuSound, $saveData['menusound_playpos']);
            $this->waitAndSetPosition($form->MainGame->content->FightSound, $saveData['fightsound_playpos']);
            
            if (isset($saveData['environment_state']) && is_array($saveData['environment_state']))
            {
                $env = $form->MainGame->content->Environment;
                $env->restoreState($saveData['environment_state'], $saveData['quest_time']['hm']);
                $env->resume();
                
                $ambientPlayer = $env->getAmbientPlayer();
                if ($ambientPlayer && isset($saveData['environment_state']['ambient_position']))
                {
                    $this->waitAndSetPosition($ambientPlayer, $saveData['environment_state']['ambient_position']);
                }              
            }        
            
            $GLOBALS['IsSaveLoading'] = false; //убрать эту хуйню!!!!!!!!!!!! йй системой загрузки лвла!!!!!!!!!!!!!!!!!!
    
            if (Debug_Build) Logger::info("Loaded save: " . $saveName);
        });
    }
    
    function waitAndSetPosition($player, $positionMs, $retries = 10)
    {
        Timer::after(100, function () use ($player, $positionMs, $retries){
            uiLater(function () use ($player, $positionMs, $retries) {
            //TODO: таки разобраться с проверкой на duration
                if ($player->status == 'READY' || $player->status == 'PLAYING')
                {
                    $player->positionMs = $positionMs;
                }
                elseif ($retries > 0)
                {
                    $this->waitAndSetPosition($player, $positionMs, $retries - 1);
                }
            });
        });
    }    

    public function restoreGame(string $saveName): void
    {
        $saveData = $this->load($saveName);
        if ($saveData === null)
        {
            return;
        }

        $result = $this->validateSave($saveData);
        if (!$result['ok'])
        {
            if (Debug_Build)
            {
                Debug::fail("Cannot load save '{$saveName}': " . $result['error'], __FILE__, __LINE__);
            }
            return;
        }

        $this->applySaveData($saveData, $saveName);
    }
}
