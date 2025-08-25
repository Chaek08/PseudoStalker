<?php
namespace app\forms;

use php\lang\System;
use php\framework\Logger;
use php\gui\UXImageView;
use php\desktop\Robot;
use php\gui\UXClipboard;
use php\gui\UXApplication;
use php\gui\UXDialog;
use php\gui\framework\AbstractForm;
use php\gui\event\UXMouseEvent; 
use php\io\Stream;
use php\io\File;
use php\lib\fs;
use php\gui\event\UXEvent; 
use php\gui\event\UXWindowEvent;
use php\time\Timer;
use php\lib\Str;
use app\forms\classes\Localization;
use app\forms\classes\DimasCryptoZlodey;
use php\gui\event\UXKeyEvent; 

class UISaveWnd extends AbstractForm
{
    private $localization;
    
    private $saveHistory = []; 
    private $historyIndex = -1;    

    public function __construct()
    {
        parent::__construct();

        $this->localization = new Localization($language);
        
        define("SAVE_DIRECTORY", "./userdata/savedgames/");
    }
    
    function getCurrentLanguageFromUI()
    {
        return $this->form('Client')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value;
    }    
        
    /**
     * @event show 
     */
    function InitSaveWnd(UXWindowEvent $e = null)
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
        $directory = new File(SAVE_DIRECTORY);
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
    function saveScreenshot()
    {
        $client = $this->form('Client');
        $mainMenu = $client->MainMenu;
        $exitDialog = $client->ExitDialog;
        $console = $client->Console;
        $layout = $client->layout;

        if ($mainMenu->content->UISaveWnd->visible) $mainMenu->hide();
        if ($exitDialog->visible) $exitDialog->hide();
        $client->CustomCursor->hide();
        if (!$GLOBALS['HudVisible'] && $mainMenu->content->UISaveWnd->visible) $client->MainGame->content->RenderHud(true);
        if ($console->visible) $console->opacity = 0;

        $formWidth = $client->Client_Proxy->width;
        $formHeight = $client->Client_Proxy->height;

        $originalX = $console->x;
        $originalY = $console->y;

        if ($console->x < 0)
        {
            $console->x = 0;
        }
        elseif ($console->x + $console->width > $formWidth)
        {
            $console->x = $formWidth - $console->width;
        }

        if ($console->y < 0)
        {
            $console->y = 0;
        }
        elseif ($console->y + $console->height > $formHeight)
        {
            $console->y = $formHeight - $console->height;
        }

        $image = $layout->snapshot();

        $console->x = $originalX;
        $console->y = $originalY;

        if ($mainMenu->content->UISaveWnd->visible) $mainMenu->show();
        $client->CustomCursor->show();
        if ($GLOBALS['HudVisible'] && $mainMenu->content->UISaveWnd->visible) $client->MainGame->content->RenderHud(false);
        if ($console->visible) $console->opacity = 100;

        $imageView = new UXImageView($image);
        $imageView->scaleX = 168 / 1600;
        $imageView->scaleY = 104 / 900;

        $scaledImage = $imageView->snapshot();

        $saveName = $this->Edit_SaveName->text;
        $path = SAVE_DIRECTORY . $saveName . '.jpg';
        $scaledImage->save(new File($path));
    }
    /**
     * @event Return_Btn.click-Left 
     */
    function ReturnBtn(UXMouseEvent $e = null)
    {
        $this->form('Client')->MainMenu->content->dynamic_background->toBack();
        $this->form('Client')->MainMenu->content->UISaveWnd->hide();
    }    
    /**
     * @event saves_list.action 
     */
    function SelectSave(UXEvent $e = null)
    {    
        $selectedSave = $this->saves_list->selectedItem;        
        if ($selectedSave)
        {
            $this->Edit_SaveName->text = $selectedSave;
        }
    }    
    /**
     * @event Save_Btn.click-Left 
     */
    function BtnSaveGame(UXMouseEvent $e = null)
    {
        $this->saveHistory[] = trim($this->Edit_SaveName->text);
        $this->historyIndex = count($this->saveHistory);
    
        $saveName = trim($this->Edit_SaveName->text);
        if ($saveName != '')
        {
            $fileName = $saveName . '.sav'; 
            $filePath = SAVE_DIRECTORY . $fileName;
            
            if (file_exists($filePath) && $saveName != System::getProperty('user.name') . '_quicksave' && !$GLOBALS['AutoRewriteSave'])
            {
                if (!$this->form('Client')->ExitDialog->visible)
                {
                    $this->form('Client')->ExitDialog->content->UpdateDialogWnd();
                    $GLOBALS['RewriteSaveType'] = true;
                    $this->form('Client')->ExitDialog->content->SetDialogWndType();
                    $this->form('Client')->ExitDialog->show();
                    
                    return;
                }
            }
            if (!file_exists(SAVE_DIRECTORY))
            {
                mkdir(SAVE_DIRECTORY, 0777, true);
            }
            
            $saveData = [
                'client_version' => client_version,

                'ammo' => [
                'pm_mag'     => $this->form('Client')->MainGame->content->pmAmmo,
                'ak74_mag'   => $this->form('Client')->MainGame->content->ak74Ammo,
                'pm_total'   => $this->form('Client')->Inventory->content->InventoryGrid->content->pmAmmoCount,
                'ak74_total' => $this->form('Client')->Inventory->content->InventoryGrid->content->akAmmoCount,
                ],

                'weapons_jam_state' => [
                    'Pm' => [
                        'jammed'     => $this->weaponData['Pm']['jammed'] ?? false,
                        'jamHandled' => $this->weaponData['Pm']['jamHandled'] ?? false,
                    ],
                    'AK74' => [
                        'jammed'     => $this->weaponData['AK74']['jammed'] ?? false,
                        'jamHandled' => $this->weaponData['AK74']['jamHandled'] ?? false,
                    ],
                ],

                'current_weapon' => $this->form('Client')->MainGame->content->CurrentWeaponType ?? null,

                'health_gg_inv' => [
                    'value'    => $this->form('Client')->Inventory->content->health_bar_gg->text,
                    'pb_width' => $this->form('Client')->Inventory->content->health_bar_gg->width,
                ],
                'health' => [
                    'gg' => [ 
                        'value'    => $this->form('Client')->MainGame->content->health_bar_gg->text,
                        'pb_width' => $this->form('Client')->MainGame->content->health_bar_gg->width,
                        ],
                    'enemy' => [
                        'value'    => $this->form('Client')->MainGame->content->health_bar_enemy->text,
                        'pb_width' => $this->form('Client')->MainGame->content->health_bar_enemy->width,
                        ]
                ],

                'objects_position' => [
                    'actor' => [
                        'x'          => $this->form('Client')->MainGame->content->actor->position[0],
                        'y'          => $this->form('Client')->MainGame->content->actor->position[1],
                        'is_wearing' => $this->form('Client')->Inventory->content->InventoryGrid->content->isWearing,
                    ],
                    'enemy' => [
                        'x' => $this->form('Client')->MainGame->content->enemy->position[0],
                        'y' => $this->form('Client')->MainGame->content->enemy->position[1],
                    ],
                    'item_vodka_0000' => [
                        'x' => $this->form('Client')->MainGame->content->item_vodka_0000->position[0],
                        'y' => $this->form('Client')->MainGame->content->item_vodka_0000->position[1],
                    ],
                ],

                'quest_time' => [
                    'date' => $this->form('Client')->Pda->content->Pda_Tasks->content->time_quest_date->text,
                    'hm'   => $this->form('Client')->Pda->content->Pda_Tasks->content->time_quest_hm->text,
                ],

                'vodka_exist'   => $this->form('Client')->MainGame->content->item_vodka_0000->visible,
                'medkit_count'  => $this->form('Client')->Inventory->content->InventoryGrid->content->medkitCount,
                'quest_step1'   => $GLOBALS['QuestStep1']      ?? false,
                'quest_completed' => $GLOBALS['QuestCompleted'] ?? false,
                'actor_failed'    => $GLOBALS['ActorFailed']    ?? false,
                'enemy_failed'    => $GLOBALS['EnemyFailed']    ?? false,
                'need_to_check_pda' => $GLOBALS['NeedToCheckPDA'] ?? false,
            
                'menubackground_playpos' => $this->form('Client')->MainMenu->content->MainMenuBackground->positionMs,
                'menusound_playpos'      => $this->form('Client')->MainMenu->content->MenuSound->positionMs,
                'environment_playpos'    => $this->form('Client')->MainGame->content->Environment->positionMs,    'fightsound_playpos'     => $this->form('Client')->MainGame->content->FightSound->positionMs,
            ];
        
            $encryptedData = DimasCryptoZlodey::encryptData(json_encode($saveData, JSON_PRETTY_PRINT));
            $this->saveScreenshot();
            Stream::putContents($filePath, $encryptedData);
            
            $this->saves_list->items->add($saveName);
            if (Debug_Build) Logger::info("Saved game: " . $saveName);
                
            $this->Edit_SaveName->text = '';
        }
    }
    /**
     * @event Remove_Save_Btn.click-Left 
     */
    function RemoveSaveBtn(UXMouseEvent $e = null)
    {
        $selectedSave = $this->saves_list->selectedItem;

        if ($selectedSave != '')
        {
            if (!$this->form('Client')->ExitDialog->visible)
            {
                $this->form('Client')->ExitDialog->content->UpdateDialogWnd();
                $GLOBALS['RemoveSaveType'] = true;
                $this->form('Client')->ExitDialog->content->SetDialogWndType();
                $this->form('Client')->ExitDialog->show();
                
                return;
            }
                    
            $filePath = SAVE_DIRECTORY . $selectedSave . '.sav';
            $imagePath = SAVE_DIRECTORY . $selectedSave . '.jpg';

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

            $count = count($this->saves_list->items);
            if ($count > 0)
            {
                if ($index >= $count) //!!! index хуйню javafx пидорасит !!!
                {
                    $index = $count - 1;
                }         
                elseif ($index < 0)
                {
                    $index = 0;
                }
                $this->saves_list->selectedIndex = $index;
                $this->saves_list->scrollTo($index);
            }
            else 
            {
                $this->Edit_SaveName->text = '';
            }
        }
    }
    /**
     * @event Edit_SaveName.keyDown-Up
     */
    function handleArrowUp(UXKeyEvent $e) 
    {    
        if (!empty($this->saveHistory) && $this->historyIndex > 0)
        {
            $this->historyIndex--;
            $this->Edit_SaveName->text = $this->saveHistory[$this->historyIndex];
        }
        elseif ($this->historyIndex == -1 && !empty($this->saveHistory))
        {
            $this->historyIndex = count($this->saveHistory) - 1;
            $this->Edit_SaveName->text = $this->saveHistory[$this->historyIndex];
        }
        uiLater(function() {
           $this->Edit_SaveName->positionCaret(strlen($this->Edit_SaveName->text));
        });
    }
    /**
     * @event Edit_SaveName.keyDown-Down
     */
    function handleArrowDown(UXKeyEvent $e) 
    {    
        if ($this->historyIndex < count($this->saveHistory) - 1)
        {
            $this->historyIndex++;
            $this->Edit_SaveName->text = $this->saveHistory[$this->historyIndex];
        }
        else
        {
            $this->historyIndex = count($this->saveHistory); 
            $this->Edit_SaveName->text = "";
        }
        uiLater(function() {
           $this->Edit_SaveName->positionCaret(strlen($this->Edit_SaveName->text));
        });
    }    
    /**
     * @event Edit_SaveName.keyDown-Enter 
     */
    function HotkeySaveBtn(UXKeyEvent $e = null)
    {    
        $this->BtnSaveGame();
    }
}
