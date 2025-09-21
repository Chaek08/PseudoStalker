<?php
namespace app\forms;

use app\forms\classes\Localization;
use app\forms\classes\QuestManager;
use php\gui\framework\AbstractForm;
use php\gui\event\UXWindowEvent;

class MessageBox extends AbstractForm
{
    private $questManager;
    private $listener;
    private $localization;

    public function __construct()
    {
        parent::__construct();
        $this->localization = new Localization($language);
    }

    function getCurrentLanguageFromUI()
    {
        return $this->form('Client')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value;
    }

    public function attachQuestManager(QuestManager $qm): void
    {
        $this->questManager = $qm;

        $this->listener = function (string $event, array $data, QuestManager $emitter) {
            $this->onQuestEvent($event, $data);
        };

        $qm->on(QuestManager::EVENT_STEP_UPDATED,     $this->listener);
        $qm->on(QuestManager::EVENT_QUEST_UPDATED,    $this->listener);
        $qm->on(QuestManager::EVENT_QUEST_COMPLETED,  $this->listener);
        $qm->on(QuestManager::EVENT_QUEST_FAILED,     $this->listener);
    }

    public function detachQuestManager(): void
    {
        if (!$this->questManager || !$this->listener) return;
        $qm = $this->questManager;
        $cb = $this->listener;

        $qm->off(QuestManager::EVENT_STEP_UPDATED,     $cb);
        $qm->off(QuestManager::EVENT_QUEST_UPDATED,    $cb);
        $qm->off(QuestManager::EVENT_QUEST_COMPLETED,  $cb);
        $qm->off(QuestManager::EVENT_QUEST_FAILED,     $cb);

        $this->questManager = null;
        $this->listener = null;
    }

    private function onQuestEvent(string $event, array $data): void
    {
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
    
        $quest = null;
        $questId = $data['questId'] ?? null;
        if ($questId && $this->questManager)
        {
            $quest = $this->questManager->getQuest($questId);
            if ($quest)
            {
                $this->Task_Name->text = $quest->name;
            }
        }
    
        $text = null;
        if ($event === QuestManager::EVENT_STEP_UPDATED && $quest)
        {
            $this->Task_Status->text = $this->localization->get('Task_Status_Update');        
        
            $idx  = $data['stepIndex'] ?? null;
            $new  = $data['newStatus'] ?? null;
        
            if ($idx !== null && $new === QuestManager::STATUS_COMPLETED)
            {
                $next = $idx + 1;
                $stepNext = $quest->getStep($next);
                if ($stepNext && !empty($stepNext['text']))
                {
                    $text = $stepNext['text'];
                    $this->form('Client')->MainGame->content->ShowTaskStep($text, $quest->id);
                    return;
                }
            }
        
            $step = ($idx !== null) ? $quest->getStep($idx) : null;
            if ($step && !empty($step['text']))
            {
                $this->form('Client')->MainGame->content->ShowTaskStep($step['text'], $quest->id);
                return;
            }
        }

        if ($event === QuestManager::EVENT_QUEST_FAILED)
        {
            $this->Task_Status->text = $this->localization->get('Task_Status_Failed');
        }
    
        //$this->form('Client')->MainGame->content->ShowTaskStep($text, $quest ? $quest->id : null);
        $this->form('Client')->MainGame->content->ShowMessageBox();
    }

}
