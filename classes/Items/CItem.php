<?php
namespace app\forms\classes\Items;

use app\forms\classes\Localization;

class CItem
{
    protected $id;
    protected $name;
    protected $description;
    protected $weight;
    protected $price;
    protected $icon;
    protected $condition;
    protected $count;
    
    //инвентарь
    protected $uiItem;
    protected $uiCountLabel;    //убрать
    protected $gridWidth = 1;
    protected $gridHeight = 1;    
    
    public function __construct(string $id, string $name, string $description, float $weight, int $price, string $icon, int $condition = 100, int $count = 0)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->weight = $weight;
        $this->price = $price;
        $this->icon = $icon;
        $this->condition = $condition;     
        $this->count = $count;   
    }

    public function getId() { return $this->id; }
    
    public function getName(): string
    {
        return Localization::get($this->name);
    }
    
    public function getDescription(): string
    {
        return Localization::get($this->description);
    }
    
    public function getWeight() { return $this->weight; }
    public function getPrice() { return $this->price; }
    public function getIcon() { return $this->icon; }
    
    public function getCondition()
    {
        return $this->condition;
    }

    public function setCondition(int $condition)
    {
        $this->condition = max(0, min(100, $condition));
    }   
    
    public function getCount()
    {
        return $this->count;
    }
    
    public function setCount(int $count)
    {
        $this->count = max(0, $count);
    }
    
    public function addCount(int $count): void
    {
        $this->count += $count;
    }
    
    public function removeCount(int $count = 1): void
    {
        $this->count = max(0, $this->count - $count);
    }  
    
    public function isEmpty(): bool
    {
        return $this->count <= 0;
    }    
    
    public function setUIItem($uiItem)
    {
        $this->uiItem = $uiItem;
    }
    
    public function getUIItem()
    {
        return $this->uiItem;
    }    
    
    public function getGridWidth(): int
    {
        return $this->gridWidth;
    }
    
    public function getGridHeight(): int
    {
        return $this->gridHeight;
    }
    
    public function setUICountLabel($label)
    {
        $this->uiCountLabel = $label;
    }
    
    public function getUICountLabel()
    {
        return $this->uiCountLabel;
    }    
}