<?php
namespace app\forms\classes\UI;

use php\time\Timer;
use php\gui\UXApplication;
use action\Animation;
use php\gui\paint\UXColor;
use php\gui\shape\UXRectangle;
use php\gui\UXLabel;
use php\gui\UXRectangle;
use php\gui\UXColor;
use php\gui\UXForm;
use php\gui\layout\UXAnchorPane;
use php\gui\animation\Animation;
use php\gui\event\UXMouseEvent;
use php\desktop\Mouse;

class CustomTooltip
{
    public $form;
    public $mainGame;    
    public $overlay; //внутренняя залупа, на которой будет лежать тултип
    public $rect;
    public $label;

    public $text = '';
    public $paddingX = 20;
    public $offsetX  = 10;
    public $gap      = 12;
    public $delayMs  = 1700; //через сколько оно появится при наведении
    public $fadeMs   = 200; //через сколько исчезнет

    public $visible  = false;
    public $showTimer = null;

    public function __construct(UXForm $form)
    {
        $this->form = $form;
        $this->mainGame = $form->MainGame;
        
        $this->ensureOverlay();

        $this->rect = new UXRectangle();
        $this->rect->height = 32;
        $this->rect->arcWidth = 8;
        $this->rect->arcHeight = 8;
        $this->rect->fillColor = UXColor::of('#333333D9');
        $this->rect->strokeColor = UXColor::of('#d59b30');//UXColor::of('#d59b30');
        $this->rect->strokeWidth = 2.5;

        $this->label = new UXLabel('');
        $this->label->alignment = 'CENTER';
        $this->label->textAlignment = 'CENTER';
        $this->label->textColor = UXColor::of('#ffffff');
        $this->label->font = $this->label->font->withSize(15);

        $this->rect->opacity = 0.0;
        $this->label->opacity = 0.0;

        $this->overlay->add($this->label);
        $this->overlay->add($this->rect);
        $this->rect->toBack();
    }

    public function ensureOverlay(): void
    {
        if ($this->overlay) return;

        $this->overlay = new UXAnchorPane();
        $this->overlay->opacity = 1.0;
        $this->overlay->mouseTransparent = true;

        $this->overlay->x = 0;
        $this->overlay->y = 0;
        $this->overlay->width = $this->form->width;
        $this->overlay->height = $this->form->height;

        $this->form->observer('width')->addListener(function() {
            $this->overlay->width = $this->form->width;
        });
        $this->form->observer('height')->addListener(function() {
            $this->overlay->height = $this->form->height;
        });

        $this->form->add($this->overlay);
    }

    public function setText(string $text): void
    {
        $this->text = $text;
        $this->label->text = $text;

        uiLater(function () {
            $lb = $this->label->layoutBounds;
            $lw = $lb['width'];
            $this->rect->width = $lw + $this->paddingX;
            $this->label->height = $this->rect->height;
        });
    }

    public function attachTo($targetNode): void
    {
        $targetNode->on('mouseEnter', function (UXMouseEvent $e) {
            if ($this->showTimer) { $this->showTimer->cancel(); $this->showTimer = null; }
            $this->showTimer = Timer::after($this->delayMs, function () {
                uiLater(function () {
                    $this->repositionAtCursor();
                    $this->show();
                });
            });
        });
    
        $targetNode->on('mouseExit', function (UXMouseEvent $e) {
            if ($this->showTimer) { $this->showTimer->cancel(); $this->showTimer = null; }
            $this->hide();
        });
    
        $targetNode->on('mouseMove', function (UXMouseEvent $e) {
            if ($this->visible)
            {
                $this->repositionAtCursor();
            }
        });     
    }

    public function repositionAtCursor(): void
    {
        uiLater(function () {
            $lb = $this->label->layoutBounds;
            $lw = $lb['width'];
            $this->rect->width = $lw + $this->paddingX;
            $this->label->height = $this->rect->height;
    
            $mxScreen = Mouse::x();
            $myScreen = Mouse::y();
    
            $fx = $this->form->x;
            $fy = $this->form->y;
    
            $mx = $mxScreen - $fx;
            $my = $myScreen - $fy;
            
            //отступ от курсора, уместен в pseudostalker, так как здесь свой кастомный ******** курсор
            $offsetX = 0;
            $offsetY = -50;
    
            $x = $mx + $offsetX;
            $y = $my + $offsetY - $this->rect->height / 2;
    
            $fw = $this->form->width;
            $fh = $this->form->height;
            if ($x + $this->rect->width > $fw)
            {
                $x = max(0, $fw - $this->rect->width);
            }
            if ($y < 0) $y = 0;
            if ($y + $this->rect->height > $fh)
            {
                $y = max(0, $fh - $this->rect->height);
            }
    
            $this->rect->x = $x;
            $this->rect->y = $y;
            $this->label->x = $x + $this->offsetX;
            $this->label->y = $y;
            
            $this->rect->scale = $this->mainGame->scale;
            $this->label->scale = $this->mainGame->scale;
    
            $this->label->toFront();
            $this->rect->toBack();
        });
    }

    public function show(): void
    {
        if ($this->visible) return;
        $this->visible = true;

        Animation::fadeIn($this->rect, $this->fadeMs);
        Animation::fadeIn($this->label, $this->fadeMs);
    }

    public function hide(): void
    {
        if (!$this->visible) return;
        $this->visible = false;

        Animation::fadeOut($this->rect, $this->fadeMs);
        Animation::fadeOut($this->label, $this->fadeMs);
    }
}
