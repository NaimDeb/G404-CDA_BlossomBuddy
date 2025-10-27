<?php

namespace App\Services;

use App\Builder\WeatherApiQueryBuilder;
use App\Interfaces\WeatherServiceInterface;
use App\Models\Plant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Service appelé dans la commande FetchPlants
class WeatherService extends BaseApiService implements WeatherServiceInterface 
{
    protected $cacheDuration = 2 * 60 * 60; // 2 heures en secondes

    protected $queryBuilder;

    public function __construct()
    {
        $queryBuilder = new WeatherApiQueryBuilder();
    }


    public function getCurrentWeatherData(string $q) {

        $city = $this->getCityName($q);
        $cacheKey = "current_weather_data_" . md5(strtolower($city));

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($city) {

            $results = $this->queryBuilder
                ->endpoint("current.json")
                ->addParam("q", $city)
                ->get();

            return $results;
        });
    }



    /**
     * Récupère le nom d'une ville depuis le cache ou l'API (autocomplete)
     * Utilise le cache pour limiter les appels API
     *
     * @param string $city
     * @return string|null
     */
    public function getCityName(string $city)
    {
        $cacheKey = "city_search_" . md5(strtolower($city));

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $results = $this->queryBuilder
                ->endpoint("search.json")
                ->addParam("q", $city)
                ->get();

        if (empty($results) || !isset($results[0]['name'])) {
            return null;
        }

        $canonicalName = $results[0]['name'];

        Cache::put($cacheKey, $canonicalName, $this->cacheDuration);

        return $canonicalName;
    }


}
