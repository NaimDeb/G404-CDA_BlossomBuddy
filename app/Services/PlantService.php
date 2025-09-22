<?php

namespace App\Services;

use App\Interfaces\PlantServiceInterface;
use App\Models\Plant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Service appelé dans la commande FetchPlants
class PlantService implements PlantServiceInterface
{
    protected $apiUrl = 'https://perenual.com/api/v2/species/details';
    protected $cacheDuration = 86400; // 24 heures en secondes

    public function fetchAndStorePlants(): void
    {
        $processedCount = 0;
        $batchSize = 10; // Traiter 10 plantes à la fois
        $maxRetries = 3;
        $cacheHits = 0;
        $apiHits = 0;

        Log::info("Starting plant data fetch process...");

        for ($id = 200; $id <= 203; $id++) {
            try {
                // Vérifier le taux limite toutes les 10 requêtes
                if ($processedCount > 0 && $processedCount % $batchSize === 0) {
                    sleep(2); // Pause de 2 secondes entre les lots
                    Log::info("Batch complete, taking a short break...");
                }

                Log::info("Processing plant ID: {$id}");
                $plantData = $this->getPlantData($id, $maxRetries, $cacheHits, $apiHits);

                if ($plantData && !empty($plantData)) {
                    $plantDataFiltered = $this->filterPlantData($plantData);
                    $this->storePlantData($plantDataFiltered);
                    $processedCount++;
                    
                    // Log de progression
                    Log::info("Processed plant {$id} ({$processedCount} total)");
                }
            } catch (\Exception $e) {
                Log::error("Failed to process plant {$id}: " . $e->getMessage());
                continue;
            }
        }
    }

    /**
     * Récupère les données d'une plante depuis le cache ou l'API
     */
    private function getPlantData(int $id, int $maxRetries = 3, &$cacheHits = 0, &$apiHits = 0): array
    {
        $cacheKey = "plant_data_{$id}";

        // Vérifier si les données sont en cache
        if (cache()->has($cacheKey)) {
            $cacheHits++;
            Log::info("✓ Retrieved plant {$id} from CACHE (Cache hits: {$cacheHits})");
            return cache()->get($cacheKey);
        }

        $apiHits++;
        Log::info("→ Fetching plant {$id} from API (API calls: {$apiHits})");

        // Sinon, faire l'appel API avec retry
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $plantData = $this->fetchPlantData($id);

                if (!empty($plantData)) {
                    // Mettre en cache pour 24 heures
                    cache()->put($cacheKey, $plantData, now()->addSeconds($this->cacheDuration));
                    Log::info("Stored plant {$id} in cache");
                    return $plantData;
                }

                if ($attempt < $maxRetries) {
                    sleep(2); // Attendre 2 secondes avant de réessayer
                }
            } catch (\Exception $e) {
                Log::warning("Attempt {$attempt} failed for plant {$id}: " . $e->getMessage());
                
                if ($attempt === $maxRetries) {
                    throw $e;
                }
                
                sleep(2);
            }
        }

        return [];
    }

    /**
     * Fetches plant data from the Perenual API.
     * @param int $id
     * @return array
     */
    private function fetchPlantData(int $id): array
    {
        $apiKey = env('PLANT_API_KEY');

        $response = Http::withoutVerifying()->get("{$this->apiUrl}/{$id}", [
            'key' => $apiKey
        ]);

        if ($response->successful()) {
            return $response->json();
        } else {
            Log::error("Failed to fetch plant with ID {$id}: " . $response->body());
            return [];
        }
    }

    /**
     * Filters plant data to keep only the relevant fields.
     * @param array $plantData
     * @return array
     */
    private function filterPlantData(array $plantData): array
    {
        return [
            'api_id' => $plantData['id'],
            'common_name' => $plantData['common_name'],
            'watering_general_benchmark' => $plantData['watering_general_benchmark'],
            'watering' => $plantData['watering'] ?? null,
            'flowers' => (bool)($plantData['flowers'] ?? false),
            'fruits' => (bool)($plantData['fruits'] ?? false),
            'leaf' => (bool)($plantData['leaf'] ?? false),
            'growth_rate' => $plantData['growth_rate'] ?? null,
            'maintenance' => $plantData['maintenance'] ?? null,
        ];
    }

   
    private function storePlantData(array $plantData): void
    {
        // Utilisation de upsert pour éviter les doublons basés sur api_id
        Plant::updateOrCreate(
            ['api_id' => $plantData['api_id']],
            $plantData
        );
    }
}