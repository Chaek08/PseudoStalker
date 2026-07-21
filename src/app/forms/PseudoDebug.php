<?php
namespace app\forms;

use action\Animation;
use app\forms\classes\UI\CustomTooltip;
use php\gui\UXImage;
use php\io\File;
use php\gui\UXClipboard;
use php\lang\System;
use app\forms\classes\Log;
use app\forms\Client;
use php\gui\UXApplication;
use app\forms\classes\Debug;
use php\gui\event\UXKeyEvent;
use php\gui\UXLabel;
use php\gui\layout\UXPanel;
use php\gui\framework\AbstractForm;
use php\gui\event\UXMouseEvent; 
use php\gui\event\UXWindowEvent; 


class PseudoDebug extends AbstractForm
{
    function SetRandomCrashPic()
    {
        $count = 2;
    
        $id = mt_rand(1, $count);
    
        $this->dialog_picture->image = new UXImage("res://.data/ui/PseudoDebug/dialog_pictures/{$id}.png");
    }
    
    function popEffect()
    {
        $node = $this->form('Client')->PseudoDebug;
    
        if (!$node) return;
    
        uiLater(function () use ($node) {
    
            $node->scaleX = 0.92;
            $node->scaleY = 0.92;
    
            $steps = [
                [180, 1.10],
                [120, 0.97],
                [140, 1.03],
                [120, 1.00]
            ];
    
            $play = function ($index) use (&$play, $steps, $node) {
    
                if (!isset($steps[$index])) return;
    
                [$duration, $scale] = $steps[$index];
    
                Animation::scaleTo(
                    $node,
                    $duration,
                    $scale,
                    function () use (&$play, $index) {
                        $play($index + 1);
                    }
                );
            };
    
            $play(0);
        });
    }

    public function setData(string $type, string $msg, string $file, int $line, string $trace = '')
    {
        $this->Title_Label->text = $type;
        
        $this->Desc_Label->text  = "Reason: " . $msg;
        
        $this->Path_Label->text  = "File: " . $file;
        $this->Line_Label->text  = "Line: " . (string)$line;
        
        $this->St_Label->text = "Stack trace: ";
        $this->St_textArea->text = $trace;
        
        $tooltip = new CustomTooltip($this->form('Client'));
        $tooltip->setText($this->form('Client')->getProductName());
        $tooltip->attachTo($this->dialog_picture);
    }

    /**
     * @event btn_close.click-Left 
     */
    function Close(UXMouseEvent $e = null)
    {    
        $this->form('Client')->PseudoDebug->hide();
        
        $this->form('Client')->overlay->hide();
        
        Debug::next();
    }
    
    /**
     * @event btn_exit_game.click-Left 
     */
    function ExitGame(UXMouseEvent $e = null)
    {
        $this->form('Client')->DestroyClient();
    }    

    /**
     * @event btn_openlog.click-Left 
     */
    function OpenLogFile(UXMouseEvent $e = null)
    {
        $file = Log::getLogFile();
    
        if ($file && is_file($file))
        {
            execute("notepad \"$file\"");
        }     
    }

    /**
     * @event btn_copyreport.click-Left 
     */
    function CopyReport(UXMouseEvent $e = null)
    {
        $report =
            $this->Title_Label->text . "\n\n" .
            $this->Desc_Label->text . "\n" .
            $this->Path_Label->text . "\n" .
            $this->Line_Label->text . "\n\n" .
            $this->St_Label->text . "\n" .
            $this->St_textArea->text;
    
        UXClipboard::setText($report);
        
        $this->form('Client')->toast("Crash report copied to clipboard");
    }
}
