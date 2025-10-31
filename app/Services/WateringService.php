<?php

namespace App\Services;

use App\Models\Plant;
use App\Services\Watering\Strategies\DefaultWateringStrategy;
use DateTime;

/**
 * Service qui coordonne le WeatherService et les Watering Strategy
 */
class WateringService {

    public function __construct(
        private WeatherService $weather,
        private DefaultWateringStrategy $strategy
    ){}

    public function calculateNextWatering(Plant $plant, string $city): DateTime
    {
        $weatherData = $this->weather->getCurrentWeatherData($city);

        $hoursUntilNextWatering = $this->strategy->calculateUntilNextWatering($plant, $weatherData);

        return now()->addHours($hoursUntilNextWatering);
    }

}