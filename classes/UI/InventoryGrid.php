<?php
namespace app\forms\classes\UI;

class InventoryGrid
{
    private $grid = [];

    private $cols;
    private $rows;

    public function __construct(int $cols = 11, int $rows = 13)
    {
        $this->cols = $cols;
        $this->rows = $rows;

        for ($x = 0; $x < $cols; $x++)
        {
            for ($y = 0; $y < $rows; $y++)
            {
                $this->grid[$x][$y] = null;
            }
        }
    }

    public function canPlace($x, $y, $w, $h): bool
    {
        if ($x < 0 || $y < 0)
        {
            return false;            
        } 

        if ($x + $w > $this->cols)
        {
            return false;            
        } 

        if ($y + $h > $this->rows)
        {
            return false;            
        }        

        for ($ix = 0; $ix < $w; $ix++)
        {
            for ($iy = 0; $iy < $h; $iy++)
            {
                if ($this->grid[$x + $ix][$y + $iy] !== null)
                {
                    return false;            
                } 
            }
        }

        return true;
    }

    public function place($item, $x, $y, $w, $h): void
    {
        for ($ix = 0; $ix < $w; $ix++)
        {
            for ($iy = 0; $iy < $h; $iy++)
            {
                $this->grid[$x + $ix][$y + $iy] = $item;
            }
        }
    }

    public function remove($item): void
    {
        for ($x = 0; $x < $this->cols; $x++)
        {
            for ($y = 0; $y < $this->rows; $y++)
            {
                if ($this->grid[$x][$y] === $item)
                {
                    $this->grid[$x][$y] = null;
                }
            }
        }
    }
    
    public function findItem($item): ?array
    {
        for ($x = 0; $x < $this->cols; $x++)
        {
            for ($y = 0; $y < $this->rows; $y++)
            {
                if ($this->grid[$x][$y] === $item)
                {
                    return [$x, $y];
                }
            }
        }
    
        return null;
    }    

    public function findFreeSlot($w, $h)
    {
        for ($y = 0; $y < $this->rows; $y++)
        {
            for ($x = 0; $x < $this->cols; $x++)
            {
                if ($this->canPlace($x, $y, $w, $h))
                {
                    return [$x, $y];
                }
            }
        }

        return null;
    }

    public function getGrid(): array
    {
        return $this->grid;
    }
}