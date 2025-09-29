<?php

namespace App\Services;


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseApiService
{
    protected string $apiKey;


    /**
     * Fais un call api, 
     */
    protected function callApi(string $url, array $params = [], int $cacheSeconds = 0): array
    {
        $params['key'] = $this->apiKey;

        $cacheKey = null;
        if ($cacheSeconds > 0) {
            $cacheKey = 'api_cache_' . md5($url . json_encode($params));
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }
        }

        try {
            $response = Http::withoutVerifying()->get($url, $params);

            if ($response->successful()) {
                $data = $response->json();
                if ($cacheKey && $cacheSeconds > 0) {
                    Cache::put($cacheKey, $data, $cacheSeconds);
                }
                return $data;
            }

            Log::error("API call failed at {$url} with params " . json_encode($params) . ": " . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error("Exception during API call at {$url} with params " . json_encode($params) . ": " . $e->getMessage());
            return [];
        }
    }

}
