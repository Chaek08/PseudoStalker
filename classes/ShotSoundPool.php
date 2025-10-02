<?php
namespace app\forms\classes;

class ShotSoundPool
{
    private $i = 0;
    private $size = 6; // сколько одновременных «слоёв» разрешаем

    public function playShot($form, string $path, string $baseTag): void
    {
        $tag = "{$baseTag}_" . $this->i;
        $this->i = ($this->i + 1) % $this->size;
        $form->playSoundAsync($path, true, $tag);
    }
}