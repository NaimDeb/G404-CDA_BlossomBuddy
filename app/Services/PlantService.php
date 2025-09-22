<?php
namespace App\Services;

use App\Interfaces\PlantServiceInterface;
use App\Models\Plant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Service appelé dans la commande FetchPlants
class PlantService implements PlantServiceInterface
{
    // Params : key (required). Url must be /{id}?key=your_api_key
    protected $apiIDSearchlUrl = 'https://perenual.com/api/v2/species/details';
    // Params : key (required), q (optional), page=1 (required?)
    protected $apiNameSearchUrl = 'https://perenual.com/api/v2/species-list';
    protected $cacheDuration = 86400; // 24 heures en secondes
    protected $maxApiSearchResults = 5; // Limite de résultats pour l'API
    protected $minDbSearchResults = 3; // Nombre minimum de résultats DB avant de stopper la recherche API

    /**
     * Recherche une plante par nom dans la DB, le cache, puis l'API.
     *
     * @param string $name
     * @param int $maxRetries
     * @return array
     */
    public function searchPlantByName(string $name, int $maxRetries = 3): array
    {
        // 1. Recherche d'abord dans la base de données
        $dbResults = Plant::where('common_name', 'LIKE', '%' . $name . '%')
            ->limit($this->maxApiSearchResults)
            ->get()
            ->toArray();

        // Si on a assez de résultats dans la DB, on s'arrête là
        if (count($dbResults) >= $this->minDbSearchResults) {
            return ['source' => 'database', 'results' => $dbResults];
        }

        // 2. Recherche dans le cache
        $cacheKey = "plant_search_" . md5($name);
        if (cache()->has($cacheKey)) {
            $cacheResults = cache()->get($cacheKey);
            return ['source' => 'cache', 'results' => $cacheResults];
        }

        // 3. Recherche via l'API
        $apiKey = env('PLANT_API_KEY');
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = \Illuminate\Support\Facades\Http::withoutVerifying()->get($this->apiNameSearchUrl, [
                    'key' => $apiKey,
                    'q' => $name,
                    'limit' => $this->maxApiSearchResults
                ]);
                if ($response->successful()) {
                    $data = $response->json();
                    cache()->put($cacheKey, $data, now()->addSeconds($this->cacheDuration));
                    return ['source' => 'api', 'results' => $data];
                }
                if ($attempt < $maxRetries) {
                    sleep(2);
                }
            } catch (\Exception $e) {
                if ($attempt === $maxRetries) {
                    throw $e;
                }
                sleep(2);
            }
        }
        return ['source' => 'none', 'results' => []];
    }

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

        $response = Http::withoutVerifying()->get("{$this->apiIDSearchlUrl}/{$id}", [
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

    /**
     * Vérifie si les données d'une plante sont complètes et les complète via l'API si nécessaire
     * @param string $name Nom de la plante
     * @return array|null Données complètes de la plante ou null si non trouvée
     */
    public function checkAndCompleteData(string $name): ?array
    {
        // Chercher d'abord dans la DB
        $plant = Plant::where('common_name', 'LIKE', '%' . $name . '%')->first();
        
        if (!$plant) {
            // Si pas dans la DB, chercher via l'API
            $searchResult = $this->searchPlantByName($name);
            if (empty($searchResult['results']) || empty($searchResult['results']['data'])) {
                return null;
            }

            // Récupérer les données complètes via l'API pour le premier résultat
            $apiId = $searchResult['results']['data'][0]['id'];
            $completeData = $this->getPlantData($apiId);
            if (empty($completeData)) {
                return null;
            }

            // Filtrer et sauvegarder les données
            $filteredData = $this->filterPlantData($completeData);
            $this->storePlantData($filteredData);
            return $filteredData;
        }

        // Vérifier si les données sont complètes
        if ($this->isPlantDataComplete($plant)) {
            return $plant->toArray();
        }

        // Compléter les données manquantes via l'API
        if ($plant->api_id) {
            $completeData = $this->getPlantData($plant->api_id);
            if (!empty($completeData)) {
                $filteredData = $this->filterPlantData($completeData);
                $this->storePlantData($filteredData);
                return $filteredData;
            }
        }

        return $plant->toArray();
    }

    /**
     * Vérifie si les données d'une plante sont complètes
     */
    private function isPlantDataComplete($plant): bool
    {
        $requiredFields = [
            'api_id',
            'common_name',
            'watering_general_benchmark',
            'watering',
            'growth_rate',
            'maintenance'
        ];

        foreach ($requiredFields as $field) {
            if ($plant->$field === null) {
                return false;
            }
        }

        return true;
    }
}