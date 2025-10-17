<?php

namespace App\Builder;

use Exception;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class PlantApiQueryBuilder extends AbstractApiQueryBuilder
{

    public function __construct()
    {
        parent::__construct(
            apiUrl: 'https://perenual.com/api/v2/',
            apiKey: env('PLANT_API_KEY'),
            apiKeyName: 'key'
        );
    }
    
}
