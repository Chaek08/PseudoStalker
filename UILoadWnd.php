<?php
namespace app\forms;

use app\forms\exit_dlg;
use app\forms\classes\SaveLoadManager;
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
    private $localization;
    
    public $SaveLoadManager;

    public function __construct() 
    {
        parent::__construct();

        $this->localization = new Localization($language);
        
        $weaponData = &$this->weaponData;
        $this->SaveLoadManager = new SaveLoadManager(array($this, 'form'), $weaponData);            
    }
    
    function getCurrentLanguageFromUI()
    {
        return $this->form('Client')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value;
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
        $directory = new File($this->SaveLoadManager->getSaveDir());
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
    /**
     * @event Return_Btn.click-Left 
     */
    function ReturnBtn(UXMouseEvent $e = null)
    {
        $this->form('Client')->MainMenu->content->dynamic_background->toBack();
        $this->form('Client')->MainMenu->content->UILoadWnd->hide();
    }    
    /**
     * @event saves_list.action 
     */
    function ShowSavePreview(UXEvent $e = null)
    {
        $selectedSave = $this->saves_list->selectedItem;
        
        if (empty($selectedSave))
        {
            $this->HideSavePreview();
            return;
        }
        
        $imagePath = $this->SaveLoadManager->getSaveDir() . $selectedSave . '.jpg';
    
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
    
        $saveData = $this->SaveLoadManager->load($selectedSave);
    
        if ($saveData === null)
        {
            $this->savedata_name->text   = $selectedSave;
            $this->savedata_health->text = '---%';
            $this->savedata_time->text   = '--:-- --/--/----';
            return;
        }
    
        $this->savedata_name->show();
        $this->savedata_time->show();
        $this->savedata_health->show();
        
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());        
        
        $this->savedata_name->text = $selectedSave;
        $hp = $saveData['health']['actor']['hp'] ?? null;
        $this->savedata_health->text = $this->localization->get('SaveData_Health_Label') . ' : ' . ($hp !== null ? $hp . '%' : '---%');
        $this->savedata_time->text = $this->localization->get('SaveData_Time_Label') 
            . ' : ' . ($saveData['quest_time']['hm'] ?? '--:--') 
            . '  ' . ($saveData['quest_time']['date'] ?? '--/--/----');
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
        $saveName = $this->saves_list->selectedItem;
        $saveData = $this->SaveLoadManager->load($saveName);
        if ($saveData === null) return;
        
        $result = $this->SaveLoadManager->validateSave($saveData);
        
        if (!$result['ok'])// похуй//нам не нужна exitdialog хуета, ибо здесь нет выбора да или нет
        {
            $this->localization->setLanguage($this->getCurrentLanguageFromUI());
            if ($result['error'] === 'corrupt')
            {
                if (!$this->form('Client')->ExitDialog->visible)
                {
                    $this->form('Client')->ExitDialog->content->showDialog(exit_dlg::TYPE_CORRUPT_SAVE);
                    //$this->form('Client')->toast($this->localization->get('SaveCorruptToast'));
                }
            }
            elseif ($result['error'] === 'version')
            {
                if (!$this->form('Client')->ExitDialog->visible)
                {
                    $this->form('Client')->ExitDialog->content->showDialog(exit_dlg::TYPE_CLIENT_VERSION_ERR);
                    //$this->form('Client')->toast($this->localization->get('InvalidGameClientToast'));
                }
            }
            return;
        }
        
        if (!empty($GLOBALS['ContinueGameState']))
        {
            if (!$this->form('Client')->ExitDialog->visible && $this->form('Client')->MainMenu->visible)
            {        
                $this->form('Client')->ExitDialog->content->showDialog(exit_dlg::TYPE_LOAD_WITH_LOSS);
                return;
            }
        }
        
        $this->SaveLoadManager->applySaveData($saveData, $saveName);
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
                $this->form('Client')->ExitDialog->content->showDialog(exit_dlg::TYPE_REMOVE_SAVE);
                return;
            } 
        
            $filePath = $this->SaveLoadManager->getSaveDir() . $selectedSave . '.sav';
            $imagePath = $this->SaveLoadManager->getSaveDir() . $selectedSave . '.jpg';

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
    
    function getLatestSaveName(): ?string
    {
        $items = $this->saves_list->items->toArray();
    
        $latestName = null;
        $latestTime = 0;
    
        foreach ($items as $saveName)
        {
            $filePath = $this->SaveLoadManager->getSaveDir() . $saveName . '.sav';
            if (file_exists($filePath))
            {
                $fileTime = filemtime($filePath);
                if ($fileTime > $latestTime)
                {
                    $latestTime = $fileTime;
                    $latestName = $saveName;
                }
            }
        }
    
        return $latestName;
    }      
}
