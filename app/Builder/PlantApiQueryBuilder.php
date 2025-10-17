<?php

namespace App\Builder;

use Exception;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class PlantApiQueryBuilder extends AbstractApiQueryBuilder
{
    
    protected const API_URL = 'https://perenual.com/api/v2/';
    protected const API_KEY_CONFIG_PATH = 'apiKeys.plant_api_key';
    protected const API_KEY_NAME = 'key';
    
}
