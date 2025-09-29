<?php

namespace App\Services;

use App\Interfaces\WeatherServiceInterface;
use App\Models\Plant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Service appelé dans la commande FetchPlants
class WeatherService extends BaseApiService implements WeatherServiceInterface 
{
    // Params : key (required). q (required)
    protected $apiSearchUrl = 'http://api.weatherapi.com/v1/search.json';
    // Params : key (required), q (optional)
    protected $apiCurrentUrl = 'http://api.weatherapi.com/v1/current.json';
    protected $cacheDuration = 2 * 60 * 60; // 2 heures en secondes

    public function __construct()
    {
        $this->apiKey = env('WEATHER_API_KEY');
    }


    public function getCurrentWeatherData(string $q) {

        $city = $this->getCityName($q);
        $cacheKey = "current_weather_data_" . md5(strtolower($city));

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($city) {

            $results = $this->callApi($this->apiCurrentUrl, ["q" => $city]);

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
        // ! Pas super vu que si l'utilisateur met un nom non complet (ex: :Londo), ça va créer un nouveau cache.
        $cacheKey = "city_search_" . md5(strtolower($city));

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($city) {
             $results = $this->callApi($this->apiSearchUrl, ["q" => $city]);

            if (empty($results)) {
                Log::warning("City search returned empty results for '{$city}'");
                return null;
            }

            // S'assurer que le champ 'name' existe
            return $results[0]['name'] ?? null;
        });
    }


}
