<?php
namespace app\forms;

use Throwable;
use php\gui\UXImage;
use std, gui, framework, app;
use php\time\Time;
use app\forms\classes\QuestManager;

class pda_fragment_tasks extends AbstractForm
{
    private $localization;
    
    public $questManager;
    
    public $currentQuestId = null;
    
    public function __construct() 
    {
        parent::__construct();

        $this->localization = new Localization($language);
        
        $this->questManager = new QuestManager();
        
        $onQuestEvent = function (string $event, array $data, QuestManager $emitter) {
            $this->form('Client')->MainGame->content->ShowMessageBox();   
            $this->updatePdaNotification();
        };
        
        $this->questManager->on(QuestManager::EVENT_STEP_UPDATED, $onQuestEvent);
        $this->questManager->on(QuestManager::EVENT_QUEST_UPDATED, $onQuestEvent);
        $this->questManager->on(QuestManager::EVENT_QUEST_COMPLETED, $onQuestEvent);
        $this->questManager->on(QuestManager::EVENT_QUEST_FAILED, $onQuestEvent);
        
        uiLater(function() {
           $this->form('Client')->MainGame->content->MessageBox->content->attachQuestManager($this->questManager);
        });
    }
    
    function getCurrentLanguageFromUI()
    {
        return $this->form('Client')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value;
    }    
    
    public $SDK_QuestName;
    public $SDK_QuestIcon;
    public $SDK_QuestDesc;
    public $SDK_QuestStep1;
    public $SDK_QuestStep2;
    public $SDK_QuestTarget;
    
    /**
     * @event show 
     */
    function InitTasks(UXWindowEvent $e = null)
    {
        $goblinQuest = new QuestManager(
            "goblin_quest",
            $this->localization->get('DefeatEnemy_Task'),
            "res://.data/ui/pda/icon_Task.png",
            $this->localization->get('TaskDetails'),
            [
                ['text' => $this->localization->get('TalkToGoblin_Task'),  'status' => QuestManager::STATUS_PROCESS],
                ['text' => $this->localization->get('DefeatGoblin_Task'),  'status' => QuestManager::STATUS_PROCESS],
            ]
        );

        $this->questManager->addQuest($goblinQuest);
        
        $this->currentQuestId = $goblinQuest->id;
        $this->showQuest($goblinQuest);

        $this->quest_detail_btn->on("click", function () {
            $qid = $this->currentQuestId;
            if ($qid)
            {
                $quest = $this->questManager->getQuest($qid);
                if ($quest) $this->toggleDetails($quest);
            }
        });
        
        $buttons = [
            'active_task',
            'passive_task',
            'failed_task'
        ];

        $this->activePressedTaskLabel = null;

        foreach ($buttons as $btnName)
        {
            $label = $this->{$btnName};

            $label->on("mouseEnter", function($e) use ($label) {
                if ($label->textColor != "#d59b30")
                {
                    $label->textColor = "white";
                }
            });

            $label->on("mouseExit", function($e) use ($label) {
                if ($label->textColor != "#d59b30")
                {
                    $label->textColor = "#777778";
                }
            });

            $label->on("mouseDown", function($e) use ($btnName) {
                $this->activePressedTaskLabel = $btnName;
            });
        }

        $this->on("mouseUp", function($e) use ($buttons) {
            if ($this->activePressedTaskLabel != null)
            {
                $btnName = $this->activePressedTaskLabel;
                $label = $this->{$btnName};

                if ($label->hover)
                {
                    $this->ResetBtnColor();
                    $label->textColor = "#d59b30";
    
                    switch ($btnName)
                    {
                        case 'active_task':
                            $this->ShowActiveTasks();
                            break;
                        case 'passive_task':
                            $this->ShowPassiveTasks();
                            break;
                        case 'failed_task':
                            $this->ShowFailedTasks();
                            break;
                    }
                }
                else
                {
                    if ($label->textColor != "#d59b30")
                    {
                        $label->textColor = "#777778";
                    }
                }

                $this->activePressedTaskLabel = null;
            }
        });                
    }
    
    function refreshQuestLocalization(): void
    {
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
    
        $quest = $this->questManager->getQuest("goblin_quest");
        if (!$quest) return;
    
        $quest->name        = $this->localization->get('DefeatEnemy_Task');
        $quest->description = $this->localization->get('TaskDetails');
    
        if (isset($quest->steps[0]))
        {
            $quest->steps[0]['text'] = $this->localization->get('TalkToGoblin_Task');
        }
        if (isset($quest->steps[1]))
        {
            $quest->steps[1]['text'] = $this->localization->get('DefeatGoblin_Task');
        }
    
        $this->showQuest($quest);
    }
    
    function showQuest(QuestManager $quest)
    {
        $this->currentQuestId = $quest->id;

        $this->task_label->text = $quest->name;
        $this->icon_task->image = new UXImage($quest->icon);

        $this->time_quest_hm->text = Time::now()->toString('HH:mm');
        $this->time_quest_date->text = Time::now()->toString('dd/MM/YYYY');

        if (isset($quest->steps[0]))
        {
            $this->step1->text = $quest->steps[0]['text'];
            $this->updateStep($this->step1, $quest->steps[0]['status']);
        }
        else
        {
            $this->step1->text = '';
            $this->step1->graphic = null;
        }

        if (isset($quest->steps[1]))
        {
            $this->step2->text = $quest->steps[1]['text'];
            $this->updateStep($this->step2, $quest->steps[1]['status']);
        }
        else
        {
            $this->step2->text = '';
            $this->step2->graphic = null;
        }
    }

    function updateStep($label, $status)
    {
        $status = $status ?? QuestManager::STATUS_PROCESS;

        switch ($status)
        {
            case QuestManager::STATUS_PROCESS:
                $label->graphic = new UXImageView(new UXImage('res://.data/ui/pda/task_step_process.png'));
                break;
            case QuestManager::STATUS_COMPLETED:
                $label->graphic = new UXImageView(new UXImage('res://.data/ui/pda/task_step_complete.png'));
                break;
            case QuestManager::STATUS_FAILED:
                $label->graphic = new UXImageView(new UXImage('res://.data/ui/pda/task_step_failed.png'));
                break;
            default:
                $label->graphic = null;
                break;
        }
    }

    function updateData(QuestManager $quest)
    {
        uiLater(function() use ($quest) {
            $this->showQuest($quest);
        });
    }

    function toggleDetails(QuestManager $quest)
    {
        if ($this->task_detail_text->visible)
        {
            $this->task_detail_text->hide();
            $this->tab_detail->text = null;
            $this->quest_detail_btn->image = new UXImage('res://.data/ui/pda/task_detail_off.png');
        }
        else
        {
            $this->task_detail_text->text = $quest->description;
            $this->task_detail_text->show();

            $this->localization->setLanguage($this->getCurrentLanguageFromUI());
            $this->tab_detail->text = $this->localization->get('TabTaskDetail');

            $this->quest_detail_btn->image = new UXImage('res://.data/ui/pda/task_detail_opened.png');
        }
    }

    function completeStep($questId, $stepIndex)
    {
        $changed = $this->questManager->completeStep($questId, $stepIndex);
        
        if ($changed && !empty($GLOBALS['AllSounds']) && $GLOBALS['AllSounds'])
        {
            $this->form('Client')->playSoundAsync('res://.data/audio/pda.mp3', 'pda_task');
        }        

        $quest = $this->questManager->getQuest($questId);
        if ($quest)
        {
            uiLater(function() use ($quest){
                $this->showQuest($quest);
            });
        }

        return $changed;
    }

    function failStep($questId, $stepIndex)
    {
        $changed = $this->questManager->failStep($questId, $stepIndex);
        
        if ($changed && !empty($GLOBALS['AllSounds']) && $GLOBALS['AllSounds'])
        {
            $this->form('Client')->playSoundAsync('res://.data/audio/pda.mp3', 'pda_task');
        }        

        $quest = $this->questManager->getQuest($questId);
        if ($quest)
        {
            uiLater(function() use ($quest) {
                $this->showQuest($quest);
            });
        }

        return $changed;
    }    
    
    function updatePdaNotification()
    {
        if ($this->questManager->hasUnreadNotifications())
        {
            if ($GLOBALS['HudVisible'])
            {
                $this->form('Client')->MainGame->content->pda_icon->show();
            }
            $this->form('Client')->Pda->content->stat_label->graphic = new UXImageView(new UXImage('res://.data/ui/pda/mainbtn_new_icon.png'));
        }
        else
        {
            if ($GLOBALS['HudVisible'])
            {
                $this->form('Client')->MainGame->content->pda_icon->hide();
            }
            $this->form('Client')->Pda->content->stat_label->graphic =  new UXImageView(new UXImage('res://.data/ui/pda/mainbtn_icon.png'));
        }
    }
    
    function clearPdaNotification()
    {
        $this->form('Client')->Pda->content->Pda_Tasks->content->questManager->markAllNotificationsRead();
    
        $this->form('Client')->Pda->content->stat_label->graphic = new UXImageView(new UXImage('res://.data/ui/pda/mainbtn_icon.png'));
    
        if ($GLOBALS['HudVisible'])
        {
            $this->form('Client')->MainGame->content->pda_icon->hide();
        }
    }
     
    function DetailTask(UXMouseEvent $e = null)
    {
        if ($this->task_detail_text->visible)
        {
            $this->task_detail_text->hide();
            $this->tab_detail->text = null;
            
            $this->quest_detail_btn->image = new UXImage('res://.data/ui/pda/task_detail_off.png');
            $this->quest_detail_btn->hoverImage = new UXImage('res://.data/ui/pda/task_detail_on.png');
            $this->quest_detail_btn->clickImage = new UXImage('res://.data/ui/pda/task_detail_on.png');
        }
        else
        {
            $this->task_detail_text->show();
            
            $this->localization->setLanguage($this->getCurrentLanguageFromUI());
            
            $this->tab_detail->text = $this->localization->get('TabTaskDetail');

            $this->quest_detail_btn->image = new UXImage('res://.data/ui/pda/task_detail_opened.png');
            $this->quest_detail_btn->hoverImage = new UXImage('res://.data/ui/pda/task_detail_opened.png');
            $this->quest_detail_btn->clickImage = new UXImage('res://.data/ui/pda/task_detail_opened.png');
        }
    }
    /**
     * @event frame_hide.click-Left 
     */
    function ClearDetailTask(UXMouseEvent $e = null) //заебись, сделал один фрейм на весь экран и тем самым сократил количество функций :like
    {               
        if (!$this->task_detail_text->visible) return;

        $this->task_detail_text->hide();
        $this->tab_detail->text = null;
        
        $this->quest_detail_btn->image = new UXImage('res://.data/ui/pda/task_detail_off.png');
        $this->quest_detail_btn->hoverImage = new UXImage('res://.data/ui/pda/task_detail_on.png'); 
        $this->quest_detail_btn->clickImage = new UXImage('res://.data/ui/pda/task_detail_on.png');
    }
    /**
     * @event active_task.click-Left 
     */
    function ShowActiveTasks(UXMouseEvent $e = null)
    {
        $quest = $this->questManager->getQuest($this->currentQuestId);
        if (!$quest) return;
    
        if ($quest->status === QuestManager::STATUS_ACTIVE)
        {
            $this->AddTask();
        }
        else
        {
            $this->DeleteTask();
        }
    }
    
    /**
     * @event passive_task.click-Left 
     */
    function ShowPassiveTasks(UXMouseEvent $e = null)
    {
        $quest = $this->questManager->getQuest($this->currentQuestId);
        if (!$quest) return;
    
        if ($quest->status === QuestManager::STATUS_COMPLETED)
        {
            $this->AddTask();
        }
        else
        {
            $this->DeleteTask();
        }
    }
    
    /**
     * @event failed_task.click-Left 
     */
    function ShowFailedTasks(UXMouseEvent $e = null)
    {
        $quest = $this->questManager->getQuest($this->currentQuestId);
        if (!$quest) return;
    
        if ($quest->status === QuestManager::STATUS_FAILED)
        {
            $this->AddTask();
            //$this->step2->graphic = new UXImageView(new UXImage('res://.data/ui/pda/task_step_failed.png'));
        }
        else
        {
            $this->DeleteTask();
        }
    }

    function ResetBtnColor()
    {
        foreach (['active_task', 'passive_task', 'failed_task'] as $btn)
        {
            $this->{$btn}->textColor = "#777778";
        }      
    }
    
    function AddTask()
    {
        $this->task_label->show();
        $this->icon_task->show();
        $this->quest_detail_btn->show();
        $this->time_quest_hm->show();
        $this->time_quest_date->show();        
        $this->step1->show();
        $this->step2->show();
    }
    
    function UpdateQuestTime()
    {
        $this->time_quest_hm->text = Time::now()->toString('HH:mm');
        $this->time_quest_date->text = Time::now()->toString('dd/MM/YYYY');
    }
    
    function DeleteTask()
    {
        $this->task_label->hide();
        $this->icon_task->hide();
        $this->quest_detail_btn->hide(); 
        $this->task_detail_text->hide();  
        $this->time_quest_hm->hide();
        $this->time_quest_date->hide();
        $this->step1->hide();
        $this->step2->hide();
        $this->tab_detail->text = null;
        
        $this->quest_detail_btn->image = new UXImage('res://.data/ui/pda/task_detail_off.png');
        $this->quest_detail_btn->hoverImage = new UXImage('res://.data/ui/pda/task_detail_on.png'); 
        $this->quest_detail_btn->clickImage = new UXImage('res://.data/ui/pda/task_detail_on.png');        
                     
    }
}
