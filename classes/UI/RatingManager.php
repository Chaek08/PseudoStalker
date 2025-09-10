<?php
namespace app\forms\classes\UI;

use php\gui\layout\UXHBox;
use php\gui\layout\UXVBox;
use php\gui\layout\UXScrollPane;
use php\gui\UXScrollPane;
use php\gui\UXVBox;
use php\gui\UXHBox;
use php\gui\UXLabel;

class RatingManager
{
    protected $entries = [];
    protected $clickHandlers = [];
    protected $activeEntry = null;
    
    public $onBackgroundClick = null;    

    public function setEntry(string $name, int $points)
    {
        foreach ($this->entries as $entry)
        {
            if ($entry->name === $name)
            {
                $entry->points = $points;
                return;
            }
        }
    
        $this->addEntry($name, $points);
    }

    public function addEntry(string $name, int $points)
    {
        $this->entries[] = (object)[
            'name' => $name,
            'points' => $points,
            'node' => null,
            'labels' => []
        ];
    }

    public function updatePoints(string $name, int $points)
    {
        foreach ($this->entries as $entry)
        {
            if ($entry->name === $name)
            {
                $entry->points = $points;
                break;
            }
        }
    }

    public function onClick(string $name, callable $handler)
    {
        $this->clickHandlers[$name] = $handler;
    }
    
    public function clickEntry(string $name)
    {
        foreach ($this->entries as $entry)
        {
            if ($entry->name === $name)
            {
                $this->resetColors();
    
                foreach ($entry->labels as $label)
                {
                    $label->textColor = "#cccccc";
                }
    
                if (isset($this->clickHandlers[$entry->name]))
                {
                    $handler = $this->clickHandlers[$entry->name];
                    $handler($entry);
                }
    
                break;
            }
        }
    }

    public function resetColors()
    {
        foreach ($this->entries as $entry)
        {
            if (isset($entry->labels))
            {
                foreach ($entry->labels as $label)
                {
                    $label->textColor = "#999999";
                }
            }
        }
    }
    
    public function render(UXScrollPane $scroll)
    {
        if (!($scroll->content instanceof UXVBox))
        {
            $box = new UXVBox();
            $box->spacing = 0;
            $box->alignment = 'TOP_LEFT';
            $box->fillWidth = true;
            $box->useMaxWidth = true;
            $scroll->content = $box;
            $scroll->fitToWidth = true;
        }
    
        $container = $scroll->content;
        $container->children->clear();
    
        usort($this->entries, function ($a, $b) {
            return $b->points - $a->points;
        });
    
        $pos = 1;
        foreach ($this->entries as $entry)
        {
            if (!isset($entry->node))
            {
                $row = new UXHBox();
                $row->alignment = 'CENTER_LEFT';
                $row->spacing = 0;
                $row->maxWidth = 392;
                $row->minWidth = 392;
                $row->prefWidth = 392;
                $row->maxHeight = 24;
                $row->minHeight = 24;
                $row->prefHeight = 24;
    
                $lblPos = new UXLabel();
                $lblPos->font->size = 15;
                $lblPos->font->bold = true;
                $lblPos->minWidth = 32;
                $lblPos->prefWidth = 32;
                $lblPos->maxWidth = 32;
                $lblPos->alignment = 'CENTER_LEFT';
                $lblPos->textColor = "#999999";
    
                $lblName = new UXLabel($entry->name);
                $lblName->font->size = 15;
                $lblName->font->bold = true;
                $lblName->minWidth = 326;
                $lblName->prefWidth = 326;
                $lblName->maxWidth = 326;
                $lblName->alignment = 'CENTER';
                $lblName->textColor = "#999999";
    
                $lblScore = new UXLabel((string)$entry->points);
                $lblScore->font->size = 15;
                $lblScore->font->bold = true;
                $lblScore->minWidth = 48;
                $lblScore->prefWidth = 48;
                $lblScore->maxWidth = 48;
                $lblScore->alignment = 'CENTER_RIGHT';
                $lblScore->textColor = "#999999";
    
                $row->add($lblPos);
                $row->add($lblName);
                $row->add($lblScore);
    
                $entry->node = $row;
                $entry->labels = [$lblPos, $lblName, $lblScore];
    
                $row->on("mouseEnter", function () use ($entry) {
                    foreach ($entry->labels as $label)
                    {
                        if ($label->textColor != "#cccccc")
                        {
                            $label->textColor = "#ffffff";
                        }
                    }
                });
                $row->on("mouseExit", function () use ($entry) {
                    foreach ($entry->labels as $label)
                    {
                        if ($label->textColor != "#cccccc")
                        {
                            $label->textColor = "#999999";
                        }
                    }
                });
    
                $row->on("mouseDown", function () use ($entry) {
                    $this->activeEntry = $entry;
                });
    
                $row->on("mouseUp", function () use ($entry) {
                    if ($this->activeEntry == $entry && $entry->node->hover)
                    {
                        $this->resetColors();
                        foreach ($entry->labels as $label)
                        {
                            $label->textColor = "#cccccc";
                        }
                        if (isset($this->clickHandlers[$entry->name]))
                        {
                            $handler = $this->clickHandlers[$entry->name];
                            $handler($entry);
                        }
                    }
                    $this->activeEntry = null;
                });
            }
    
            $entry->labels[0]->text = $pos . '.';
            $entry->labels[2]->text = (string)$entry->points;
    
            $container->add($entry->node);
            $pos++;
        }
    
        $entries = $this->entries;
        $self = $this;
    
        $scroll->on('mouseDown', function($e) use ($entries, $self) {
            $clickedBackground = true;
    
            foreach ($entries as $entry)
            {
                if (!isset($entry->node)) continue;
    
                $rowX = $entry->node->screenX;
                $rowY = $entry->node->screenY;
                $rowW = $entry->node->width;
                $rowH = $entry->node->height;
    
                if ($e->screenX >= $rowX && $e->screenX <= $rowX + $rowW &&
                    $e->screenY >= $rowY && $e->screenY <= $rowY + $rowH) 
                {
                    $clickedBackground = false;
                    break;
                }
            }
    
            if ($clickedBackground)
            {
                $self->resetColors();
                if ($self->onBackgroundClick !== null)
                {
                    call_user_func($self->onBackgroundClick);
                }
            }
        });
    }
}
