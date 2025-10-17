<?php

namespace App\Builder;

use Exception;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

abstract class AbstractApiQueryBuilder{

    // Placeholders des constantes pour pas qu'intelephense bug
    protected const API_URL = '';
    protected const API_KEY_CONFIG_PATH = '';
    protected const API_KEY_NAME = 'key';

    protected string $apiUrl;
    protected string $apiKey;
    protected string $apiKeyName;

    protected ?string $endpoint = null;
    protected array $params = [];

    public function __construct()
    {
        // Late static building !!! ^^ ça rend les enfants plus propres
        $this->apiUrl = static::API_URL;
        $this->apiKey = config(static::API_KEY_CONFIG_PATH) ?? null;
        $this->apiKeyName = static::API_KEY_NAME ?? 'key';
    }

   /**
     * Adds the endpoint you should call to (without the start of the URL)
     * Example : ->endpoint("users/add")
     */
    public function endpoint(string $endpoint): self
    {
        $this->endpoint = $this->apiUrl . ltrim($endpoint, '/');
        return $this;
    }

    /**
     * Adds one param to the body of the request
     */
    public function addParam(string $key, $value): self
    {
        $this->params[$key] = $value;

        return $this;
    }

    /**
     * Adds the environment file's api key
     */
    protected function addApiKey(): self
    {
        $this->params[$this->apiKeyName] = $this->apiKey;
        return $this;
    }

    /**
     * Builds the full URL to call
     */
    public function get(): array
    {
        if (!$this->verifyIfEndpointComplete()) throw new InvalidArgumentException("You didn't submit any endpoint");

        // We add the apiKey
        if ($this->apiKey) $this->addApiKey();

        // Call API
        try {
            $response = Http::withoutVerifying()->get($this->endpoint, $this->params);

            if (!$response->successful()) throw new Exception("The API response was unsuccessful : " . $response->body());

        } catch (Exception $e) {
            throw $e;
        }

        // Transforms the data into json, should I put it somewhere else ? 
        $data = $response->json();
        return $data;
    }



    /**
     * Verifies if the Builder has an endpoint to prevent calling the API for nothing
     */
    private function verifyIfEndpointComplete()
    {
        return isset($this->endpoint) && !empty($this->endpoint);
    }
}