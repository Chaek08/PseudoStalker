<?php
namespace app\forms;

use php\gui\UXImage;
use php\gui\UXClipboard;
use Exception;
use php\io\Stream;
use php\gui\UXApplication;
use php\time\Timer;
use php\lib\fs;
use php\io\File;
use php\gui\event\UXWindowEvent;
use php\gui\framework\AbstractForm;
use php\gui\event\UXMouseEvent; 
use php\gui\event\UXEvent; 
use php\lib\Str;
use php\framework\Logger;
use app\forms\classes\Localization;
use app\forms\classes\DimasCryptoZlodey;

class UILoadWnd extends AbstractForm
{
    const SAVE_DIRECTORY = './userdata/savedgames/';

    private $localization;

    public function __construct() 
    {
        parent::__construct();

        $this->localization = new Localization($language);
    }   
    /**
     * @event show 
     */
    function InitLoadWnd(UXWindowEvent $e = null)
    {
        $this->refreshSavesList();

        Timer::every(1000, function () {
            UXApplication::runLater(function () {
                $this->refreshSavesList();
            });
        });
    }
    function refreshSavesList()
    {
        $directory = new File(self::SAVE_DIRECTORY);
        $newItems = [];

        if ($directory->exists())
        {
            $files = $directory->findFiles();
            foreach ($files as $file)
            {
                $ext = fs::ext($file->getName());
                if ($file->isFile() && $ext == 'sav')
                {
                    $newItems[] = fs::nameNoExt($file->getName());
                }
            }
        }

        $currentItems = $this->saves_list->items->toArray();
        if ($newItems !== $currentItems)
        {
            $selected = $this->saves_list->selectedItem;

            $this->saves_list->items->clear();
            $this->saves_list->items->addAll($newItems);

            $index = -1;
            foreach ($newItems as $i => $item)
            {
                if ($item === $selected)
                {
                    $index = $i;
                    break;
                }
            }
            if ($index >= 0)
            {
                $this->saves_list->selectedIndex = $index;
            }
        }
    }
    function keyExists($array, $keys)
    {
        $key = array_shift($keys);
        if (!isset($array[$key]))
        {
            return false;
        }
        if (count($keys) === 0)
        {
            return true;
        }
        return $this->keyExists($array[$key], $keys);
    }
    function waitAndSetPosition($player, $positionMs, $retries = 10)
    {
        Timer::after(100, function () use ($player, $positionMs, $retries){
            uiLater(function () use ($player, $positionMs, $retries) {
            //TODO: таки разобраться с проверкой на duration
                if ($player->status === 'READY' || $player->status === 'PLAYING')
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
    /**
     * @event Return_Btn.click-Left 
     */
    function ReturnBtn(UXMouseEvent $e = null)
    {
        $this->form('maingame')->MainMenu->content->dynamic_background->toBack();
        $this->form('maingame')->MainMenu->content->UILoadWnd->hide();
    }    
    /**
     * @event saves_list.action 
     */
    function ShowSavePreview(UXEvent $e = null)
    {
        $this->localization->setLanguage($this->form('maingame')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value);
    
        $selectedSave = $this->saves_list->selectedItem;
        
        if (empty($selectedSave))
        {
            $this->HideSavePreview();
            return;
        }
        
        $imagePath = self::SAVE_DIRECTORY . $selectedSave . '.jpg';
        $saveFile = self::SAVE_DIRECTORY . $selectedSave . '.sav';

        if (file_exists($imagePath))
        {
            $this->noise->hide();
            $this->save_image->show();
            $this->save_image->image = new UXImage($imagePath);
        }
        else
        {
            $this->save_image->hide();
            $this->noise->show();
        }
        
        $encryptedData = Stream::getContents($saveFile);
        $saveData = json_decode(DimasCryptoZlodey::decryptData($encryptedData), true);
        
        $this->savedata_name->show();
        $this->savedata_time->show();
        $this->savedata_health->show();
        
        $this->savedata_name->text = $selectedSave;
        $this->savedata_health->text = $this->localization->get('SaveData_Health_Label') . ' : ' . ($saveData['health']['gg']['value'] ?? '---%'); 
        $this->savedata_time->text = $this->localization->get('SaveData_Time_Label') . ' : ' . ($saveData['quest_time']['hm'] ?? '--:--') . '  ' . ($saveData['quest_time']['date'] ?? '--/--/----');
    }
    function HideSavePreview()
    {
        $this->noise->show();
        
        $this->save_image->hide();
        $this->savedata_health->hide();
        $this->savedata_time->hide();
        $this->savedata_name->hide();
    }    
    /**
     * @event Load_Btn.click-Left 
     */
    function BtnLoadSave(UXMouseEvent $e = null)
    {
        $this->localization->setLanguage($this->form('maingame')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value);
        
        $saveName = $this->saves_list->selectedItem;
        
        $filePath = self::SAVE_DIRECTORY . $saveName . '.sav';
        $encryptedData = Stream::getContents($filePath);
        $saveData = json_decode(DimasCryptoZlodey::decryptData($encryptedData), true);
                
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
            'quest_step1',
            'quest_completed',
            'actor_failed',
            'enemy_failed',
            'need_to_check_pda',
            'menubackground_playpos',
            'menusound_playpos',
            'mainambientsound_playpos',
            'fightsound_playpos',
        ];
        foreach ($requiredKeys as $key)
        {
            if (!$this->keyExists($saveData, explode('.', $key)))
            {
                $this->form('maingame')->toast($this->localization->get('SaveCorruptToast'));
                return;
            }
        }
        foreach ($saveData as $key => $value)
        {
            if (!in_array($key, $requiredKeys))
            {
                $this->form('maingame')->toast($this->localization->get('SaveCorruptToast'));
                return;
            }
        }
        
        if ($saveData['client_version'] !== client_version)
        {
            $this->form('maingame')->toast($this->localization->get('InvalidGameClientToast')); 
            if (Debug_Build) Logger::error("Invalid client version!");
            return;
        }
        
        $this->form('maingame')->ResetGameClient();
        $this->form('maingame')->MainMenu->content->UILoadWnd->content->ReturnBtn();
        $this->form('maingame')->MainMenu->content->BtnStartGame_MouseDownLeft();
        $this->form('maingame')->MainMenu->content->BtnStartGame_MouseExit();
        if ($GLOBALS['AllSoundSwitcher_IsOn']) $GLOBALS['AllSounds'] = false;
        
        $this->form('maingame')->Pda->content->Pda_Tasks->content->time_quest_date->text = $saveData['quest_time']['date'];
        $this->form('maingame')->Pda->content->Pda_Tasks->content->time_quest_hm->text = $saveData['quest_time']['hm'];
        $this->form('maingame')->item_vodka_0000->visible = $saveData['vodka_exist'];
        if ($this->form('maingame')->item_vodka_0000->visible) $this->form('maingame')->Inventory->content->DropVodka();
        
        $GLOBALS['QuestStep1'] = $saveData['quest_step1'];
        $GLOBALS['QuestCompleted'] = $saveData['quest_completed'];
        $GLOBALS['ActorFailed'] = $saveData['actor_failed'];
        $GLOBALS['EnemyFailed'] = $saveData['enemy_failed'];
        $GLOBALS['NeedToCheckPDA'] = $saveData['need_to_check_pda'];
        
        if (isset($saveData['objects_position']['actor']))
        {
            $this->form('maingame')->actor->position = [
            $saveData['objects_position']['actor']['x'],
            $saveData['objects_position']['actor']['y']
            ];
        }
        if (isset($saveData['objects_position']['enemy']))
        {
            $this->form('maingame')->enemy->position = [
            $saveData['objects_position']['enemy']['x'],
            $saveData['objects_position']['enemy']['y']
            ];
        }
        if (isset($saveData['objects_position']['item_vodka_0000']))
        {
            $this->form('maingame')->item_vodka_0000->position = [
            $saveData['objects_position']['item_vodka_0000']['x'],
            $saveData['objects_position']['item_vodka_0000']['y']
            ];
        }
        if ($GLOBALS['ActorFailed'] || $GLOBALS['EnemyFailed'])
        {
            if ($saveData['quest_step1'] == true) $this->form('maingame')->Pda->content->Pda_Tasks->content->Step1_Complete();
            $this->form('maingame')->finalizeBattle();
            
            $this->form('maingame')->Pda->content->Pda_Ranking->content->DeathFilter();
            if ($this->form('maingame')->Fail->visible) $this->form('maingame')->Fail->content->ReturnBtn();
            if ($saveData['need_to_check_pda'] == false) $this->form('maingame')->Pda->content->Pda_Tasks->content->Step_DeletePda();
        }
        if ($GLOBALS['QuestStep1'] && !$GLOBALS['QuestCompleted']) 
        {
            $this->form('maingame')->Dialog->content->Talk_3();
        }
        
        $this->form('maingame')->GetHealth();
        $this->form('maingame')->health_bar_gg->text = $saveData['health']['gg']['value'];
        $this->form('maingame')->health_bar_gg->width = $saveData['health']['gg']['pb_width'];
        $this->form('maingame')->Inventory->content->health_bar_gg->width = $saveData['health_gg_inv']['pb_width'];
        $this->form('maingame')->Inventory->content->health_bar_gg->text = $saveData['health_gg_inv']['value'];
        $this->form('maingame')->health_bar_enemy->text = $saveData['health']['enemy']['value'];
        $this->form('maingame')->health_bar_enemy->width = $saveData['health']['enemy']['pb_width'];
        $this->form('maingame')->Bleeding();
        
        $this->waitAndSetPosition($this->form('maingame')->MainMenu->content->MainMenuBackground, $saveData['menubackground_playpos']);
        $this->waitAndSetPosition($this->form('maingame')->MainMenu->content->MenuSound, $saveData['menusound_playpos']);
        $this->waitAndSetPosition($this->form('maingame')->MainAmbient, $saveData['mainambientsound_playpos']);
        $this->waitAndSetPosition($this->form('maingame')->FightSound, $saveData['fightsound_playpos']);
        
        if ($GLOBALS['AllSoundSwitcher_IsOn']) $GLOBALS['AllSounds'] = true;
                
        if (Debug_Build) Logger::info("Loaded save: " . $saveName);
    }
    /**
     * @event Remove_Save_Btn.click-Left 
     */
    function RemoveSaveBtn(UXMouseEvent $e = null)
    {
        $selectedSave = $this->saves_list->selectedItem;

        if ($selectedSave != '')
        {
            $filePath = self::SAVE_DIRECTORY . $selectedSave . '.sav';
            $imagePath = self::SAVE_DIRECTORY . $selectedSave . '.jpg';

            if (file_exists($filePath))
            {
                unlink($filePath);
            }

            if (file_exists($imagePath))
            {
                unlink($imagePath);
            }

            $items = $this->saves_list->items->toArray();
            $index = -1;

            foreach ($items as $i => $item)
            {
                if ($item === $selectedSave)
                {
                    $index = $i;
                    break;
                }
            }

            $this->saves_list->items->remove($selectedSave);

            $count_loadgamelist = count($this->saves_list->items);
            if ($count_loadgame > 0)
            {
                if ($index >= $count_loadgamelist) //!!! index хуйню javafx пидорасит !!!
                {
                    $index = $count_loadgamelist - 1;
                }         
                elseif ($index < 0)
                {
                    $index = 0;
                }
                $this->saves_list->selectedIndex = $index;
                $this->saves_list->scrollTo($index);
            }

            $this->ShowSavePreview();
        }
    }
    /**
     * @event saves_list.click-2x 
     */
    function ProcessSaveClick(UXMouseEvent $e = null)
    {    
        $this->BtnLoadSave();
    }
}
