<?php
namespace app\forms\classes\UI;

use php\framework\Logger;
use php\gui\UXImage;
use app\forms\classes\UI\UIRoles;
use app\forms\classes\Localization;

class UICharacterInfo
{
    private $form;
    private $localization;
    private $roles;

    public $icon;
    public $rank;
    public $relationship;
    public $community;
    public $bio;
    public $name;
    public $reputation;

    private $character; // actor | valerok | enemy

    public function __construct(
        $form,
        Localization $localization,
        $icon = null,
        $rank = null,
        $relationship = null,
        $community = null,
        $bio = null,
        $name = null,
        $reputation = null
    ) {
        $this->form = $form;
        $this->localization = $localization;
        $this->roles = new UIRoles($form, $localization);

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

    public function addRank(int $value) { return $this->setRankValue($this->getRankValue() + $value); }
    public function removeRank(int $value) { return $this->setRankValue($this->getRankValue() - $value); }
    public function resetRank() { return $this->setRankValue($this->getBaseRankValue()); }

    public function setRankValue(int $value, bool $isBase = false)
    {
        if (!$this->character) return $this;

        $prefix = ucfirst($this->character);
        $GLOBALS[$prefix . 'RankValue'] = $value;

        if ($isBase)
        {
            $GLOBALS[$prefix . 'BaseRankValue'] = $value;
        }

        if ($this->rank)
        {
            $this->rank->text = $this->getRankByValue($value);
        }

        return $this;
    }

    public function getRankValue(): int
    {
        if (!$this->character) return 0;
        return $GLOBALS[ucfirst($this->character) . 'RankValue'];
    }

    public function getBaseRankValue(): int
    {
        if (!$this->character) return 0;
        return $GLOBALS[ucfirst($this->character) . 'BaseRankValue'];
    }

    private function getRankByValue(int $value): string
    {
        if ($value >= 900) return $this->localization->get('Rank_Master');
        elseif ($value >= 600) return $this->localization->get('Rank_Veterinarian');
        elseif ($value >= 300) return $this->localization->get('Rank_Experienced');
        elseif ($value >= 100) return $this->localization->get('Rank_Novice');
        else return;
    }

    public function setEnemy()
    {
        $this->character = 'enemy';

        if ($this->community) $this->roles->pidoras($this->community);
        
        if (!isset($GLOBALS['EnemyRankValue']) || $GLOBALS['EnemyRankValue'] === 0)
        {
            $this->setRankValue(666, true);
        }
        else
        {
            $this->setRankValue($GLOBALS['EnemyRankValue']);
        }

        if ($this->relationship)
        {
            $this->relationship->text = $this->localization->get('Relationship_Enemy');
            $this->relationship->textColor = '#cc3333';
        }

        $namePath = trim($this->form->form('Client')->Pda->content->SDK_EnemyName);
        $iconPath = trim($this->form->form('Client')->Pda->content->SDK_EnemyIcon);
        $bioPath  = trim($this->form->form('Client')->Pda->content->SDK_EnemyBio);
        $reputationPath = trim($this->form->form('Client')->Pda->content->SDK_EnemyReputation);

        if ($this->name) $this->name->text = $namePath ?: $this->localization->get('Enemy_Name');
        if ($this->icon) $this->icon->image = new UXImage($iconPath ?: 'res://.data/ui/icon_npc/icon_petyx.png');
        if ($this->bio) $this->bio->text = $bioPath ?: $this->localization->get('GoblindaV_Bio');
        if ($this->reputation) $this->reputation->text = $reputationPath ?: $this->localization->get('Enemy_Reputation_Default');
    }

    public function setValerok()
    {
        $this->character = 'valerok';

        if ($this->community) $this->roles->ladcega($this->community);
        
        if (!isset($GLOBALS['ValerokRankValue']) || $GLOBALS['ValerokRankValue'] === 0)
        {
            $this->setRankValue(777, true);
        }
        else
        {
            $this->setRankValue($GLOBALS['ValerokRankValue']);
        }

        if ($this->relationship)
        {
            $this->relationship->text = $this->localization->get('Relationship_Friend');
            $this->relationship->textColor = '#669966';
        }

        $namePath = trim($this->form->form('Client')->Pda->content->SDK_ValerokName);
        $iconPath = trim($this->form->form('Client')->Pda->content->SDK_ValerokIcon);
        $bioPath  = trim($this->form->form('Client')->Pda->content->SDK_ValerokBio);
        $reputationPath = trim($this->form->form('Client')->Pda->content->SDK_ValerokReputation);

        if ($this->name) $this->name->text = $namePath ?: $this->localization->get('Valerok_Name');
        if ($this->icon) $this->icon->image = new UXImage($iconPath ?: 'res://.data/ui/icon_npc/valerok.png');
        if ($this->bio) $this->bio->text = $bioPath ?: $this->localization->get('Valerok_Bio');
        if ($this->reputation) $this->reputation->text = $reputationPath ?: $this->localization->get('Valerok_Reputation_Default');
    }

    public function setActor()
    {
        $this->character = 'actor';

        if ($this->community) $this->roles->danilaEmoji($this->community);
        
        if (!isset($GLOBALS['ActorRankValue']) || $GLOBALS['ActorRankValue'] === 0)
        {
            $this->setRankValue(152, true);
        }
        else
        {
            $this->setRankValue($GLOBALS['ActorRankValue']);
        }

        if ($this->relationship) $this->relationship->visible = false;

        $namePath = trim($this->form->form('Client')->Pda->content->SDK_ActorName);
        $iconPath = trim($this->form->form('Client')->Pda->content->SDK_ActorIcon);
        $bioPath  = trim($this->form->form('Client')->Pda->content->SDK_ActorBio);
        $reputationPath = trim($this->form->form('Client')->Pda->content->SDK_ActorReputation);

        if ($this->name) $this->name->text = $namePath ?: $this->localization->get('GG_Name');
        if ($this->icon) $this->icon->image = new UXImage($iconPath ?: 'res://.data/ui/icon_npc/actor.png');
        if ($this->bio) $this->bio->text = $bioPath ?: $this->localization->get('Actor_Bio');
        if ($this->reputation) $this->reputation->text = $reputationPath ?: $this->localization->get('Actor_Reputation_Default');
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
