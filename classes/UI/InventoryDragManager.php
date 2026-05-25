<?php
namespace app\forms\classes\UI;

use php\gui\UXImageView;
use php\gui\animation\UXAnimationTimer;

class InventoryDragManager
{
    protected $owner;

    protected $draggedItem = null;
    protected $draggedItemOriginalPos = null;

    protected $dragGhost = null;

    protected $dragGhostFollowTimer = null;

    protected $dragActivated = false;

    public function __construct($owner)
    {
        $this->owner = $owner;
    }

    public function beginDrag($item, $extraFrontNode = null)
    {
        if ($this->draggedItem) return;
        
        if ($this->owner->contextMenu && $this->owner->contextMenu->isVisible())
        {
            return;
        }
    
        $this->draggedItem = $item;
        $this->draggedItemOriginalPos = $item->position;
        $this->dragActivated = true;
    
        $item->toFront();
    
        if ($extraFrontNode)
        {
            $extraFrontNode->toFront();
        }
    
        $this->createDragGhost($item);
        $this->startDragGhostFollowTimer();
    }

    public function endDrag()
    {
        $this->endDragUI();

        $this->draggedItem = null;
        $this->draggedItemOriginalPos = null;
    }

    public function endDragUI()
    {
        $this->dragActivated = false;
        
        $this->stopDragGhostFollowTimer();
        
        $this->destroyDragGhost();
    }

    protected function createDragGhost($originalItem)
    {
        $this->destroyDragGhost();

        $originalItem->opacity = 0;

        $label = $this->owner->getItemCountLabel($originalItem);

        if ($label)
        {
            $label->visible = false;
        }

        $this->dragGhost = new UXImageView();

        $this->dragGhost->image = $originalItem->image;

        $this->dragGhost->scale = $this->owner->form('Client')->MainGame->scale;

        $this->dragGhost->opacity = 0.6;
        $this->dragGhost->enabled = false;
        $this->dragGhost->visible = true;

        $cursor = $this->owner->form('Client')->CustomCursor;

        if (!$cursor)
        {
            return;
        }

        $this->owner->form('Client')->add($this->dragGhost);

        $this->dragGhost->position = [
            $cursor->x - ($this->dragGhost->width / 2),
            $cursor->y - ($this->dragGhost->height / 2)
        ];

        $this->dragGhost->toFront();
    }

    protected function destroyDragGhost()
    {
        if ($this->draggedItem)
        {
            $this->draggedItem->opacity = 1;

            $label = $this->owner->getItemCountLabel($this->draggedItem);

            if ($label)
            {
                $count = $this->owner->getItemCount($this->draggedItem);

                $label->visible = ($count >= 2);
            }
        }

        if ($this->dragGhost)
        {
            $this->owner->form('Client')->remove($this->dragGhost);

            $this->dragGhost = null;
        }
    }

    protected function startDragGhostFollowTimer()
    {
        $this->stopDragGhostFollowTimer();

        $this->dragGhostFollowTimer =
            new UXAnimationTimer(function ()
            {
                $this->updateDragGhost();
            });

        $this->dragGhostFollowTimer->start();
    }

    protected function stopDragGhostFollowTimer()
    {
        if ($this->dragGhostFollowTimer)
        {
            $this->dragGhostFollowTimer->stop();
            $this->dragGhostFollowTimer = null;
        }
    }

    protected function updateDragGhost()
    {
        if (!$this->dragGhost || !$this->draggedItem)
        {
            return;
        }
    
        $cursor = $this->owner->form('Client')->CustomCursor;
    
        if (!$cursor)
        {
            return;
        }
    
        $this->dragGhost->position = [
            $cursor->x - ($this->dragGhost->width / 2),
            $cursor->y - ($this->dragGhost->height / 2)
        ];
    }

    public function isDragging(): bool
    {
        return $this->draggedItem != null;
    }

    public function isActivated(): bool
    {
        return $this->dragActivated;
    }

    public function getDraggedItem()
    {
        return $this->draggedItem;
    }

    public function getOriginalPosition()
    {
        return $this->draggedItemOriginalPos;
    }
}