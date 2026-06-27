<?php
namespace app\forms\classes\UI;

use behaviour\custom\DropShadowEffectBehaviour;
use app\forms\classes\Localization;
use php\gui\text\UXFont;
use php\gui\UXFlatButton;
use php\gui\UXButton;
use php\gui\UXNode;

class InventoryContextMenu
{
    protected $form;
    protected $inventory;   

    protected $main;
    protected $buttons = [];
    
    public $dropShadowEffect;    

    public function __construct($clientForm, $inventoryContent)
    {
        $this->form      = $clientForm;
        $this->inventory = $inventoryContent;       

        $this->createNodes();
    }

    protected function createNodes(): void
    {
        $this->main = new UXFlatButton();
        $this->main->text = '';
        $this->main->alignment = 'CENTER';
        $this->main->borderRadius = 5;
        $this->main->color = '#ff7d00';
        $this->main->contentDisplay = 'LEFT';
        $this->main->ellipsisString = '...';
        $this->main->focusTraversable = true;
        $this->main->graphicTextGap = 4;
        $this->main->opacity = 0.75609756097561;
        $this->main->width = 168;
        $this->main->height = 40;
        $this->main->textAlignment = 'CENTER';
        $this->main->textColor = '#ffffff';
        $this->main->underline = false;
        $this->main->wrapText = false;
        $this->main->font = UXFont::of('System Regular', 12);
        
        $this->dropShadowEffect = new DropShadowEffectBehaviour();
        $this->dropShadowEffect->color   = '#222222';
        $this->dropShadowEffect->offsetX = 0;
        $this->dropShadowEffect->offsetY = 0;
        $this->dropShadowEffect->radius  = 10;
        $this->dropShadowEffect->spread  = 0;
        $this->dropShadowEffect->when    = 'ALWAYS';
        $this->dropShadowEffect->apply($this->main);    
    
        $this->form->add($this->main);
        $this->main->hide();
        
        $btn = function (string $captionKey): UXFlatButton {
            $caption = Localization::get($captionKey);
    
            $b = new UXFlatButton($caption);
            $b->alignment = 'CENTER';
            $b->borderRadius = 5;
            $b->clickColor = '#cc8033';
            $b->color = '#cc8033';
            $b->hoverColor = '#e6994d';
            $b->focusTraversable = true;
            $b->width = 152;
            $b->height = 24;
            $b->textColor = '#ffffff';
            $b->font = UXFont::of('System Regular', 13);
    
            $this->form->add($b);
            $b->hide();
            return $b;
        };
    
        $this->buttons = [
            'drop'       => $btn('Drop_Label'),
            'use'        => $btn('Use_Label'),
            'takeOff'    => $btn('TakeOff_Label'),
            'putOn'      => $btn('PutOn_Label'),
            'moveToSlot' => $btn('MoveToSlot_Label'),
        ];

    }
     
    public function refreshCaptions(): void
    {
        $map = [
            'drop'       => 'Drop_Label',
            'use'        => 'Use_Label',
            'takeOff'    => 'TakeOff_Label',
            'putOn'      => 'PutOn_Label',
            'moveToSlot' => 'MoveToSlot_Label',
        ];
    
        foreach ($map as $key => $locKey)
        {
            if (isset($this->buttons[$key]) && $this->buttons[$key])
            {
                $this->buttons[$key]->text = Localization::get($locKey);
            }
        }
    }
    
    public function showForItem($item, array $cursorPos, bool $isWearing): void
    {
        if (!$item) return;

        $this->hide();

        [$x, $y] = $this->clampPosition($cursorPos);

        $scale = $this->form->MainGame->scale ?? 1.0;

        $this->main->position = [$x, $y];
        $this->main->scale    = $scale;
        $this->main->show();
        $this->main->toFront();

        $buttons = $this->resolveButtonsForItem($item, $isWearing);

        foreach ($buttons as $btnKey)
        {
            if (!isset($this->buttons[$btnKey])) continue;
            $btn = $this->buttons[$btnKey];

            $btn->position = [$x + 8, $y + 8];
            $btn->scale    = $scale;
            $btn->show();
            $btn->toFront();
        }
    }

    public function updatePosition(array $cursorPos): void
    {
        if (!$this->main->visible) return;

        [$x, $y] = $this->clampPosition($cursorPos);

        $this->main->position = [$x, $y];

        foreach ($this->buttons as $btn)
        {
            if ($btn->visible)
            {
                $btn->position = [$x + 8, $y + 8];
            }
        }
    }

    public function hide(): void
    {
        if ($this->main)
        {
            $this->main->hide();
        }
        foreach ($this->buttons as $btn)
        {
            $btn->hide();
        }
    }

    protected function resolveButtonsForItem($item, bool $isWearing): array
    {
        $inv = $this->inventory;

        if ($item === $inv->Inv_Vodka)
        {
            return ['drop'];
        }
        if ($item === $inv->Inv_Medkit)
        {
            return ['use'];
        }
        if ($item === $inv->Inv_Wpn_Pm || $item === $inv->Inv_Wpn_AK74)
        {
            return ['moveToSlot'];
        }
        if ($item === $inv->Inv_Outfit)
        {
            return [$isWearing ? 'takeOff' : 'putOn'];
        }

        return [];
    }

    protected function clampPosition(array $pos): array
    {
        [$x, $y] = $pos;

        if (isset($this->form->width, $this->main->width))
        {
            $maxX = max(0, $this->form->width - $this->main->width);
            $x = max(0, min($x, $maxX));
        }

        if (isset($this->form->height, $this->main->height))
        {
            $maxY = max(0, $this->form->height - $this->main->height);
            $y = max(0, min($y, $maxY));
        }

        return [$x, $y];
    }
    
    public function isVisible(): bool
    {
        return $this->main && $this->main->visible;
    }    

    public function getButton(string $key): ?UXFlatButton
    {
        return $this->buttons[$key] ?? null;
    }
}
