<?php

namespace App\Services\Watering\Strategies;

use App\Models\Plant;
use App\Services\Watering\Contracts\WateringStrategyInterface;
use Illuminate\Support\Facades\Log;

class DefaultWateringStrategy implements WateringStrategyInterface {


    /**
     * Calcule le nombres d'heures jusqu'au prochain watering
     */
    public function calculateUntilNextWatering(Plant $plant, array $weatherData){

        $wateringBenchmark = $plant->watering_general_benchmark;
        $unit = $wateringBenchmark['unit'] ?? null;
        $value = trim($wateringBenchmark['value'] ?? '', '"');
        $range = explode('-', $value);

        $hoursUntilNextWatering =  match($unit) {
            'days' => (int) $range[0] * 24,
            'week' => (int) $range[0] * 7 * 24,
            default => (int) $range[0],
        };

        // For each day in the forecast, calculate a coefficient and apply it to the days until next watering
        // foreach ($weatherData as $day) {
            $humidity = $weatherData['current']['humidity'];

            // For each 10% above 70%, we add 10% to daysUntilNextWatering
            if ($humidity > 70) {
                $tranchesAbove70 = floor(($humidity - 70) / 10) + 1;
                $hoursUntilNextWatering += $hoursUntilNextWatering * (0.1 * $tranchesAbove70);
            }
            // For each 10% under 40%, we remove 10% to daysUntilNextWatering
            elseif ($humidity < 40) {
                $tranchesBelow40 = floor((40 - $humidity) / 10) - 1;
                $hoursUntilNextWatering -= $hoursUntilNextWatering * (0.1 * $tranchesBelow40);
            }
        // }

        return $hoursUntilNextWatering;
    }

}