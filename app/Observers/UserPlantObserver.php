<?php

namespace App\Observers;

use App\Models\UserPlant;
use App\Services\WateringService;

class UserPlantObserver
{
    /**
     * Handle the UserPlant "created" event.
     */
    public function created(UserPlant $userPlant): void
    {
        $plant = $userPlant->plant;
        $city = $userPlant->city;
        // app() au lieu de new pour rendre le code SOLID. Si j'ai a faire des tests unitaires, et que je veux changer de service, j'ai juste a faire $this->app->bind(WeatherService::class, FakeWeatherService::class); au lieu d'overload
        $weatherService = app(WateringService::class);
        $nextWateringAt = $weatherService->calculateNextWatering($plant, $city);

        $userPlant->update(['next_watering_at' => $nextWateringAt]);
    }

    /**
     * Handle the UserPlant "updated" event.
     */
    public function updated(UserPlant $userPlant): void
    {
        //
    }

    /**
     * Handle the UserPlant "deleted" event.
     */
    public function deleted(UserPlant $userPlant): void
    {
        //
    }

    /**
     * Handle the UserPlant "restored" event.
     */
    public function restored(UserPlant $userPlant): void
    {
        //
    }

    /**
     * Handle the UserPlant "force deleted" event.
     */
    public function forceDeleted(UserPlant $userPlant): void
    {
        //
    }
}
