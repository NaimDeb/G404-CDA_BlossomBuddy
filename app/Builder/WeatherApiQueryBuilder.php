<?php

namespace App\Builder;

use Exception;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class WeatherApiQueryBuilder extends AbstractApiQueryBuilder
{
    
    protected const API_URL = 'http://api.weatherapi.com/v1/';
    protected const API_KEY_CONFIG_PATH = 'apiKeys.weather_api_key';
    protected const API_KEY_NAME = 'key';
    
}
