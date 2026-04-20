<?php
namespace app\forms\classes;

use php\gui\animation\UXAnimationTimer;
use php\gui\UXApplication;
use php\time\Timer;
use app\forms\classes\Log;

class CWindowManager
{
    private $form;
    private $ltx;
    
    private $tracker;    

    private $prevClientW = null;
    private $prevClientH = null;

    public function __construct($form, $ltx)
    {
        $this->form = $form;
        $this->ltx = $ltx;
    }
    
    public function startTracking()
    {
        $this->tracker = new UXAnimationTimer(function () {
            $this->trackResolution();
        });
    
        $this->tracker->start();
    }    

    public function applyResolutionFromLTX()
    {
        $vid = $this->ltx->r_string('vid_mode');

        if (!preg_match('/^[1-9]\d*x[1-9]\d*$/', $vid))
        {
            Log::error("Invalid vid_mode '{$vid}', fallback to 1600x900");

            $this->ltx->w_string('vid_mode', '1600x900');
            $this->ltx->save();
            return;
        }

        [$targetW, $targetH] = explode('x', $vid);

        $targetW = (int)$targetW;
        $targetH = (int)$targetH;

        $clientW = $this->form->Client_Proxy->width;
        $clientH = $this->form->Client_Proxy->height;

        $diffW = $this->form->width - $clientW;
        $diffH = $this->form->height - $clientH;

        $this->form->width = $targetW + $diffW;
        $this->form->height = $targetH + $diffH;

        $this->trackResolution();
    }

    public function trackResolution()
    {
        $w = $this->form->Client_Proxy->width;
        $h = $this->form->Client_Proxy->height;
    
        if ($this->prevClientW === $w && $this->prevClientH === $h)
        {
            return;
        }
    
        $this->prevClientW = $w;
        $this->prevClientH = $h;
    
        $res = "{$w}x{$h}";
    
        $this->ltx->w_string('vid_mode', $res);
        $this->ltx->save();
    
        $this->updateUI($w, $h);
    }
    
    private function updateUI($w, $h)
    {
        UXApplication::runLater(function() use ($w, $h)
        {
            $this->updateDebug($w, $h);
            $this->scaleScenes($w, $h);
        });
    }   
    
    private function updateDebug($w, $h)
    {
        if (!defined('ResTracker') || !ResTracker)
        {
            return;
        }
    
        static $prevRes = '';
    
        $res = "$w x $h";
    
        if ($res === $prevRes)
        {
            return;
        }
    
        $prevRes = $res;
    
        if (isset($this->form->DebugUtilities))
        {
            $this->form->DebugUtilities->content->track_res->text = $res;
        }
    }
    
    private function getScenes()
    {
        return [
            $this->form->MainGame,
            $this->form->MainMenu,
            $this->form->Pda,
            $this->form->Dialog,
            $this->form->ExitDialog,
            $this->form->Inventory,
            $this->form->Fail,
            $this->form->DebugUtilities
        ];
    }    
    
    private function scaleScenes($w, $h)
    {
        foreach ($this->getScenes() as $obj)
        {
            $scale = min($w / $obj->width, $h / $obj->height);
    
            $obj->scaleX = $scale;
            $obj->scaleY = $scale;
            $obj->x = ($w - $obj->width) / 2;
            $obj->y = ($h - $obj->height) / 2;
        }
    }

    public function setFullscreen(bool $state)
    {
        $this->form->fullScreen = $state;
    
        $this->ltx->w_bool('vid_fullscreen', $state);
        $this->ltx->save();
    }
    
    public function toggleFullscreen()
    {
        $newState = !$this->form->fullScreen;
    
        $this->form->fullScreen = $newState;
    
        $this->ltx->w_bool('vid_fullscreen', $newState);
        $this->ltx->save();
    }    
}