<?php
namespace app\forms\classes;

class QuestManager
{
    private $isManager = false;

    private $quests = [];
    
    private $unread = [];

    public $id;
    public $name;
    public $icon;
    public $description;
    public $steps = [];

    public const STATUS_INACTIVE  = 'inactive';
    public const STATUS_ACTIVE    = 'active';
    public const STATUS_PASSIVE   = 'passive';
    public const STATUS_FAILED    = 'failed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_PROCESS   = 'process';
    
    public const EVENT_STEP_UPDATED  = 'step.updated';
    public const EVENT_QUEST_UPDATED = 'quest.updated';
    public const EVENT_QUEST_COMPLETED = 'quest.completed';
    public const EVENT_QUEST_FAILED    = 'quest.failed';
    
    private $listeners = [];

    public $status = self::STATUS_INACTIVE;

    public function __construct($id = null, $name = null, $icon = null, $description = null, $steps = null)
    {
        if ($id === null) {
            $this->isManager = true;
            $this->quests = [];
            return;
        }

        $this->isManager = false;
        $this->id = $id;
        $this->name = $name;
        $this->icon = $icon;
        $this->description = $description;

        $normalized = [];
        if (is_array($steps))
        {
            foreach ($steps as $s)
            {
                if (is_string($s))
                {
                    $normalized[] = ['text' => $s, 'status' => self::STATUS_PROCESS];
                }
                elseif (is_array($s))
                {
                    $text = isset($s['text']) ? $s['text'] : (isset($s) ? $s : '');
                    $status = isset($s['status']) ? $s['status'] : (isset($s['completed']) ? $s['completed'] : null);
                    if (is_bool($status))
                    {
                        $status = $status ? self::STATUS_COMPLETED : self::STATUS_PROCESS;
                    }
                    $normalized[] = [
                        'text' => $text,
                        'status' => $status !== null ? $status : self::STATUS_PROCESS
                    ];
                }
            }
        }
        $this->steps = $normalized;

        if ($this->isCompleted())
        {
            $this->status = self::STATUS_COMPLETED;
        }
    }
    
    public function on(string $event, $listener): void
    {
        if (!isset($this->listeners[$event]))
        {
            $this->listeners[$event] = [];
        }

        if (\is_callable($listener))
        {
            $this->listeners[$event][] = $listener;
        }
    }
    
    public function off(string $event, $listener): void
    {
        if (!isset($this->listeners[$event])) return;
        foreach ($this->listeners[$event] as $i => $cb)
        {
            if ($cb === $listener)
            {
                unset($this->listeners[$event][$i]);
            }
        }
        if (empty($this->listeners[$event]))
        {
            unset($this->listeners[$event]);
        }
    }
    
    private function emit(string $event, array $data = []): void
    {
        if (isset($this->listeners[$event]))
        {
            foreach ($this->listeners[$event] as $cb)
            {
                $cb($event, $data, $this);
            }
        }
    }    

    public function completeStep($a, $b = null)
    {
        if ($this->isManager)
        {
            $questId   = $a;
            $stepIndex = $b;
            if ($questId === null) return false;
            $quest = $this->getQuest($questId);
            if (!$quest) return false;
            $changed = $quest->questCompleteStep($stepIndex);
            if (!$changed) return false;
            $quest->status = $quest->isCompleted() ? self::STATUS_COMPLETED : self::STATUS_ACTIVE;
            $this->flagUnreadIfNeeded($quest);
            return true;
        }
        $stepIndex = $a;
        return $this->questCompleteStep($stepIndex);
    }
    
    public function failStep($a, $b = null)
    {
        if ($this->isManager)
        {
            $questId   = $a;
            $stepIndex = $b;
            if ($questId === null) return false;
            $quest = $this->getQuest($questId);
            if (!$quest) return false;
            $changed = $quest->questFailStep($stepIndex);
            if (!$changed) return false;
            if ($quest->hasFailedStep())
            {
                $quest->status = self::STATUS_FAILED;
            }
            $this->flagUnreadIfNeeded($quest);
            return true;
        }
        $stepIndex = $a;
        return $this->questFailStep($stepIndex);
    }

    private function questCompleteStep($stepIndex)
    {
        return $this->setStepStatus($stepIndex, self::STATUS_COMPLETED);
    }

    private function questFailStep($stepIndex)
    {
        return $this->setStepStatus($stepIndex, self::STATUS_FAILED);
    }

    public function setStepStatus($stepIndex, $status)
    {
        if (!isset($this->steps[$stepIndex]))
        {
            return false;
        }
        $allowed = [self::STATUS_PROCESS, self::STATUS_COMPLETED, self::STATUS_FAILED, self::STATUS_PASSIVE];
        if (!in_array($status, $allowed, true))
        {
            return false;
        }
    
        $prevQuestStatus = $this->status;
        $prevStepStatus  = $this->steps[$stepIndex]['status'] ?? null;
    
        $this->steps[$stepIndex]['status'] = $status;
    
        if ($this->isCompleted())
        {
            $this->status = self::STATUS_COMPLETED;
        }
        elseif ($this->hasFailedStep())
        {
            $this->status = self::STATUS_FAILED;
        }
        else
        {
            $this->status = self::STATUS_ACTIVE;
        }
    
        $this->emit(self::EVENT_STEP_UPDATED, [
            'questId'    => $this->id,
            'stepIndex'  => $stepIndex,
            'prevStatus' => $prevStepStatus,
            'newStatus'  => $status,
        ]);
    
        if ($this->status !== $prevQuestStatus)
        {
            $this->emit(self::EVENT_QUEST_UPDATED, [
                'questId'    => $this->id,
                'prevStatus' => $prevQuestStatus,
                'newStatus'  => $this->status,
            ]);
    
            if ($this->status === self::STATUS_COMPLETED)
            {
                $this->emit(self::EVENT_QUEST_COMPLETED, [
                    'questId' => $this->id,
                ]);
            } elseif ($this->status === self::STATUS_FAILED)
            {
                $this->emit(self::EVENT_QUEST_FAILED, [
                    'questId' => $this->id,
                ]);
            }
        }
    
        return true;
    }

    public function isCompleted()
    {
        if (empty($this->steps)) return false;
        foreach ($this->steps as $step)
        {
            if (($step['status'] ?? self::STATUS_PROCESS) !== self::STATUS_COMPLETED)
            {
                return false;
            }
        }
        return true;
    }

    public function hasFailedStep()
    {
        foreach ($this->steps as $step)
        {
            if (($step['status'] ?? '') === self::STATUS_FAILED)
            {
                return true;
            }
        }
        return false;
    }

    public function reset()
    {
        foreach ($this->steps as &$step)
        {
            $step['status'] = self::STATUS_PROCESS;
        }
        unset($step);
        $this->status = self::STATUS_INACTIVE;
    }
        
    public function addStep($text, $status = null)
    {
        $this->steps[] = [
            'text' => $text,
            'status' => $status !== null ? $status : self::STATUS_PROCESS
        ];
    }

    public function getStep($index)
    {
        return $this->steps[$index] ?? null;
    }

    public function getStepsCount()
    {
        return count($this->steps);
    }

    public function getProgress()
    {
        $total = $this->getStepsCount();
        if ($total === 0) return 0;
        $done = 0;
        foreach ($this->steps as $s) 
        {
            if (($s['status'] ?? '') === self::STATUS_COMPLETED) $done++;
        }
        return (int) floor(100 * $done / $total);
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'icon' => $this->icon,
            'description' => $this->description,
            'status' => $this->status,
            'steps' => $this->steps,
            'progress' => $this->getProgress()
        ];
    }
    
    private function flagUnreadIfNeeded(self $quest): void
    {
        $this->unread[$quest->id] = (
            $quest->status === self::STATUS_COMPLETED ||
            $quest->status === self::STATUS_FAILED
        );
    }
    
    public function hasUnreadNotifications(): bool
    {
        foreach ($this->unread as $flag)
        {
            if ($flag === true) return true;
        }
        return false;
    }
    
    public function markAllNotificationsRead(): void
    {
        foreach ($this->unread as $id => $_)
        {
            $this->unread[$id] = false;
        }
    }    

    public function addQuest(self $quest)
    {
        if (!$this->isManager) return false;
    
        if ($quest->isCompleted())
        {
            $quest->status = self::STATUS_COMPLETED;
        }
        elseif ($quest->hasFailedStep())
        {
            $quest->status = self::STATUS_FAILED;
        }
        elseif ($quest->status === self::STATUS_INACTIVE)
        {
            $quest->status = self::STATUS_ACTIVE;
        }
    
        $this->quests[$quest->id] = $quest;
    
        $quest->listeners =& $this->listeners;
    
        $this->unread[$quest->id] = false;
        $this->flagUnreadIfNeeded($quest);
        return true;
    }


    public function hasQuest($id)
    {
        return isset($this->quests[$id]);
    }

    public function getQuest($id)
    {
        return isset($this->quests[$id]) ? $this->quests[$id] : null;
    }

    public function removeQuest($id)
    {
        if (isset($this->quests[$id]))
        {
            unset($this->quests[$id]);
            return true;
        }
        return false;
    }

    public function completeQuest($questId)
    {
        $quest = $this->getQuest($questId);
        if (!$quest) return false;
        $prev = $quest->status;
        $quest->status = self::STATUS_COMPLETED;
        $this->flagUnreadIfNeeded($quest);
    
        if ($quest->status !== $prev)
        {
            $this->emit(self::EVENT_QUEST_UPDATED, [
                'questId'    => $quest->id,
                'prevStatus' => $prev,
                'newStatus'  => $quest->status,
            ]);
            $this->emit(self::EVENT_QUEST_COMPLETED, [
                'questId' => $quest->id,
            ]);
        }
        return true;
    }
    
    public function failQuest($questId)
    {
        $quest = $this->getQuest($questId);
        if (!$quest) return false;
        $prev = $quest->status;
        $quest->status = self::STATUS_FAILED;
        $this->flagUnreadIfNeeded($quest);
    
        if ($quest->status !== $prev)
        {
            $this->emit(self::EVENT_QUEST_UPDATED, [
                'questId'    => $quest->id,
                'prevStatus' => $prev,
                'newStatus'  => $quest->status,
            ]);
            $this->emit(self::EVENT_QUEST_FAILED, [
                'questId' => $quest->id,
            ]);
        }
        return true;
    }

    public function getAllQuests()
    {
        return $this->quests;
    }
}
