<?php
namespace app\forms\classes\UI;

use php\gui\UXImage;
use php\gui\UXImageView;
use app\forms\classes\Localization;

class UIRoles
{
    private $form;

    public function __construct($form)
    {
        $this->form = $form;
    }

    public function reset($target)
    {
        $target->text = '-';
        $target->textColor = 'white';
        $target->graphic = new UXImageView(
            new UXImage('res://.data/ui/dialog/no_role.png')
        );

        return [
            'text'  => '-',
            'icon'  => 'res://.data/ui/dialog/no_role.png',
            'color' => 'white',
        ];
    }

    public function ladcega($target)
    {
        $name  = trim($this->form->form('Client')->Pda->content->SDK_LaRoleName);
        $icon  = trim($this->form->form('Client')->Pda->content->SDK_LaRoleIcon);
        $color = trim($this->form->form('Client')->Pda->content->SDK_LaRoleColor);

        return $this->apply(
            $target,
            $name ?: Localization::get('LA_Community'),
            $icon ?: 'res://.data/ui/dialog/ladcega_role.png',
            $color ?: '#e64d4d'
        );
    }

    public function danilaEmoji($target)
    {
        $name  = trim($this->form->form('Client')->Pda->content->SDK_DeRoleName);
        $icon  = trim($this->form->form('Client')->Pda->content->SDK_DeRoleIcon);
        $color = trim($this->form->form('Client')->Pda->content->SDK_DeRoleColor);

        return $this->apply(
            $target,
            $name ?: Localization::get('DE_Community'),
            $icon ?: 'res://.data/ui/dialog/danila_emoji_role.png',
            $color ?: '#cc8033'
        );
    }

    public function pidoras($target)
    {
        $name  = trim($this->form->form('Client')->Pda->content->SDK_PidoRoleName);
        $icon  = trim($this->form->form('Client')->Pda->content->SDK_PidoRoleIcon);
        $color = trim($this->form->form('Client')->Pda->content->SDK_PidoRoleColor);

        return $this->apply(
            $target,
            $name ?: Localization::get('Community_Pido'),
            $icon ?: 'res://.data/ui/dialog/pidoras_role.png',
            $color ?: '#16a4cd'
        );
    }

    private function apply($target, $text, $iconPath, $color)
    {
        $target->text = $text;
        $target->graphic = new UXImageView(new UXImage($iconPath));
        $target->textColor = $color;

        ($g = $target->graphic)->width = ($g->height = 16);

        return [
            'text'  => $text,
            'icon'  => $iconPath,
            'color' => $color,
        ];
    }
}
