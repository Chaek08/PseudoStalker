<?php
namespace app\forms\classes\UI;

use Throwable;
use php\framework\Logger;
use php\gui\UXImage;
use app\forms\classes\UI\UIRoles;
use app\forms\classes\Localization;
use app\forms\classes\CharacterRank;

class UICharacterInfo
{
    private $form;
    private $roles;

    public $icon;
    public $rank;
    public $relationship;
    public $community;
    public $bio;
    public $name;
    public $reputation;

    private $character; // actor | valerok | enemy | danila!!!

    public function __construct($form, $icon = null, $rank = null, $relationship = null, $community = null, $bio = null, $name = null, $reputation = null)
    {
        $this->form = $form;
        $this->roles = new UIRoles($form);

        $this->icon = $icon;
        $this->rank = $rank;
        $this->relationship = $relationship;
        $this->community = $community;
        $this->bio = $bio;
        $this->name = $name;
        $this->reputation = $reputation;

        foreach (['Actor', 'Valerok', 'Enemy'] as $who)
        {
            if (!isset($GLOBALS[$who . 'RankValue']))
            {
                $GLOBALS[$who . 'RankValue'] = 0;
            }
            if (!isset($GLOBALS[$who . 'BaseRankValue']))
            {
                $GLOBALS[$who . 'BaseRankValue'] = 0;
            }
        }
    }
         
    public function setIcon($icon) { $this->icon = $icon; return $this; }
    public function setRank($rank) { $this->rank = $rank; return $this; }
    public function setRelationship($relationship) { $this->relationship = $relationship; return $this; }
    public function setCommunity($community) { $this->community = $community; return $this; }
    public function setBio($bio) { $this->bio = $bio; return $this; }
    public function setName($name) { $this->name = $name; return $this; }
    public function setReputation($reputation) { $this->reputation = $reputation; return $this; }    

    public function resetRank()
    {
        if (!$this->character) return $this;
    
        CharacterRank::reset($this->character);
        return $this->updateRankText();
    }

    private function updateRankText()
    {
        if ($this->rank)
        {
            $this->rank->text = $this->getRankByValue(CharacterRank::get($this->character));
        }
        
        return $this;
    }

    public function addRank(int $value)
    {
        if (!$this->character) return $this;
    
        CharacterRank::add($this->character, $value);
        return $this->updateRankText();
    }

    public function setRankValue(int $value, bool $isBase = false)
    {
        if (!$this->character) return $this;
    
        CharacterRank::set($this->character, $value, $isBase);
        return $this->updateRankText();
    }

    private function getRankByValue(int $value): string
    {
        if ($value >= 900) return Localization::get('Rank_Master');
        elseif ($value >= 600) return Localization::get('Rank_Veterinarian');
        elseif ($value >= 300) return Localization::get('Rank_Experienced');
        elseif ($value >= 100) return Localization::get('Rank_Novice');
        else return;
    }

    public function setEnemy()
    {
        $this->character = 'enemy';
    
        if ($this->community) $this->roles->pidoras($this->community);
    
        CharacterRank::init('enemy', 337);
        $this->updateRankText();
        
        if ($this->relationship)
        {
            $this->relationship->text = Localization::get('Relationship_Enemy');
            $this->relationship->textColor = '#cc3333';
        }
    
        $namePath       = trim($this->form->form('Client')->Pda->content->SDK_EnemyName);
        $iconPath       = trim($this->form->form('Client')->Pda->content->SDK_EnemyIcon);
        $bioPath        = trim($this->form->form('Client')->Pda->content->SDK_EnemyBio);
        $reputationPath = trim($this->form->form('Client')->Pda->content->SDK_EnemyReputation);
    
        $nameText = $namePath !== '' ? $namePath : Localization::get('Enemy_Name');
        $bioText  = $bioPath  !== '' ? $bioPath  : Localization::get('GoblindaV_Bio');
        $repText  = $reputationPath !== '' ? $reputationPath : Localization::get('Reputation_Terrible');
        $iconImage = new UXImage($iconPath !== '' ? $iconPath : 'res://.data/ui/icon_npc/icon_petyx.png');
    
        if (is_object($this->name))
        {
            $this->name->text = $nameText;
        }
        else
        {
            $this->name = $nameText;
        }
    
        if (is_object($this->bio))
        {
            $this->bio->text = $bioText;
        }
        else
        {
            $this->bio = $bioText;
        }
    
        if (is_object($this->reputation))
        {
            $this->reputation->text = $repText;
        }
        else
        {
            $this->reputation = $repText;
        }
    
        if (is_object($this->icon))
        {
            $this->icon->image = $iconImage;
        }
        else
        {
            $this->icon = $iconImage;
        }
    }


    public function setValerok()
    {
        $this->character = 'valerok';
    
        if ($this->community) $this->roles->ladcega($this->community);
    
        CharacterRank::init('valerok', 777);
        $this->updateRankText();
    
        if ($this->relationship)
        {
            $this->relationship->text = Localization::get('Relationship_Friend');
            $this->relationship->textColor = '#669966';
        }
    
        $namePath       = trim($this->form->form('Client')->Pda->content->SDK_ValerokName);
        $iconPath       = trim($this->form->form('Client')->Pda->content->SDK_ValerokIcon);
        $bioPath        = trim($this->form->form('Client')->Pda->content->SDK_ValerokBio);
        //$reputationPath = trim($this->form->form('Client')->Pda->content->SDK_ValerokReputation);
    
        $nameText = $namePath !== '' ? $namePath : Localization::get('Valerok_Name');
        $bioText  = $bioPath  !== '' ? $bioPath  : Localization::get('Valerok_Bio');
        $repText  = $reputationPath !== '' ? $reputationPath : Localization::get('Reputation_Default');
        $iconImage = new UXImage($iconPath !== '' ? $iconPath : 'res://.data/ui/icon_npc/icon_valerok.png');
    
        if (is_object($this->name))
        {
            $this->name->text = $nameText;
        }
        else
        {
            $this->name = $nameText;
        }
    
        if (is_object($this->bio))
        {
            $this->bio->text = $bioText;
        }
        else
        {
            $this->bio = $bioText;
        }
    
        if (is_object($this->reputation))
        {
            $this->reputation->text = $repText;
        }
        else
        {
            $this->reputation = $repText;
        }
    
        if (is_object($this->icon))
        {
            $this->icon->image = $iconImage;
        }
        else
        {
            $this->icon = $iconImage;
        }
    }

    public function setActor()
    {
        $this->character = 'actor';
    
        if ($this->community) $this->roles->danilaEmoji($this->community);
    
        CharacterRank::init('actor', 152);
        $this->updateRankText();
    
        if ($this->relationship) $this->relationship->visible = false;
    
        $namePath = trim($this->form->form('Client')->Pda->content->SDK_ActorName);
        $iconPath = trim($this->form->form('Client')->Pda->content->SDK_ActorIcon);
        $bioPath  = trim($this->form->form('Client')->Pda->content->SDK_ActorBio);
        $reputationPath = trim($this->form->form('Client')->Pda->content->SDK_ActorReputation);
    
        $nameText = $namePath !== '' ? $namePath : Localization::get('GG_Name');
        $bioText  = $bioPath !== ''  ? $bioPath  : Localization::get('Actor_Bio');
        $repText  = $reputationPath !== '' ? $reputationPath : Localization::get('Reputation_Neutral');
        $iconImage = new UXImage($iconPath !== '' ? $iconPath : 'res://.data/ui/icon_npc/icon_actor.png');
    
        if (is_object($this->name))
        {
            $this->name->text = $nameText;
        }
        else
        {
            $this->name = $nameText;
        }
    
        if (is_object($this->bio))
        {
            $this->bio->text = $bioText;
        }
        else
        {
            $this->bio = $bioText;
        }
    
        if (is_object($this->reputation))
        {
            $this->reputation->text = $repText;
        }
        else
        {
            $this->reputation = $repText;
        }
    
        if (is_object($this->icon))
        {
            $this->icon->image = $iconImage;
        }
        else
        {
            $this->icon = $iconImage;
        }
    }
    
    public function setDanila()
    {
        $this->character = 'danila';
    
        if ($this->community) $this->roles->danilaEmoji($this->community);
    
        CharacterRank::init('danila', 854);
        $this->updateRankText();
        
        if ($this->relationship)
        {
            $this->relationship->text = Localization::get('Relationship_Friend');
            $this->relationship->textColor = '#669966';
        }        
    
        $namePath = trim($this->form->form('Client')->Pda->content->SDK_DanilaName);
        $iconPath = trim($this->form->form('Client')->Pda->content->SDK_DanilaIcon);
        $bioPath  = trim($this->form->form('Client')->Pda->content->SDK_DanilaBio);
        //$reputationPath = trim($this->form->form('Client')->Pda->content->SDK_DanilaReputation);
    
        $nameText = $namePath !== '' ? $namePath : Localization::get('Danila_Name');
        $bioText  = $bioPath !== ''  ? $bioPath  : Localization::get('Danila_Bio');
        $repText  = $reputationPath !== '' ? $reputationPath : Localization::get('Reputation_Good');
        $iconImage = new UXImage($iconPath !== '' ? $iconPath : 'res://.data/ui/icon_npc/icon_danila.png');
    
        if (is_object($this->name))
        {
            $this->name->text = $nameText;
        }
        else
        {
            $this->name = $nameText;
        }
    
        if (is_object($this->bio))
        {
            $this->bio->text = $bioText;
        }
        else
        {
            $this->bio = $bioText;
        }
    
        if (is_object($this->reputation))
        {
            $this->reputation->text = $repText;
        }
        else
        {
            $this->reputation = $repText;
        }
    
        if (is_object($this->icon))
        {
            $this->icon->image = $iconImage;
        }
        else
        {
            $this->icon = $iconImage;
        }
    }    

    public function reset()
    {
        foreach (['rank', 'relationship', 'community', 'bio', 'name', 'reputation'] as $prop)
        {
            if ($this->$prop) $this->$prop->text = null;
        }

        if ($this->relationship) $this->relationship->visible = true;
        if ($this->icon) $this->icon->image = new UXImage('res://.data/ui/icon_npc/no_icon.png');

        $this->setRankValue($this->getBaseRankValue());
    }
}
