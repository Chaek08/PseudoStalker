<?php
namespace app\forms;

use app\forms\classes\SaveLoadManager;
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
    public $SaveLoadManager;
    
    private $saveHistory = []; 
    private $historyIndex = -1;    

    public function __construct()
    {
        parent::__construct();

        $weaponData = &$this->weaponData;
        $this->SaveLoadManager = new SaveLoadManager(array($this, 'form'), $weaponData);
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
        $path = $this->SaveLoadManager->getSaveDir() . $saveName . '.jpg';
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
        if ($this->form('Client')->MainGame->content->isMP) return;
    
        $this->saveHistory[] = trim($this->Edit_SaveName->text);
        $this->historyIndex  = count($this->saveHistory);
    
        $saveName = trim($this->Edit_SaveName->text);
        if ($saveName != '')
        {
            $filePath = $this->SaveLoadManager->getSaveDir() . $saveName . '.sav';
    
            if (file_exists($filePath)
                && $saveName != System::getProperty('user.name') . '_quicksave'
                && !isset($GLOBALS['AutoRewriteSave']))
            {
                if (!$this->form('Client')->ExitDialog->visible)
                {
                    $this->form('Client')->ExitDialog->content->showDialog(exit_dlg::TYPE_REWRITE_SAVE);
                    return;
                }
            }
    
            $this->SaveLoadManager->save($saveName);
    
            $this->saveScreenshot();
            $this->saves_list->items->add($saveName);
    
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
    
    /**
     * @event main_frame.click-Left 
     */
    function MainFrameAction(UXMouseEvent $e = null)
    {    
        if ($this->saves_list->selectedIndex >= 0)
        {
            $this->Edit_SaveName->text = '';
        }
    
        $this->saves_list->selectedIndex = -1;
    }    
}
