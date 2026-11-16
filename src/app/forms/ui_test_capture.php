<?php
namespace app\forms;

use app\forms\classes\UI\DMTextureCapture;
use php\gui\shape\UXRectangle;
use php\gui\layout\UXAnchorPane;
use php\gui\UXImage;
use php\gui\UXImageView;
use php\gui\framework\AbstractForm;
use php\gui\event\UXWindowEvent; 


class ui_test_capture extends AbstractForm
{

    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {
        $hud = $this->form('ui_test_capture');
        $texture = "res://.data/img/ui_hud.png";

        $health = DMTextureCapture::Crop($texture, 185, 38, 164, 34, 'ui_hud_shkala_health');
        $view = new UXImageView($health);
        $view->position = [860, 660];
        $view->size = [155, 34];
        $hud->add($view);

        $icon = DMTextureCapture::Crop($texture, 209, 204, 19, 18, 'ui_hud_icon_health');
        $view = new UXImageView($icon);
        $view->position = [865, 670];
        $hud->add($view);

        $armor = DMTextureCapture::Crop($texture, 185, 0, 164, 38, 'ui_hud_shkala_armor');
        $view = new UXImageView($armor);
        $view->position = [860, 622];
        $view->size = [155, 34];
        $hud->add($view);

        $icon = DMTextureCapture::Crop($texture, 228, 204, 19, 18, 'ui_hud_icon_armour');
        $view = new UXImageView($icon);
        $view->position = [865, 628];
        $hud->add($view);

        $weapon = DMTextureCapture::Crop($texture, 185, 72, 157, 65, 'ui_hud_frame_patron');
        $view = new UXImageView($weapon);
        $view->position = [860, 692];
        $hud->add($view);

        $background = DMTextureCapture::Crop($texture, 349, 65, 125, 125, 'ui_hud_stamina_full');
        $view = new UXImageView($background);
        $view->position = [0, 643];
        $hud->add($view);

        $state = DMTextureCapture::Crop($texture, 0, 428, 65, 75, 'ui_hud_soldier_normal');
        $view = new UXImageView($state);
        $view->position = [27, 665];
        $hud->add($view);

        $stamina = DMTextureCapture::Crop($texture, 347, 190, 57, 7, 'ui_hud_shk_stamina');
        $view = new UXImageView($stamina);
        $view->position = [32, 737];
        $hud->add($view);

        $luminosity = DMTextureCapture::Crop($texture, 474, 65, 10, 71, 'ui_hud_shk_light');
        $view = new UXImageView($luminosity);
        $view->position = [106, 677];
        $hud->add($view);

        $noise = DMTextureCapture::Crop($texture, 484, 65, 10, 71, 'ui_hud_shk_noise');
        $view = new UXImageView($noise);
        $view->position = [10, 677];
        $hud->add($view);

        $map = DMTextureCapture::Crop($texture, 0, 0, 185, 192, 'ui_hud_map');
        $view = new UXImageView($map);
        $view->position = [0, 0];
        $view->size = [185, 192];
        $hud->add($view);

        $counter = DMTextureCapture::Crop($texture, 0, 192, 35, 28, 'ui_hud_map_counter');
        $view = new UXImageView($counter);
        $view->position = [138, 167];
        $view->size = [35, 28];
        $hud->add($view);
        
        $healthBar = DMTextureCapture::Crop($texture, 509, 50, 110, 10, 'ui_hud_shk_health');
        $view = new UXImageView($healthBar);
        $view->position = [891, 665];
        $view->size = [104, 11];
        $hud->add($view);
        
        $armorBar = DMTextureCapture::Crop($texture, 347, 197, 110, 10, 'ui_hud_shk_armour');
        $view = new UXImageView($armorBar);
        $view->position = [891, 643];
        $view->size = [104, 11];
        $hud->add($view); 
/*        
        $statusIcons = [
            ['ui_hud_icon_weapon', 289, 290],
            ['ui_hud_icon_radiation', 97, 290],
            ['ui_hud_icon_drop', 225, 290],
            ['ui_hud_icon_eat', 161, 226],
            ['ui_hud_icon_psycho', 225, 226],
            ['ui_hud_icon_goodmode', 161, 290],
            ['ui_hud_icon_sleep', 97, 226],
            ['ui_hud_icon_artefact', 289, 226]
        ];
        
        foreach ($statusIcons as $i => $icon)
        {
            $x = 768 + ($i % 4) * 64;
            $y = 494 + (int)($i / 4) * 64;
        
            $image = DMTextureCapture::Crop(
                $texture,
                $icon[1],
                $icon[2],
                64,
                64,
                $icon[0]
            );
        
            $view = new UXImageView($image);
            $view->position = [$x, $y];
            $view->size = [64, 64];
            $hud->add($view);
        } 
        
*/            
    }
}
