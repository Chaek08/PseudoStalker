<?php
namespace app\forms;

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
    function saveScreenshot()
    {
        $this->form('maingame')->MainMenu->hide();
        $this->form('maingame')->CustomCursor->hide();
        if (!$GLOBALS['HudVisible'])
        { 
            $this->form('maingame')->ToggleHud();
        }

        $image = $this->form('maingame')->layout->snapshot();

        $this->form('maingame')->CustomCursor->show();
        $this->form('maingame')->MainMenu->show();
        if ($GLOBALS['HudVisible'])
        { 
            $this->form('maingame')->ToggleHud();
        }
        
        $imageView = new UXImageView($image);

        $imageView->scaleX = 168 / 1600;
        $imageView->scaleY = 104 / 900;
        
        $scaledImage = $imageView->snapshot();

        $saveName = $this->Edit_SaveName->text;
        $path = self::SAVE_DIRECTORY . $saveName . '.jpg';
        $scaledImage->save(new File($path));
    }
    /**
     * @event Return_Btn.click-Left 
     */
    function ReturnBtn(UXMouseEvent $e = null)
    {
        $this->form('maingame')->MainMenu->content->dynamic_background->toBack();
        $this->form('maingame')->MainMenu->content->UISaveWnd->hide();
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
        $this->localization->setLanguage($this->form('maingame')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value);
    
        $saveName = $this->Edit_SaveName->text;
        if ($saveName != '')
        {
            $fileName = $saveName . '.sav'; 
            $filePath = self::SAVE_DIRECTORY . $fileName;
            
            if (file_exists($filePath))
            {
                $this->form('maingame')->toast($this->localization->get('brainAFKToast'));
                return; 
            }
            if (!file_exists(self::SAVE_DIRECTORY))
            {
                mkdir(self::SAVE_DIRECTORY, 0777, true);
            }
            if (!file_exists($filePath))
            {
                $saveData = [
                    'client_version' => client_version,
                    'health_gg_inv' => [
                            'value' => $this->form('maingame')->Inventory->content->health_bar_gg->text,
                            'pb_width' => $this->form('maingame')->Inventory->content->health_bar_gg->width,
                    ],
                    'health' => [
                        'gg' => [ 
                            'value' => $this->form('maingame')->health_bar_gg->text,
                            'pb_width' => $this->form('maingame')->health_bar_gg->width,
                        ],
                        'enemy' => [
                            'value' => $this->form('maingame')->health_bar_enemy->text,
                            'pb_width' => $this->form('maingame')->health_bar_enemy->width,
                        ]
                    ],
                    'objects_position' => [
                        'actor' => [
                            'x' => $this->form('maingame')->actor->position[0],
                            'y' => $this->form('maingame')->actor->position[1],
                        ],
                        'enemy' => [
                            'x' => $this->form('maingame')->enemy->position[0],
                            'y' => $this->form('maingame')->enemy->position[1],
                        ],
                        'item_vodka_0000' => [
                            'x' => $this->form('maingame')->item_vodka_0000->position[0],
                            'y' => $this->form('maingame')->item_vodka_0000->position[1],
                        ],
                    ],
                    'quest_time' => [
                        'date' => $this->form('maingame')->Pda->content->Pda_Tasks->content->time_quest_date->text,
                        'hm' => $this->form('maingame')->Pda->content->Pda_Tasks->content->time_quest_hm->text,
                    ],
                    'vodka_exist' => $this->form('maingame')->item_vodka_0000->visible,
                    'quest_step1' => isset($GLOBALS['QuestStep1']) ? $GLOBALS['QuestStep1'] : false,
                    'quest_completed' => isset($GLOBALS['QuestCompleted']) ? $GLOBALS['QuestCompleted'] : false,
                    'actor_failed' => isset($GLOBALS['ActorFailed']) ? $GLOBALS['ActorFailed'] : false,
                    'enemy_failed' => isset($GLOBALS['EnemyFailed']) ? $GLOBALS['EnemyFailed'] : false,
                    'need_to_check_pda' => isset($GLOBALS['NeedToCheckPDA']) ? $GLOBALS['NeedToCheckPDA'] : false,
                    'menubackground_playpos' => $this->form('maingame')->MainMenu->content->MainMenuBackground->positionMs,
                    'menusound_playpos' => $this->form('maingame')->MainMenu->content->MenuSound->positionMs,
                    'mainambientsound_playpos' => $this->form('maingame')->MainAmbient->positionMs,
                    'fightsound_playpos' => $this->form('maingame')->FightSound->positionMs,
                ];
                $encryptedData = DimasCryptoZlodey::encryptData(json_encode($saveData, JSON_PRETTY_PRINT));
                $this->saveScreenshot();
                Stream::putContents($filePath, $encryptedData);
            }
            
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
     * @event Edit_SaveName.keyDown-Enter 
     */
    function HotkeySaveBtn(UXKeyEvent $e = null)
    {    
        $this->BtnSaveGame();
    }
}
