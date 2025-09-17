<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plant extends Model
{

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'common_name',
        'watering_general_benchmark',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'watering_general_benchmark' => 'json',
            'common_name' => 'string'
        ];
    }

    public function users(){
        return $this->belongsToMany(
            User::class,
            "user_plant",
            "plant_id",
            "user_id"
        );
    }
}




// {
//     "id": 100,
//     "common_name": "Pixie Japanese Maple",
//     "scientific_name": [
//         "Acer palmatum 'Pixie'"
//     ],
//     "other_name": [],
//     "family": null,
//     "hybrid": null,
//     "authority": null,
//     "subspecies": null,
//     "cultivar": "Pixie",
//     "variety": null,
//     "species_epithet": "palmatum",
//     "genus": "Acer",
//     "origin": [
//         "Japan"
//     ],
//     "type": "tree",
//     "dimensions": [
//         {
//             "type": "Height",
//             "min_value": 10,
//             "max_value": 10,
//             "unit": "feet"
//         }
//     ],
//     "cycle": "Perennial",
//     "attracts": [],
//     "propagation": [
//         "Air Layering Propagation",
//         "Grafting Propagation",
//         "Cutting",
//         "Division",
//         "Seed Propagation"
//     ],
//     "hardiness": {
//         "min": "6",
//         "max": "6"
//     },
//     "hardiness_location": {
//         "full_url": "https://perenual.com/api/hardiness-map?species_id=100&size=og&key=sk-gDPx68cac34e697cc12426",
//         "full_iframe": "<iframe frameborder=0 scrolling=yes seamless=seamless width=1000 height=550 style='margin:auto;' src='https://perenual.com/api/hardiness-map?species_id=100&size=og&key=sk-gDPx68cac34e697cc12426'></iframe>"
//     },
//     "watering": "Average",
//     "watering_general_benchmark": {
//         "value": "\"7-10\"",
//         "unit": "days"
//     },
//     "plant_anatomy": [],
//     "sunlight": [
//         "full sun",
//         "part shade"
//     ],
//     "pruning_month": [
//         "March",
//         "April",
//         "March",
//         "April",
//         "May",
//         "June",
//         "July",
//         "August",
//         "September",
//         "October",
//         "November"
//     ],
//     "pruning_count": [],
//     "seeds": false,
//     "maintenance": null,
//     "care_guides": "http://perenual.com/api/species-care-guide-list?species_id=100&key=sk-gDPx68cac34e697cc12426",
//     "soil": [],
//     "growth_rate": "Low",
//     "drought_tolerant": false,
//     "salt_tolerant": false,
//     "thorny": false,
//     "invasive": false,
//     "tropical": false,
//     "indoor": false,
//     "care_level": "Moderate",
//     "pest_susceptibility": [],
//     "flowers": false,
//     "flowering_season": null,
//     "cones": false,
//     "fruits": false,
//     "edible_fruit": false,
//     "harvest_season": null,
//     "leaf": true,
//     "edible_leaf": false,
//     "cuisine": false,
//     "medicinal": false,
//     "poisonous_to_humans": false,
//     "poisonous_to_pets": false,
//     "description": "Pixie Japanese Maple is an amazing ornamental shrub that will quickly make your garden shine with its unique structure and showy foliage. Its small, fern-like, deep-green leaves with a pinkish-red edge add to the eye-catching quality of this variety. It has an upright, bushy growth that reaches around 2 metres in height, making it an ideal choice for a low-maintenance ornamental plant. Pixie is also very tolerant of a variety of soils and climates, so it's an ideal choice for a range of garden situations. No matter where you grow it, you can expect bold colour from spring through to autumn - why not add this statement piece to your garden today?",
//     "default_image": {
//         "license": 45,
//         "license_name": "Attribution-ShareAlike 3.0 Unported (CC BY-SA 3.0)",
//         "license_url": "https://creativecommons.org/licenses/by-sa/3.0/deed.en",
//         "original_url": "https://perenual.com/storage/species_image/100_acer_palmatum_pixie/og/Acer_palmatum_27Oshio_beni27_-_JPG2.jpg",
//         "regular_url": "https://perenual.com/storage/species_image/100_acer_palmatum_pixie/regular/Acer_palmatum_27Oshio_beni27_-_JPG2.jpg",
//         "medium_url": "https://perenual.com/storage/species_image/100_acer_palmatum_pixie/medium/Acer_palmatum_27Oshio_beni27_-_JPG2.jpg",
//         "small_url": "https://perenual.com/storage/species_image/100_acer_palmatum_pixie/small/Acer_palmatum_27Oshio_beni27_-_JPG2.jpg",
//         "thumbnail": "https://perenual.com/storage/species_image/100_acer_palmatum_pixie/thumbnail/Acer_palmatum_27Oshio_beni27_-_JPG2.jpg"
//     },
//     "other_images": "Upgrade Plan To Supreme For Access https://perenual.com/subscription-api-pricing. Im sorry",
//     "xWateringQuality": "Upgrade Plan To Supreme For Access https://perenual.com/subscription-api-pricing. Im sorry",
//     "xWateringPeriod": "Upgrade Plan To Supreme For Access https://perenual.com/subscription-api-pricing. Im sorry",
//     "xWateringAvgVolumeRequirement": "Upgrade Plan To Supreme For Access https://perenual.com/subscription-api-pricing. Im sorry",
//     "xWateringDepthRequirement": "Upgrade Plan To Supreme For Access https://perenual.com/subscription-api-pricing. Im sorry",
//     "xWateringBasedTemperature": "Upgrade Plan To Supreme For Access https://perenual.com/subscription-api-pricing. Im sorry",
//     "xWateringPhLevel": "Upgrade Plan To Supreme For Access https://perenual.com/subscription-api-pricing. Im sorry",
//     "xSunlightDuration": "Upgrade Plan To Supreme For Access https://perenual.com/subscription-api-pricing. Im sorry",
//     "xTemperatureTolence": "Upgrade Plan To Supreme For Access https://perenual.com/subscription-api-pricing. Im sorry",
//     "xPlantSpacingRequirement": "Upgrade Plan To Supreme For Access https://perenual.com/subscription-api-pricing. Im sorry"
// }