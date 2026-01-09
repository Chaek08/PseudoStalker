<?php
namespace app\forms;

use app\forms\classes\Debug;
use php\gui\event\UXKeyEvent;
use php\gui\UXLabel;
use php\gui\layout\UXPanel;
use php\gui\framework\AbstractForm;


class PseudoDebug extends AbstractForm
{
    private $result = 'STOP';

    public function setData(string $type, string $msg, string $file, int $line)
    {
        $this->Title_Label->text = $type;
        $this->Desc_Label->text  = $msg;
        $this->Path_Label->text  = $file;
        $this->Line_Label->text  = (string)$line;
    }

    public function getResult(): string
    {
        return $this->result;
    }

    /**
     * @event show
     */
    function InitDebugWnd()
    {
        $stop = $this->createButton("Stop", function () {
            $this->result = 'STOP';
            $this->close();
        });
        $stop->size = [152, 29];
        $stop->x = 10;
        $stop->y = 170;
        $this->add($stop);

        $debug = $this->createButton("Debug", function () {
            $this->result = 'DEBUG';
            $this->close();
        });
        $debug->size = [152, 29];
        $debug->x = 326;
        $debug->y = 170;
        $this->add($debug);

        $this->requestFocus();        
    }

    private function createButton(string $text, callable $onClick): UXPanel
    {
        $btn = new UXPanel();
        $btn->backgroundColor = '#f0f0f0';
        $btn->borderColor = '#646464';
        $btn->borderWidth = 1;

        $label = new UXLabel($text);
        $label->alignment = 'CENTER';
        $label->textAlignment = 'CENTER';
        $label->textColor = 'black';
        $label->mouseTransparent = true;

        $label->anchors = [
            'left'   => true,
            'right'  => true,
            'top'    => true,
            'bottom' => true
        ];

        $btn->add($label);

        $btn->on('mouseEnter', function () use ($btn) {
            $btn->borderWidth = 2;
        });

        $btn->on('mouseExit', function () use ($btn, $label) {
            $btn->borderWidth = 1;
            $label->translateX = 0;
            $label->translateY = 0;
        });

        $btn->on('mouseDown', function () use ($label) {
            $label->translateX = 1;
            $label->translateY = 1;
        });

        $btn->on('mouseUp', function () use ($label, $onClick) {
            $label->translateX = 0;
            $label->translateY = 0;
            call_user_func($onClick);
        });   

        return $btn;
    }
    
    /**
     * @event keyDown-Space 
     */
    function Space(UXKeyEvent $e = null)
    {    
        $this->result = 'STOP';
        $this->close();        
    }
    
}