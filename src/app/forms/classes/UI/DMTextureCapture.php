<?php
namespace app\forms\classes\UI;

use app\forms\classes\Log;
use php\gui\UXImageView;
use php\gui\shape\UXRectangle;
use php\gui\layout\UXAnchorPane;
use php\gui\UXImage;

class DMTextureCapture
{
    public static function Crop($texture, int $x, int $y, int $width, int $height, string $name = 'default'): UXImage
    {
        if (is_string($texture))
        {
            $texture = new UXImage($texture);
        }

        Log::info("[DMTextureCapture] Added: {$name} (x={$x}, y={$y}, size={$width}x{$height})");

        $container = new UXAnchorPane();
        $container->size = [$width, $height];

        $clip = new UXRectangle();
        $clip->width = $width;
        $clip->height = $height;
        $container->clip = $clip;

        $view = new UXImageView($texture);
        $view->position = [-$x, -$y];
        $view->size = [$texture->width, $texture->height];
        $container->add($view);

        return $container->snapshot();
    }
}