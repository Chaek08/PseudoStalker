<?php
namespace app\forms\classes\Environment;

use script\MediaPlayerScript;

class EnvironmentRender 
{
    protected $player;
    protected $currentPath;

    public function __construct($mediaView)
    {
        $this->player = new MediaPlayerScript();
        $this->player->view = $mediaView;
        $this->player->loop = true;
    }

    public function play($path)
    {
        if ($path === $this->currentPath)
        {
            $this->player->play();
            return;
        }

        $this->currentPath = $path;

        $this->player->stop();
        $this->player->open($path);
        $this->player->play();
    }

    public function pause()
    {
        $this->player->pause();
    }

    public function resume()
    {
        $this->player->play();
    }

    public function stop()
    {
        $this->player->stop();
        $this->currentPath = null;
    }

    public function getCurrentPath()
    {
        return $this->currentPath;
    }
}