<?php
namespace app\forms\classes\UI;

use php\gui\UXImage;
use php\gui\UXImageView;
use behaviour\custom\DropShadowEffectBehaviour;
use php\gui\text\UXFont;
use php\gui\paint\UXColor;
use php\gui\UXApplication;
use action\Animation;
use php\gui\animation\UXAnimationTimer;
use php\gui\UXLabel;
use app\forms\classes\UI\UIRoles;

class CNameIndicator
{
    private $label;
    private $form;
    private $ownerModel;
    private $followTimer;
    private $dropShadowEffect;

    private const LABEL_WIDTH = 500;
    private const LABEL_HEIGHT = 22;

    private $nickname = 'Entity';


    public function __construct($ownerModel, string $nickname = '', $form = null)
    {
        $this->ownerModel = $ownerModel;
        $this->nickname = $nickname;
        $this->form = $form;

        $this->label = new UXLabel($nickname);

        $this->label->width = self::LABEL_WIDTH;
        $this->label->height = self::LABEL_HEIGHT;

        $this->label->alignment = 'CENTER';

        $this->label->mouseTransparent = true;
        $this->label->opacity = 0.9;

        $this->label->visible = true;
        $this->label->autosize = true;
         
        $this->label->textAlignment = 'CENTER';
        $this->label->textColor = UXColor::of('#ffffff');//UXColor::of('#ffa500');
        $this->label->font = UXFont::of('System', 20);
        
        $this->label->contentDisplay = 'RIGHT';
        
        $this->dropShadowEffect = new DropShadowEffectBehaviour();
        $this->dropShadowEffect->color   = '#1a1a1a';//'#1a1a1ab2';
        $this->dropShadowEffect->offsetX = 0;
        $this->dropShadowEffect->offsetY = 0;
        $this->dropShadowEffect->radius  = 10;//12.5;
        $this->dropShadowEffect->spread  = 0;//0.81;
        $this->dropShadowEffect->when    = 'ALWAYS';
        
        $this->dropShadowEffect->apply($this->label);         

        $ownerModel->parent->add($this->label);

        $this->updatePosition();

        $this->startFollow();
    }
    
    public function setRole(string $role): void
    {
        $roles = new UIRoles($this->form);
    
        switch ($role)
        {
            case 'ladcega':
                $data = $roles->ladcega($this->label);
                break;
    
            case 'danilaEmoji':
                $data = $roles->danilaEmoji($this->label);
                break;
    
            case 'pidoras':
                $data = $roles->pidoras($this->label);
                break;
    
            default:
                $roles->reset($this->label);
                return;
        }
    
        $this->label->text = $this->nickname;
        $this->label->textColor = UXColor::of($data['color']);
    
        $icon = new UXImageView(new UXImage($data['icon']));
        $icon->fitWidth = 16;
        $icon->fitHeight = 16;
    
        $this->label->graphic = $icon;
    }   


    private function startFollow(): void
    {
        $this->stopFollow();

        $this->followTimer = new UXAnimationTimer(function () {

            if ($this->label)
            {
                $this->updatePosition();
            }

        });

        $this->followTimer->start();
    }


    private function stopFollow(): void
    {
        if ($this->followTimer)
        {
            $this->followTimer->stop();
            $this->followTimer = null;
        }
    }


    private function updatePosition(): void
    {
        if (!$this->label || !$this->ownerModel)
            return;


        $m = $this->ownerModel;


        // центрируем над головой
        $this->label->x = $m->x + ($m->width / 2) - (self::LABEL_WIDTH / 2);


        // ниже sound indicator
        $this->label->y = $m->y - 40;
    }


    public function setText(string $text): void
    {
        $this->nickname = $text;

        if ($this->label)
        {
            $this->label->text = $text;
        }
    }


    public function getText(): string
    {
        return $this->nickname;
    }


    public function show(): void
    {
        if (!$this->label)
            return;

        $this->fxLater(function(){

            if (!$this->label)
                return;

            $this->label->opacity = 0;
            $this->label->visible = true;

            Animation::fadeTo(
                $this->label,
                150,
                0.9
            );

        });
    }


    public function hide(): void
    {
        if (!$this->label)
            return;


        Animation::fadeTo(
            $this->label,
            200,
            0,
            function(){

                if ($this->label)
                {
                    $this->label->visible = false;
                }

            }
        );
    }


    public function destroy(): void
    {
        $this->stopFollow();


        $label = $this->label;

        $this->label = null;
        $this->ownerModel = null;


        if ($label)
        {
            $label->visible = false;
            $label->opacity = 0;


            if ($label->parent)
            {
                $label->parent->remove($label);
            }
        }
    }


    private function fxLater(callable $fn): void
    {
        UXApplication::runLater($fn);
    }
}