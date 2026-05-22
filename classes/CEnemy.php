<?php
namespace app\forms\classes;

use app\forms\classes\Log;

class CEnemy extends CEntity
{
    protected $game;

    public function __construct($game, int $maxHP = 100)
    {
        parent::__construct($game, $maxHP);
        
        $this->game = $game;
        
        $this->setWeight(65.0);
        Log::info('Base enemy weight: ' . $this->getWeight());
    }    
}