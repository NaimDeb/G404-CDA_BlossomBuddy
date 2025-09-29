<?php

namespace App\Interfaces;

interface WeatherServiceInterface
{
    public function getCurrentWeatherData(string $q);

}
