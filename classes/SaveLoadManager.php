<?php
namespace app\forms\classes;

use php\time\Timer;
use php\framework\Logger;
use php\io\Stream;
use app\forms\classes\DimasCryptoZlodey;

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
        foreach ($keys as $key) {
            if (!is_array($array) || !array_key_exists($key, $array)) {
                return false;
            }
            $array = $array[$key];
        }
        return true;
    }

    public function collectSaveData()
    {
        $c = $this->callForm('Client');

        return array(
            'client_version' => client_version,
            'ammo' => array(
                'pm_mag'     => $c->MainGame->content->pmAmmo,
                'ak74_mag'   => $c->MainGame->content->ak74Ammo,
                'pm_total'   => $c->Inventory->content->InventoryGrid->content->pmAmmoCount,
                'ak74_total' => $c->Inventory->content->InventoryGrid->content->akAmmoCount,
            ),
            'weapons_jam_state' => array(
                'Pm' => array(
                    'jammed'     => isset($this->weaponData['Pm']['jammed']) ? $this->weaponData['Pm']['jammed'] : false,
                    'jamHandled' => isset($this->weaponData['Pm']['jamHandled']) ? $this->weaponData['Pm']['jamHandled'] : false,
                ),
                'AK74' => array(
                    'jammed'     => isset($this->weaponData['AK74']['jammed']) ? $this->weaponData['AK74']['jammed'] : false,
                    'jamHandled' => isset($this->weaponData['AK74']['jamHandled']) ? $this->weaponData['AK74']['jamHandled'] : false,
                ),
            ),
            'current_weapon' => isset($c->MainGame->content->CurrentWeaponType) ? $c->MainGame->content->CurrentWeaponType : null,
            'health_gg_inv' => array(
                'value'    => $c->Inventory->content->health_bar_gg->text,
                'pb_width' => $c->Inventory->content->health_bar_gg->width,
            ),
            'health' => array(
                'gg' => array(
                    'value'    => $c->MainGame->content->health_bar_gg->text,
                    'pb_width' => $c->MainGame->content->health_bar_gg->width,
                ),
                'enemy' => array(
                    'value'    => $c->MainGame->content->health_bar_enemy->text,
                    'pb_width' => $c->MainGame->content->health_bar_enemy->width,
                ),
            ),
            'objects_position' => array(
                'actor' => array(
                    'x' => $c->MainGame->content->actor->position[0],
                    'y' => $c->MainGame->content->actor->position[1],
                    'is_wearing' => $c->Inventory->content->InventoryGrid->content->isWearing,
                ),
                'enemy' => array(
                    'x' => $c->MainGame->content->enemy->position[0],
                    'y' => $c->MainGame->content->enemy->position[1],
                ),
                'item_vodka_0000' => array(
                    'x' => $c->MainGame->content->item_vodka_0000->position[0],
                    'y' => $c->MainGame->content->item_vodka_0000->position[1],
                ),
            ),
            'quest_time' => array(
                'date' => $c->Pda->content->Pda_Tasks->content->time_quest_date->text,
                'hm'   => $c->Pda->content->Pda_Tasks->content->time_quest_hm->text,
            ),
            'vodka_exist'      => $c->MainGame->content->item_vodka_0000->visible,
            'medkit_count'     => $c->Inventory->content->InventoryGrid->content->medkitCount,
            'quest_step1'      => isset($GLOBALS['QuestStep1']) ? $GLOBALS['QuestStep1'] : false,
            'quest_completed'  => isset($GLOBALS['QuestCompleted']) ? $GLOBALS['QuestCompleted'] : false,
            'actor_failed'     => isset($GLOBALS['ActorFailed']) ? $GLOBALS['ActorFailed'] : false,
            'enemy_failed'     => isset($GLOBALS['EnemyFailed']) ? $GLOBALS['EnemyFailed'] : false,
            'need_to_check_pda'=> isset($GLOBALS['NeedToCheckPDA']) ? $GLOBALS['NeedToCheckPDA'] : false,
            'menubackground_playpos' => $c->MainMenu->content->MainMenuBackground->positionMs,
            'menusound_playpos'      => $c->MainMenu->content->MenuSound->positionMs,
            'environment_playpos'    => $c->MainGame->content->Environment->positionMs,
            'fightsound_playpos'     => $c->MainGame->content->FightSound->positionMs,
        );
    }

    public function validateSave(array $data): array
    {
        $requiredKeys = [
            'client_version',
            'health',
            'health.gg',
            'health_gg_inv',
            'health_gg_inv.value',
            'health_gg_inv.pb_width',
            'health.gg.value',
            'health.gg.pb_width',
            'health.enemy',
            'health.enemy.value',
            'health.enemy.pb_width',
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
            'actor_failed',
            'enemy_failed',
            'need_to_check_pda',
            'menubackground_playpos',
            'menusound_playpos',
            'environment_playpos',
            'fightsound_playpos',
            'ammo',
            'ammo.pm_mag',
            'ammo.ak74_mag',
            'ammo.pm_total',
            'ammo.ak74_total',
            'current_weapon',
            'weapons_jam_state',
            'weapons_jam_state.Pm',
            'weapons_jam_state.Pm.jammed',
            'weapons_jam_state.Pm.jamHandled',
            'weapons_jam_state.AK74',
            'weapons_jam_state.AK74.jammed',
            'weapons_jam_state.AK74.jamHandled',
        ];

        foreach ($requiredKeys as $key)
        {
            $parts = explode('.', $key);
            if (!$this->keyExists($data, $parts))
            {
                if (Debug_Build) 
                {
                    Logger::error("Corrupt save: missing key '$key'");
                }
                return ['ok' => false, 'error' => 'corrupt', 'missing' => $key];
            }

        }

        if (!isset($data['client_version']) || $data['client_version'] !== client_version)
        {
            if (Debug_Build)
            {
                Logger::error("Version mismatch in save: expected " . client_version . ", got " . ($data['client_version'] ?? 'null'));
            }
            return ['ok' => false, 'error' => 'version'];
        }

        return ['ok' => true];
    }

    public function save($saveName)
    {
        if ($saveName === '') return;

        if (!is_dir($this->saveDir)) {
            mkdir($this->saveDir, 0777, true);
        }

        $path = $this->saveDir . $saveName . '.sav';
        $data = $this->collectSaveData();
        $json = json_encode($data);
        $encrypted = DimasCryptoZlodey::encryptData($json);
        Stream::putContents($path, $encrypted);

        if (defined('Debug_Build') && Debug_Build) {
            Logger::info("Saved game: " . $saveName);
        }
    }

    public function load($saveName)
    {
        $path = $this->saveDir . $saveName . '.sav';
        if (!file_exists($path)) return null;

        $raw  = Stream::getContents($path);
        $data = json_decode(DimasCryptoZlodey::decryptData($raw), true);

        return $data ?: null;
    }

    public function applySaveData(array $saveData, string $saveName): void
    {
        $form = $this->callForm('Client');

        $form->MainGame->content->ResetGameClient(function () use ($saveData, $saveName, $form)
        {
            $form->MainMenu->content->UILoadWnd->content->ReturnBtn();
            $form->MainMenu->content->BtnStartGame();
            if ($GLOBALS['AllSoundSwitcher_IsOn']) $GLOBALS['AllSounds'] = false;
    
            $form->Pda->content->Pda_Tasks->content->UpdateData();
            $form->Pda->content->Pda_Tasks->content->time_quest_date->text = $saveData['quest_time']['date'];
            $form->Pda->content->Pda_Tasks->content->time_quest_hm->text   = $saveData['quest_time']['hm'];
    
            $form->MainGame->content->item_vodka_0000->visible = $saveData['vodka_exist'];
            if ($form->MainGame->content->item_vodka_0000->visible)
            {
                if ($form->Inventory->content->InventoryGrid->content->selectedItem = $form->Inventory->content->InventoryGrid->content->Inv_Vodka)
                    $form->Inventory->content->InventoryGrid->content->DropItem();
            }
    
            $GLOBALS['QuestStep1']     = $saveData['quest_step1'];
            $GLOBALS['QuestCompleted'] = $saveData['quest_completed'];
            $GLOBALS['ActorFailed']    = $saveData['actor_failed'];
            $GLOBALS['EnemyFailed']    = $saveData['enemy_failed'];
            $GLOBALS['NeedToCheckPDA'] = $saveData['need_to_check_pda'];
    
            if (isset($saveData['objects_position']['actor']))
            {
                $form->MainGame->content->actor->position = [
                    $saveData['objects_position']['actor']['x'],
                    $saveData['objects_position']['actor']['y']
                ];
            }
            if (isset($saveData['objects_position']['enemy']))
            {
                $form->MainGame->content->enemy->position = [
                    $saveData['objects_position']['enemy']['x'],
                    $saveData['objects_position']['enemy']['y']
                ];
            }
    
            if (isset($saveData['ammo']))
            {
                $form->MainGame->content->pmAmmo   = $saveData['ammo']['pm_mag'];
                $form->MainGame->content->ak74Ammo = $saveData['ammo']['ak74_mag'];
                $form->Inventory->content->InventoryGrid->content->pmAmmoCount = $saveData['ammo']['pm_total'];
                $form->Inventory->content->InventoryGrid->content->akAmmoCount = $saveData['ammo']['ak74_total'];
            }
    
            if (isset($saveData['weapons_jam_state']))
            {
                $this->weaponData['Pm']['jammed']       = $saveData['weapons_jam_state']['Pm']['jammed'];
                $this->weaponData['Pm']['jamHandled']   = $saveData['weapons_jam_state']['Pm']['jamHandled'];
                $this->weaponData['AK74']['jammed']     = $saveData['weapons_jam_state']['AK74']['jammed'];
                $this->weaponData['AK74']['jamHandled'] = $saveData['weapons_jam_state']['AK74']['jamHandled'];
            }
    
            $form->MainGame->content->CurrentWeaponType = 'AK74';
            $form->MainGame->content->DetachWeapon('AK74');
    
            $form->MainGame->content->CurrentWeaponType = 'Pm';
            $form->MainGame->content->DetachWeapon('Pm');
    
            $form->MainGame->content->CurrentWeaponType = null;
    
            if (isset($saveData['current_weapon']))
            {
                $this->CurrentWeaponType = $saveData['current_weapon'];
            }
    
            if ($saveData['current_weapon'] == 'Pm')
            {
                $form->SwitchWeapon1();
            }
            elseif ($saveData['current_weapon'] == 'AK74')
            {
                $form->SwitchWeapon2();
            }
    
            if (isset($saveData['objects_position']['item_vodka_0000']))
            {
                $form->MainGame->content->item_vodka_0000->position = [
                    $saveData['objects_position']['item_vodka_0000']['x'],
                    $saveData['objects_position']['item_vodka_0000']['y']
                ];
            }
    
            if ($GLOBALS['ActorFailed'] || $GLOBALS['EnemyFailed'])
            {
                if ($saveData['quest_step1'] == true) $form->Pda->content->Pda_Tasks->content->Step1_Complete();
                $form->MainGame->content->finalizeBattle();
    
                $form->Pda->content->Pda_Ranking->content->DeathFilter();
                if ($form->Fail->visible) $form->Fail->content->ReturnBtn();
                if ($saveData['need_to_check_pda'] == false) $form->Pda->content->Pda_Tasks->content->Step_DeletePda();
            }
    
            if ($GLOBALS['QuestStep1'] && !$GLOBALS['QuestCompleted'])
            {
                $form->Dialog->content->Talk_Final();
                $form->MainGame->content->fight_image->show();
            }
    
            if ($form->MainGame->content->MessageBox->visible) $form->MainGame->content->MessageBox->hide();
            if ($form->MainGame->content->Task_Step_Label->visible) $form->MainGame->content->Task_Step_Label->hide();
    
            $form->MainGame->content->InitEnvironmentTimer($saveData['quest_time']['hm']);
            $form->MainGame->content->UpdateEnvironment($saveData['quest_time']['hm']);
    
            $form->MainGame->content->GetHealth();
            $form->MainGame->content->health_bar_gg->text  = $saveData['health']['gg']['value'];
            $form->MainGame->content->health_bar_gg->width = $saveData['health']['gg']['pb_width'];
            $form->Inventory->content->health_bar_gg->width = $saveData['health_gg_inv']['pb_width'];
            $form->Inventory->content->health_bar_gg->text  = $saveData['health_gg_inv']['value'];
            $form->MainGame->content->health_bar_enemy->text  = $saveData['health']['enemy']['value'];
            $form->MainGame->content->health_bar_enemy->width = $saveData['health']['enemy']['pb_width'];
            $form->MainGame->content->Bleeding();
    
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
    
            $this->waitAndSetPosition($form->MainMenu->content->MainMenuBackground, $saveData['menubackground_playpos']);
            $this->waitAndSetPosition($form->MainMenu->content->MenuSound, $saveData['menusound_playpos']);
            $this->waitAndSetPosition($form->MainGame->content->Environment, $saveData['environment_playpos']);
            $this->waitAndSetPosition($form->MainGame->content->FightSound, $saveData['fightsound_playpos']);
    
            $form->MainGame->content->PlayEnvironment();
    
            if ($GLOBALS['AllSoundSwitcher_IsOn']) $GLOBALS['AllSounds'] = true;
    
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
            return;
        }

        $this->applySaveData($saveData, $saveName);
    }
}
