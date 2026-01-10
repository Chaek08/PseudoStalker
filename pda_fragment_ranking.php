<?php
namespace app\forms;

use app\forms\classes\UI\UICharacterInfo;
use app\forms\classes\UI\UIRoles;
use php\gui\UXImage;
use php\gui\UXImageView;
use std, gui, framework, app;
use action\Element; 
use app\forms\classes\Localization;

class pda_fragment_ranking extends AbstractForm
{
    private $localization;
    
    public $actorCharacterInfo;
    public $enemyCharacterInfo; 
    public $valerokCharacterInfo;
    public $danilaCharacterInfo;
        
    public $actorCharacterName;
    public $enemyCharacterName;
    public $valerokCharacterName;
    public $danilaCharacterName;
        
    public $ratingHueta;
    public $deathFilter;
        
    public function __construct() 
    {
        parent::__construct();

        $this->localization = new Localization($language);
        
        $this->ratingHueta = new RatingManager();
        
        uiLater(function() {
            $this->localization->setLanguage($this->getCurrentLanguageFromUI());        
            
            $this->actorCharacterInfo = new UICharacterInfo($this, $this->localization, $this->user_icon, $this->rank, $this->relationship, $this->community, $this->bio, $this->actorCharacterName);
            $this->actorCharacterInfo->setActor();
            
            $this->enemyCharacterInfo = new UICharacterInfo($this, $this->localization, $this->user_icon, $this->rank, $this->relationship, $this->community, $this->bio, $this->enemyCharacterName);
            $this->enemyCharacterInfo->setEnemy();
            
            $this->valerokCharacterInfo =  new UICharacterInfo($this, $this->localization, $this->user_icon, $this->rank, $this->relationship, $this->community, $this->bio, $this->valerokCharacterName);
            $this->valerokCharacterInfo->setValerok();
            
            $this->danilaCharacterInfo =  new UICharacterInfo($this, $this->localization, $this->user_icon, $this->rank, $this->relationship, $this->community, $this->bio, $this->danilaCharacterInfo);
            $this->danilaCharacterInfo->setDanila();            
            
            //for($i=0;$i<27;$i++) $this->ratingHueta->setEntry(substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'),0,rand(6,12)),rand(100,1000));            
        });
        
        $GLOBALS['SelectedActor'] = false;
        $GLOBALS['SelectedEnemy'] = false;
        $GLOBALS['SelectedValera'] = false;
        $GLOBALS['SelectedDanila'] = false;        
    }
    
    function getCurrentLanguageFromUI()
    {
        return $this->form('Client')->MainMenu->content->Options->content->Language_Switcher_Combobobx->value;
    }     
    
    function UpdateData()
    {
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
        
        $this->ratingHueta->clearContainer($this->ratingKunteynir);
        
        $this->actorCharacterInfo->setActor();
        $this->enemyCharacterInfo->setEnemy();
        $this->valerokCharacterInfo->setValerok();                
        $this->danilaCharacterInfo->setDanila();  
                        
        $this->ratingHueta->setEntry($this->actorCharacterInfo->name, CharacterRank::get('actor'));
        $this->ratingHueta->setEntry($this->enemyCharacterInfo->name, CharacterRank::get('enemy'));
        $this->ratingHueta->setEntry($this->valerokCharacterInfo->name, CharacterRank::get('valerok'));
        $this->ratingHueta->setEntry($this->danilaCharacterInfo->name, CharacterRank::get('danila'));
                
        $this->ratingHueta->render($this->ratingKunteynir);
        
        $this->ratingHueta->onClick($this->actorCharacterInfo->name, function($entry) {
            $this->ActorInListBtn();
        });
            
        $this->ratingHueta->onClick($this->enemyCharacterInfo->name, function($entry) {
            $this->EnemyInListBtn();
        });
            
        $this->ratingHueta->onClick($this->valerokCharacterInfo->name, function($entry) {
            $this->ValerokInListBtn();
        });
        
        $this->ratingHueta->onClick($this->danilaCharacterInfo->name, function($entry) {
            $this->DanilaInListBtn();
        });        
            
        $this->ratingHueta->onBackgroundClick = function() {
            $this->HideUserInfo();
        };        
    }
    
    function ResetUserInfo()
    {
        $GLOBALS['SelectedActor'] = false;
        $GLOBALS['SelectedEnemy'] = false;
        $GLOBALS['SelectedValera'] = false;
        $GLOBALS['SelectedDanila'] = false;
                    
        $this->tab_detail->text = null;
        $this->community_desc->hide();
        $this->community->hide(); 
        $this->rank_desc->hide();        
        $this->rank->hide();
        $this->relationship->hide(); 
        $this->attitude->hide(); 
        $this->bio->hide();         
        $this->separator->hide(); 
        
        $this->user_icon->hide();   
        $this->removeDeathFilter($this->user_icon);
    }
    function ShowUserInfo()
    {
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
        
        $this->tab_detail->text = $this->localization->get('TabDetail');
        
        $this->community_desc->show();
        $this->community->show(); 
        $this->rank_desc->show();        
        $this->rank->show();
        $this->relationship->show(); 
        $this->attitude->show(); 
        $this->bio->show();         
        $this->separator->show(); 
        $this->user_icon->show();          
    }
    /**
     * @event frame_hide.click-Left 
     */
    function HideUserInfo(UXMouseEvent $e = null)
    {    
        $this->ResetUserInfo();
        $this->ResetBtnColor();
    }
    
    function ResetBtnColor()
    {
        $this->ratingHueta->resetColors();
    }
    
    function applyDeathFilter($image)
    {
        $image->innerShadowEffect->radius = 1000;
        $image->innerShadowEffect->color = '#b40000cc';
    }    
    
    function removeDeathFilter($image)
    {
        $image->innerShadowEffect->radius = 0;
        $image->innerShadowEffect->color = null;
    }
    
    function DeathFilterManager() // Cake-crypto
    { 
        if ($this->form('Client')->Pda->content->Pda_Statistic->visible)
        {
            $GLOBALS['ActorFailed']
                ? $this->applyDeathFilter($this->form('Client')->Pda->content->Pda_Statistic->content->icon)
                : $this->removeDeathFilter($this->form('Client')->Pda->content->Pda_Statistic->content->icon);           
        }
        
        if ($GLOBALS['SelectedActor']) //Проверяем, выбран ли сейчас нужный user
        {
            $GLOBALS['ActorFailed'] ? $this->applyDeathFilter($this->user_icon) : $this->removeDeathFilter($this->user_icon);
        }
        if ($GLOBALS['SelectedEnemy']) //Проверяем, выбран ли сейчас нужный user
        {
            $GLOBALS['EnemyFailed'] ? $this->applyDeathFilter($this->user_icon) : $this->removeDeathFilter($this->user_icon); //Проверяем, мёртв ли противник, чтобы в дальнейшем прописать ему DeathFilter
        }
        if ($GLOBALS['SelectedValera'] || $GLOBALS['SelectedDanila']) //Проверяем, выбран ли сейчас нужный user
        {
            $this->removeDeathFilter($this->user_icon);
        }
    }

    function ActorInListBtn()
    {    
        $this->ResetUserInfo();
        $this->ShowUserInfo();
        
        $GLOBALS['SelectedActor'] = true;
        $this->SetUserInfo();
    }

    function ValerokInListBtn()
    {    
        $this->ResetUserInfo();
        $this->ShowUserInfo();
        
        $GLOBALS['SelectedValera'] = true;
        $this->SetUserInfo();
    }

    function EnemyInListBtn()
    {    
        $this->ResetUserInfo();
        $this->ShowUserInfo();
        
        $GLOBALS['SelectedEnemy'] = true;
        $this->SetUserInfo();
    }
    
    function DanilaInListBtn()
    {    
        $this->ResetUserInfo();
        $this->ShowUserInfo();
        
        $GLOBALS['SelectedDanila'] = true;
        $this->SetUserInfo();
    }    
    /**
     * @event user_icon.click-2x 
     */
    function Redirect(UXMouseEvent $e = null)
    {    
        if ($GLOBALS['SelectedActor']) 
        {
            $this->form('Client')->Pda->content->UpdateBtnColor();
            $this->form('Client')->Pda->content->stat_label->textColor = '#d59b30';
            
            $this->form('Client')->Pda->content->StatisticBtn(); 
        }
        if ($this->form('Client')->Pda->content->Pda_Contacts->content->icon->visible)
        {
            if ($GLOBALS['EnemyFailed'])
            {
                return;
            }
            if ($GLOBALS['SelectedEnemy'])
            {
                $this->form('Client')->Pda->content->UpdateBtnColor();
                $this->form('Client')->Pda->content->contacts_label->textColor = '#d59b30';
            
                $this->form('Client')->Pda->content->ContactsBtn();
                $this->form('Client')->Pda->content->Pda_Contacts->content->CharacterClick(); 
            }                     
        } 
    }
    function SetUserInfo()
    {
        $this->localization->setLanguage($this->getCurrentLanguageFromUI());
        
        $this->DeathFilterManager();
        
        if ($GLOBALS['SelectedEnemy'])
        {
            $this->enemyCharacterInfo->setEnemy();
        }
        elseif ($GLOBALS['SelectedValera'])
        {
            $this->valerokCharacterInfo->setValerok();
        }
        elseif ($GLOBALS['SelectedDanila'])
        {
            $this->danilaCharacterInfo->setDanila();
        }        
        elseif ($GLOBALS['SelectedActor'])
        {
            $this->attitude->hide();
            $this->relationship->hide();
            $this->actorCharacterInfo->setActor();
        }
    }
}
