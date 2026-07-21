<?php

namespace app\forms\classes\UI;

class InventoryGridLayout
{
    private $left;
    private $top;
    private $right;
    private $bottom;

    private $cellSize;

    public function __construct(int $left, int $top, int $right, int $bottom, int $cellSize = 49)
    {
        $this->left = $left;
        $this->top = $top;
        $this->right = $right;
        $this->bottom = $bottom;
        $this->cellSize = $cellSize;
    }

    public function screenToCell(int $mouseX, int $mouseY): array
    {
        return [(int)floor(($mouseX - $this->left) / $this->cellSize), (int)floor(($mouseY - $this->top) / $this->cellSize)];
    }

    public function cellToScreen(int $cellX, int $cellY): array
    {
        return [$this->left + ($cellX * $this->cellSize), $this->top + ($cellY * $this->cellSize)];
    }

    public function getItemPosition($item, int $cellX, int $cellY, int $w, int $h): array
    {
        list($gridX, $gridY) = $this->cellToScreen($cellX, $cellY);

        return [$gridX + (($this->cellSize * $w) - $item->width) / 2, $gridY + (($this->cellSize * $h) - $item->height) / 2];
    }

    public function isInsideGrid(int $x, int $y): bool
    {
        return ($x >= $this->left && $x < $this->right && $y >= $this->top && $y < $this->bottom);
    }

    public function clampCell(int $cellX, int $cellY, int $itemW, int $itemH, int $cols, int $rows): array
    {
        return [max(0, min($cellX, $cols - $itemW)), max(0, min($cellY, $rows - $itemH))];
    }

    public function getCellSize(): int
    {
        return $this->cellSize;
    }
}