<?php
namespace app\forms\classes\Environment;

class EnvironmentData 
{
    public static $brightnessByCycle = [
        'morning'     => -0.1,
        'day'         =>  0.0,
        'evening'     => -0.2,
        'night'       => -0.4,
        'underground' => -0.4,
    ];
    
    public static $locations = [
        'L0' => [
            'morning' => [ 'path' => 'environment/L0/L0_Morning', 'anomaly' => true ],
            'day'     => [ 'path' => 'environment/L0/L0_Day',     'anomaly' => true ],
            'evening' => [ 'path' => 'environment/L0/L0_Evening', 'anomaly' => true ],
            'night'   => [ 'path' => 'environment/L0/L0_Night',   'rain' => true, 'anomaly' => true ],
        ],
        'L1' => [
            'morning' => [ 'path' => 'environment/L1/L1_Morning', 'anomaly' => true ],
            'day'     => [ 'path' => 'environment/L1/L1_Day',     'anomaly' => true ],
            'evening' => [ 'path' => 'environment/L1/L1_Evening', 'anomaly' => true ],
            'night'   => [ 'path' => 'environment/L1/L1_Night',   'anomaly' => true ],
        ],
        'L2' => [
            'morning' => [ 'path' => 'environment/L2/L2_Morning', 'anomaly' => true ],
            'day'     => [ 'path' => 'environment/L2/L2_Day',     'anomaly' => true ],
            'evening' => [ 'path' => 'environment/L2/L2_Evening', 'anomaly' => true ],
            'night'   => [ 'path' => 'environment/L2/L2_Night',   'anomaly' => true ],
        ],
        'L3' => [
            'morning' => [ 'path' => 'environment/L3/L3_Morning', 'anomaly' => true ],
            'day'     => [ 'path' => 'environment/L3/L3_Day',     'anomaly' => true ],
            'evening' => [ 'path' => 'environment/L3/L3_Evening', 'anomaly' => true ],
            'night'   => [ 'path' => 'environment/L3/L3_Night',   'rain' => true, 'anomaly' => true ],
        ],
        'L4' => [
            'morning' => [ 'path' => 'environment/L4/L4_Morning', 'anomaly' => true ],
            'day'     => [ 'path' => 'environment/L4/L4_Day',     'anomaly' => true ],
            'evening' => [ 'path' => 'environment/L4/L4_Evening', 'anomaly' => true ],
            'night'   => [ 'path' => 'environment/L4/L4_Night',   'rain' => true, 'anomaly' => true ],
        ],
        'L5' => [
            'underground' => [ 'path' => 'environment/L5/L5U_Underground'],
        ],
    ];    
    
    public static $thunderSounds = [
        'environment/weather/thunder-0',
        'environment/weather/thunder-1',
        'environment/weather/thunder-2',
        'environment/weather/thunder-3',
    ];

    public static $ambientSounds = [
        [ 'path' => 'environment/ambient/amb00', 'length' => 322 ],
        [ 'path' => 'environment/ambient/amb01', 'length' => 247 ],
        [ 'path' => 'environment/ambient/amb02', 'length' => 248 ],
        [ 'path' => 'environment/ambient/amb03', 'length' => 299 ],
        [ 'path' => 'environment/ambient/amb04', 'length' => 796 ],
        [ 'path' => 'environment/ambient/amb05', 'length' => 363 ],
        [ 'path' => 'environment/ambient/amb06', 'length' => 191 ],
    ];   
    
    public static $rndSoundsByCycle = [
        'evening' => [
            'environment/rnd_outdoor/rnd_boar', 'environment/rnd_outdoor/rnd_wind_tree',
            'environment/rnd_outdoor/rnd_horror','environment/rnd_outdoor/rnd_pdog',
            'environment/rnd_outdoor/rnd_pdog1','environment/rnd_outdoor/rnd_cat2',
            'environment/rnd_outdoor/rnd_cat1','environment/rnd_outdoor/rnd_boar3',
            'environment/rnd_outdoor/crickets_1','environment/rnd_outdoor/crickets_2',
            'environment/rnd_outdoor/crickets_3','environment/rnd_outdoor/rnd_dog',
            'environment/rnd_outdoor/rnd_dog1','environment/rnd_outdoor/rnd_dog2',
            'environment/rnd_outdoor/rnd_dog3','environment/rnd_outdoor/rnd_fly2',
            'environment/rnd_outdoor/rnd_fly3','environment/rnd_outdoor/rnd_krik1',
            'environment/rnd_outdoor/rnd_krik2','environment/rnd_outdoor/rnd_krik3',
            'environment/rnd_outdoor/rnd_moan','environment/rnd_outdoor/rnd_moan1',
            'environment/rnd_outdoor/owl_1','environment/rnd_outdoor/owl_2',
            'environment/rnd_outdoor/owl_3','environment/rnd_outdoor/rnd_dark',
            'environment/rnd_outdoor/rnd_dark0','environment/rnd_outdoor/rnd_dark1',
            'environment/rnd_outdoor/rnd_shooting_5','environment/rnd_outdoor/rnd_shooting_7',
            'environment/rnd_outdoor/rnd_swamp','environment/rnd_outdoor/rnd_dark4',
            'environment/rnd_outdoor/rnd_howling_1','environment/rnd_outdoor/rnd_howling_2',
        ],
        'night' => [
            'environment/rnd_outdoor/rnd_boar','environment/rnd_outdoor/rnd_wind_tree',
            'environment/rnd_outdoor/rnd_shooting_6','environment/rnd_outdoor/rnd_horror',
            'environment/rnd_outdoor/rnd_pdog1','environment/rnd_outdoor/rnd_pdog2',
            'environment/rnd_outdoor/rnd_obval','environment/rnd_outdoor/rnd_cat1',
            'environment/rnd_outdoor/rnd_dark5','environment/rnd_outdoor/rnd_dark8',
            'environment/rnd_outdoor/crickets_2','environment/rnd_outdoor/rnd_dark9',
            'environment/rnd_outdoor/rnd_dark3','environment/rnd_outdoor/rnd_dark10',
            'environment/rnd_outdoor/rnd_horror1','environment/rnd_outdoor/owl_1',
            'environment/rnd_outdoor/owl_2','environment/rnd_outdoor/rnd_krik9',
            'environment/rnd_outdoor/rnd_krik8','environment/rnd_outdoor/rnd_krik7',
            'environment/rnd_outdoor/rnd_moan5','environment/rnd_outdoor/rnd_moan6',
            'environment/rnd_outdoor/rnd_rock2','environment/rnd_outdoor/rnd_rock3',
            'environment/rnd_outdoor/rnd_rock4','environment/rnd_outdoor/rnd_dark',
            'environment/rnd_outdoor/rnd_dark0','environment/rnd_outdoor/rnd_dark1',
            'environment/rnd_outdoor/rnd_shooting_9','environment/rnd_outdoor/rnd_shOOTing_10',
            'environment/rnd_outdoor/rnd_dark4','environment/rnd_outdoor/rnd_howling_1',
            'environment/rnd_outdoor/rnd_howling_2',
        ],
        'morning' => [
            'environment/rnd_outdoor/rnd_boar1','environment/rnd_outdoor/rnd_bird1',
            'environment/rnd_outdoor/rnd_bird2','environment/rnd_outdoor/rnd_bird4',
            'environment/rnd_outdoor/rnd_boar','environment/rnd_outdoor/rnd_boar2',
            'environment/rnd_outdoor/rnd_boar3','environment/rnd_outdoor/rnd_darkwind5',
            'environment/rnd_outdoor/rnd_dog','environment/rnd_outdoor/rnd_dog1',
            'environment/rnd_outdoor/rnd_dog2','environment/rnd_outdoor/rnd_dog3',
            'environment/rnd_outdoor/rnd_fly','environment/rnd_outdoor/rnd_fly1',
            'environment/rnd_outdoor/rnd_fly2','environment/rnd_outdoor/rnd_fly3',
            'environment/rnd_outdoor/rnd_krik6','environment/rnd_outdoor/rnd_krik8',
            'environment/rnd_outdoor/rnd_krik9','environment/rnd_outdoor/rnd_moan',
            'environment/rnd_outdoor/rnd_moan3','environment/rnd_outdoor/rnd_shooting_4',
            'environment/rnd_outdoor/rnd_krik3','environment/rnd_outdoor/rnd_shooting_9',
            'environment/rnd_outdoor/rnd_shooting_3','environment/rnd_outdoor/rnd_swamp',
            'environment/rnd_outdoor/rnd_wind_tree',
        ],
        'day' => [
            'environment/rnd_outdoor/rnd_boar3','environment/rnd_outdoor/rnd_dark10',
            'environment/rnd_outdoor/rnd_dark6','environment/rnd_outdoor/rnd_dark2',
            'environment/rnd_outdoor/rnd_dark5','environment/rnd_outdoor/rnd_wind_tree',
            'environment/rnd_outdoor/crow1','environment/rnd_outdoor/crow2',
            'environment/rnd_outdoor/crow3','environment/rnd_outdoor/rnd_bird2',
            'environment/rnd_outdoor/rnd_boar','environment/rnd_outdoor/rnd_boar2',
            'environment/rnd_outdoor/rnd_boar3','environment/rnd_outdoor/rnd_darkwind3',
            'environment/rnd_outdoor/rnd_darkwind4','environment/rnd_outdoor/rnd_darkwind5',
            'environment/rnd_outdoor/rnd_dog','environment/rnd_outdoor/rnd_dog1',
            'environment/rnd_outdoor/rnd_dog2','environment/rnd_outdoor/rnd_dog3',
            'environment/rnd_outdoor/rnd_fly','environment/rnd_outdoor/rnd_fly1',
            'environment/rnd_outdoor/rnd_fly2','environment/rnd_outdoor/rnd_fly3',
            'environment/rnd_outdoor/rnd_krik3','environment/rnd_outdoor/rnd_krik2',
            'environment/rnd_outdoor/rnd_krik1','environment/rnd_outdoor/rnd_krik4',
            'environment/rnd_outdoor/rnd_krik5','environment/rnd_outdoor/rnd_krik6',
            'environment/rnd_outdoor/rnd_moan2','environment/rnd_outdoor/rnd_moan3',
            'environment/rnd_outdoor/rnd_shooting_1','environment/rnd_outdoor/rnd_shooting_2',
            'environment/rnd_outdoor/rnd_shooting_3','environment/rnd_outdoor/rnd_shooting_4',
            'environment/rnd_outdoor/rnd_shooting_5','environment/rnd_outdoor/rnd_shooting_7',
            'environment/rnd_outdoor/rnd_shooting_8','environment/rnd_outdoor/rnd_shooting_9',
            'environment/rnd_outdoor/rnd_shooting_10','environment/rnd_outdoor/rnd_swamp',
            'environment/rnd_outdoor/rnd_wind_tree',
        ],
        'underground' => [
            'environment/underground/breath_1','environment/underground/breath_2',
            'environment/underground/hit_2','environment/underground/hit_1',
            'environment/underground/strange_noise_1','environment/underground/strange_noise_2',
            'environment/underground/strange_noise_3','environment/underground/rnd_drop_1',
            'environment/underground/rnd_drop_2','environment/underground/rnd_drop_3',
            'environment/underground/rnd_drop_4','environment/underground/rnd_drop_5',
            'environment/underground/rnd_drop_6','environment/underground/rnd_metal1',
            'environment/underground/rnd_metal2','environment/underground/rnd_metal3',
            'environment/underground/rnd_rat_panic_1','environment/underground/rnd_rat_panic_2',
            'environment/underground/rnd_rat_panic_3',
        ],
    ];

    public static $effectsByName = [
        'ae0_effect_0' => ['life_time' => 10, 'sound' => 'environment/rnd_outdoor/rnd_wind_3'],
        'ae0_effect_1' => ['life_time' => 7,  'sound' => 'environment/rnd_outdoor/rnd_wind_2'],
        'ae0_effect_2' => ['life_time' => 10, 'sound' => 'environment/rnd_outdoor/rnd_wind_3'],
        'ae0_effect_3' => ['life_time' => 10, 'sound' => 'environment/rnd_outdoor/rnd_wind_3'],
        'ae0_effect_4' => ['life_time' => 15, 'sound' => 'environment/rnd_outdoor/rnd_wind_2'],
        'ae0_effect_5' => ['life_time' => 7,  'sound' => 'environment/rnd_outdoor/rnd_wind_2'],
        'ae0_effect_6' => ['life_time' => 7,  'sound' => 'environment/rnd_outdoor/rnd_wind_1'],
        'ae0_effect_7' => ['life_time' => 8,  'sound' => 'environment/rnd_outdoor/rnd_wind_1'],
        'ae0_effect_8' => ['life_time' => 7,  'sound' => 'environment/rnd_outdoor/rnd_wind_2'],
        'ae0_effect_9' => ['life_time' => 10, 'sound' => 'environment/rnd_outdoor/rnd_wind_3'],
    ];

    public static $effectsByCycle = [
        'morning' => ['ae0_effect_4'],
        'day'     => ['ae0_effect_1','ae0_effect_2','ae0_effect_3','ae0_effect_5','ae0_effect_6','ae0_effect_7','ae0_effect_8','ae0_effect_9'],
        'evening' => ['ae0_effect_0','ae0_effect_1','ae0_effect_2','ae0_effect_3','ae0_effect_8'],
        'night'   => ['ae0_effect_1','ae0_effect_2','ae0_effect_3','ae0_effect_8'],
    ];
    
    public static $sfxPeriods = [
        'evening'     => [6, 9],
        'night'       => [6, 9],
        'morning'     => [8, 14],
        'day'         => [8, 14],
        'underground' => [5, 10],
    ];

    public static $effectPeriods = [
        'morning' => [30, 60],
        'day'     => [40, 90],
        'evening' => [90, 120],
        'night'   => [130, 160],
    ];    
}