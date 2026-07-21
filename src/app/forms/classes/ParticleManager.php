<?php
namespace app\forms\classes;

use app\forms\classes\Log;
use php\lang\ThreadPool;
use behaviour\custom\ColorAdjustEffectBehaviour;
use php\gui\UXImageView;
use php\gui\UXImage;
use php\gui\UXApplication;
use php\time\Timer;
use action\Animation;
use php\lang\Thread;
use behaviour\custom\GlowEffectBehaviour;
use behaviour\custom\BloomEffectBehaviour;
use php\gui\animation\UXAnimationTimer;
use app\forms\classes\Environment\EnvironmentBase;
use app\forms\classes\Environment\EnvironmentBrightness;

class ParticleManager
{
    protected $form;
    
    protected $images = [];
    
    protected $particleUpdater;
    protected $activeParticles = [];    
    
    protected $particlePools = [];    
    protected $createdParticles = [];      
    
    public function preload(): void
    {
        $paths = [
            'res://.data/ui/particles/shoot.png',
            'res://.data/ui/particles/blood.png',
            'res://.data/ui/particles/bullet_.png'
        ];
    
        foreach ($paths as $path)
        {
            $this->images[$path] = new UXImage($path);
        }
    }
    
    protected function img(string $path): UXImage
    {
        if (!isset($this->images[$path]))
        {
            $this->images[$path] = new UXImage($path);
        }
    
        return $this->images[$path];
    }
    
    public function __construct($form)
    {
        $this->form = $form;
        
        $this->particleUpdater = new UXAnimationTimer(function() {
        
            if (!$this->activeParticles)
            {
                return;
            }
        
            foreach ($this->activeParticles as $key => $p)
            {
                $this->updateParticle($key, $p);
            }
        
            $this->activeParticles = array_values($this->activeParticles);
        });
        
        $this->particleUpdater->start();
    }
    
    protected function acquireParticle(string $type): UXImageView
    {
        if (!isset($this->particlePools[$type]))
        {
            $this->particlePools[$type] = [];
        }
    
        if (!empty($this->particlePools[$type]))
        {
            $particle = array_pop($this->particlePools[$type]);
            
            $particle->data['_inPool'] = false;
            $particle->data['released'] = false;
            
            $particle->visible = true;
            
            return $particle;
        }
        
        if (!isset($this->createdParticles[$type]))
        {
            $this->createdParticles[$type] = 0;
        }
        
        $this->createdParticles[$type]++;
        
        //echo "CREATED {$type}: {$this->createdParticles[$type]}\n";   
    
        return new UXImageView();
    }    
    
    protected function releaseParticle(string $type, UXImageView $particle): void
    {
        if (!empty($particle->data['_inPool']))
        {
            return;
        }
    
        $particle->data['_inPool'] = true;    
    
        if (isset($particle->data['timer']))
        {
            $particle->data['timer']->stop();
            $particle->data['timer'] = null;
        }    
    
        $particle->visible = false;
        $particle->opacity = 1;
        $particle->rotate = 0;
        
        $initialized = $particle->data['initialized'] ?? false;
        $bloom       = $particle->data['bloom'] ?? null;
        $glow        = $particle->data['glow'] ?? null;
    
        $particle->data = [
            '_inPool'     => true,
            'released'    => false,
            'initialized' => $initialized,
            'bloom'       => $bloom,
            'glow'        => $glow
        ];

        $particle->x = -99999;
        $particle->y = -99999;        
    
        $this->particlePools[$type][] = $particle;
        
        /*
        //Log::info(sprintf("[POOL] %s | created=%d | pooled=%d", $type, $this->createdParticles[$type] ?? 0, count($this->particlePools[$type])));    
        echo sprintf(
            "[POOL] %s | created=%d | pooled=%d\n",
            $type,
            $this->createdParticles[$type] ?? 0,
            count($this->particlePools[$type])
        );  
        */      
    }
    
    protected function setupParticle(string $type, string $image, int $width, int $height): UXImageView
    {
        $p = $this->acquireParticle($type);
    
        if (!($p->data['initialized'] ?? false))
        {
            $p->image = $this->img($image);
    
            (new ColorAdjustEffectBehaviour())->apply($p);
    
            $p->data['initialized'] = true;
        }
        
        if ($type !== 'shoot')
        {        
            $p->colorAdjustEffect->brightness = $this->form->form('Client')->MainGame->content->EnvironmentBrightness->get();
        }
        
        $p->visible = true;
        $p->opacity = 1;
        $p->width = $width;
        $p->height = $height;
        $p->rotate = 0;
    
        if (!$p->parent)
        {
            $this->form->form('Client')->MainGame->content->add($p);
        }
    
        return $p;
    }    
    
    protected function updateParticle($key, $p)
    {
        switch ($p->data['type'] ?? null)
        {
            case 'blood':
    
                $p->x += $p->data['vx'];
                $p->y += $p->data['vy'];
    
                $p->data['vy'] += 0.35;
                
                $floorOffset = 40;
                
                if (isset($p->data['floorY']) && $p->y >= $p->data['floorY'] - $floorOffset)
                {
                    $p->y = $p->data['floorY'] - $floorOffset;
                
                    $p->data['vy'] = 0;
                    $p->data['vx'] *= 0.85;
                }
    
                $p->data['life']--;
    
                if ($p->data['life'] < 20)
                {
                    $p->opacity = $p->data['life'] / 20;
                }
    
                if ($p->data['life'] <= 0)
                {
                    $this->releaseParticle('blood', $p);
                    unset($this->activeParticles[$key]);
                }
    
            break;
            
            case 'shell':
            
                $p->x += $p->data['vx'];
                $p->y += $p->data['vy'];
            
                $p->data['vy'] += $p->data['gravity'];
                $p->data['vx'] *= $p->data['friction'];
            
                $p->rotate += $p->data['rotationSpeed'];
            
                if ($p->y >= $p->data['groundY'] - $p->height - 30)
                {
                    $p->y = $p->data['groundY'] - $p->height - 30;
                
                    $p->data['vy'] = -$p->data['vy'] * 0.45;
                    $p->data['vx'] *= 0.7;
                
                    $p->data['rotationSpeed'] *= 0.75;
                
                    if (abs($p->data['vy']) < 1.5)
                    {
                        $p->data['vy'] = 0;
                        $p->data['vx'] *= 0.3;
                
                        if (abs($p->data['vx']) < 0.3)
                        {
                            $p->data['vx'] = 0;
                            $p->data['rotationSpeed'] = 0;
                        }
                    }
                }
            
                $p->data['life']--;
            
                if ($p->data['life'] < 60)
                {
                    $p->opacity = $p->data['life'] / 60;
                }
            
                if ($p->data['life'] <= 0)
                {
                    $this->releaseParticle('shell', $p);
                    unset($this->activeParticles[$key]);
                }
            
            break;   
            
            case 'bullet':
            
                $p->x += $p->data['vx'];
                $p->y += $p->data['vy'];
            
                $p->data['life']--;
            
                if ($p->data['life'] < 5)
                {
                    $p->opacity = $p->data['life'] / 5;
                }
            
                if ($p->data['life'] <= 0)
                {
                    $this->releaseParticle('bullet', $p);
                    unset($this->activeParticles[$key]);
                }
            
            break;
            
            case 'shoot':
            
                $p->data['life']--;
            
                $p->opacity = min(1, $p->data['life'] / 10);
            
                if ($p->data['life'] == 14)
                {
                    $bloom = $p->data['bloom'] ?? null;
                    $glow  = $p->data['glow'] ?? null;
            
                    if ($bloom) $bloom->threshold = 1.0;
                    if ($glow)  $glow->level = 0.0;
                }
            
                if ($p->data['life'] <= 0)
                {
                    $this->releaseParticle('shoot', $p);
                    unset($this->activeParticles[$key]);
                }
            
            break;                          
        }
    }   
 
    public function weaponShot($actorModel, $enemyModel, float $muzzleOffsetX, float $muzzleOffsetY): void
    {
        if (!$actorModel) return;
    
        $p = $this->setupParticle('shoot', 'res://.data/ui/particles/shoot.png', 128, 128);
        
        if (!isset($p->data['bloom']))
        {
            $bloom = new BloomEffectBehaviour();
            $bloom->threshold = 1.0;
            $bloom->apply($p);
        
            $glow = new GlowEffectBehaviour();
            $glow->level = 0.0;
            $glow->apply($p);
        
            $p->data['bloom'] = $bloom;
            $p->data['glow'] = $glow;
        }
        
        $p->x = $actorModel->x + $muzzleOffsetX;
        $p->y = $actorModel->y + $muzzleOffsetY;
        
        $p->data['type'] = 'shoot';
        $p->data['life'] = 20;
        
        $bloom = $p->data['bloom'] ?? null;
        $glow  = $p->data['glow'] ?? null;
        
        if ($bloom) $bloom->threshold = 0.15;
        if ($glow)  $glow->level = 0.35;
        
        $this->activeParticles[] = $p;
        
        $hitX   = $enemyModel->x + ($enemyModel->width / 2);
        $hitY   = $actorModel->y + $muzzleOffsetY;
        $floorY = $enemyModel->y + $enemyModel->height - 40;        
        
        $this->spawnBullet($actorModel, $enemyModel, $muzzleOffsetX, $muzzleOffsetY);
        $this->spawnShell($actorModel, $muzzleOffsetX, $muzzleOffsetY, $floorY);    

        if (!$enemyModel || !$enemyModel->visible) return;
        if ($actorModel->x > $enemyModel->x) return;    
    
        $this->bloodBurstAtPoint($hitX, $hitY, $floorY, 4, 7);
    }
    
    public function spawnBullet($actorModel, $enemyModel, float $offsetX, float $offsetY): void
    {
        if (!$actorModel || !$enemyModel)
        {
            return;
        }
    
        $p = $this->setupParticle('bullet', 'res://.data/ui/particles/bullet_.png', 32, 32);    
    
        $startX = $actorModel->x + $offsetX;
        $startY = $actorModel->y + $offsetY + 50;
    
        $targetX = $startX + 2000;
        $targetY = $startY + rand(-10, 10);
    
        $dx = $targetX - $startX;
        $dy = $targetY - $startY;
    
        $distance = sqrt($dx * $dx + $dy * $dy);
    
        $speed = 150;
    
        $p->x = $startX;
        $p->y = $startY;
    
        $p->data['type'] = 'bullet';
    
        $p->data['vx'] = ($dx / $distance) * $speed;
        $p->data['vy'] = ($dy / $distance) * $speed;
    
        $p->data['life'] = (int)($distance / $speed);
    
        $this->activeParticles[] = $p;
    }
        
    public function spawnShell($actorModel, float $offsetX, float $offsetY, float $groundY): void
    {
        if (!$actorModel) return;
            
        $p = $this->setupParticle('shell', 'res://.data/ui/particles/bullet_.png', 32, 32);    
    
        $p->x = $actorModel->x + $offsetX;
        $p->y = $actorModel->y + $offsetY;
    
        $p->data['type'] = 'shell';
    
        $p->data['vx'] = -rand(6, 10);
        $p->data['vy'] = -rand(12, 16);
    
        $p->data['gravity'] = 0.6;
        $p->data['friction'] = 0.98;
    
        $p->data['rotationSpeed'] = rand(8, 16);
    
        $p->data['groundY'] = $groundY + 75;
        $p->data['life'] = 240;
    
        $this->activeParticles[] = $p;
    }
    
    protected function makeBloodFactory(float $originX, float $originY, int $scatterX, int $scatterY): callable
    {
        return function () use ($originX, $originY, $scatterX, $scatterY) {
        
            $p = $this->setupParticle('blood', 'res://.data/ui/particles/blood.png', 86, 86);                      
            
            $p->x = $originX - ($p->width / 2) + $scatterX;
            $p->y = $originY - ($p->height / 2) + $scatterY;

            return $p;
        };
    }    

    public function bloodBurstAtPoint(float $originX, float $originY, float $floorY, int $countMin = 6, int $countMax = 10): void
    {
        $count = rand($countMin, $countMax);
    
        for ($i = 0; $i < $count; $i++)
        {
            $scatterX = rand(-4, 4);
            $scatterY = rand(-4, 4);
    
            $angleRad = deg2rad(rand(0, 359));
            $force    = rand(35, 70);
    
            $impulseX = cos($angleRad) * $force;
            $impulseY = sin($angleRad) * $force - rand(15, 30);
    
            $factory = $this->makeBloodFactory($originX, $originY, $scatterX, $scatterY);
    
            $p = $factory();
    
            $p->data['type'] = 'blood';
    
            $p->data['vx'] = $impulseX / 12;
            $p->data['vy'] = $impulseY / 12;
    
            $p->data['life'] = 60;
            
            $p->data['floorY'] = $floorY;            
    
            $this->activeParticles[] = $p;
        }
    }

    public function bloodConeAtTarget($targetModel, int $countMin = 4, int $countMax = 6): void
    {
        $hitX   = $targetModel->x + ($targetModel->width / 2);
        $hitY   = $targetModel->y + 5;
        $floorY = $targetModel->y + $targetModel->height - 20;

        $this->bloodConeAtPoint($hitX, $hitY, $floorY, $countMin, $countMax);
    }

    public function bloodConeAtPoint(float $originX, float $originY, float $floorY, int $countMin = 4, int $countMax = 6): void
    {
        $count = rand($countMin, $countMax);
    
        for ($i = 0; $i < $count; $i++)
        {
            $scatterX = rand(-12, 12);
            $scatterY = rand(-8, 8);
    
            $angleRad = deg2rad(rand(70, 100));
            $force    = rand(140, 220);
    
            $impulseX = cos($angleRad) * $force;
            $impulseY = -sin($angleRad) * $force;
    
            $factory = $this->makeBloodFactory($originX, $originY, $scatterX, $scatterY);
    
            $p = $factory();
    
            $p->data['type'] = 'blood';
    
            $p->data['vx'] = $impulseX / 12;
            $p->data['vy'] = $impulseY / 12;
    
            $p->data['life'] = 80;
            
            $p->data['floorY'] = $floorY;            
    
            $this->activeParticles[] = $p;
        }
    }
}