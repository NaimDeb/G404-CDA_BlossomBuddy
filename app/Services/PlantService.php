<?php

namespace App\Services;

use App\Builder\PlantApiQueryBuilder;
use App\DTO\PlantDto;
use App\Exceptions\ApiFailedException;
use App\Interfaces\PlantServiceInterface;
use App\Mappers\PlantMapper;
use App\Models\Plant;
use Illuminate\Support\Facades\Log;

// Service appelé dans la commande FetchPlants
class PlantService implements PlantServiceInterface
{
    // Params : key (required). Url must be /{id}?key=your_api_key
    protected $cacheDuration = 86400; // 24 heures en secondes
    protected $maxApiSearchResults = 5; // Limite de résultats pour l'API
    protected $minDbSearchResults = 3; // Nombre minimum de résultats DB avant de stopper la recherche API

    protected $queryBuilder;

    public function __construct()
    {
        $this->queryBuilder = new PlantApiQueryBuilder;
    }

    /**
     * Recherche une plante par nom dans la DB, le cache, puis l'API.
     *
     * @param string $name
     * @param int $maxRetries
     * @return array
     */
    public function searchPlantByName(string $name): array
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

        // 2. Recherche dans le cache, si c'est déja dans le cache, on renvoie le résultat
        $cacheKey = "plant_search_" . md5($name);
        if (cache()->has($cacheKey)) {
            $cacheResults = cache()->get($cacheKey);
            return ['source' => 'cache', 'results' => $cacheResults];
        }

        // 3. Sinon, Récupération de la plante via l'API
        try {
            $response = $this->queryBuilder->endpoint('species-list')->addParam('q', $name)->addParam('limit', $this->maxApiSearchResults)->get();
        } catch (ApiFailedException $e) {
            Log::error("Failed to fetch plant with name $name " . $response);
        }

        $dtos = [];
        foreach ($response as $row) {
            $dto = PlantMapper::fromSearchApi($row);
            $dtos[] = $dto;
        }


        // Résultat de l'API mis en cache
        cache()->put($cacheKey,
            array_map(fn($dto) => $dto->toArray(), $dtos),
            now()->addSeconds($this->cacheDuration)
        );
        return ['source' => 'api', 'results' => $dtos];
    }

    /**
     * Récupère les données d'une plante depuis le cache ou l'API via l'ID
     * @param int $id : Id de la plante côté API (Perenual) ou dans le cache
     */
    private function getPlantData(int $id): ?PlantDto
    {
        $cacheKey = "plant_data_{$id}";

        // Vérifier si les données sont en cache
        if (cache()->has($cacheKey)) {
            Log::info("✓ Retrieved plant {$id} from CACHE");
            return PlantDto::fromArray(cache()->get($cacheKey));
        }

        // Sinon, faire l'appel API
        Log::info("... Retrieving plant {$id} from API");
        $rawPlantData = $this->fetchPlantData($id);
        if (empty($rawPlantData)) {
            return null;
        }

        // Normalisation de la donnée dans un DTO
        $dto = PlantMapper::fromDetailsApi($rawPlantData);

        // Mettre en cache pour 24 heures
        cache()->put($cacheKey, $dto->toArray(), now()->addSeconds($this->cacheDuration));
        Log::info("Stored plant {$id} in cache");

        return $dto;
    }

    /**
     * Récupère les données complètes d'une plante depuis l'endpoint details de l'API
     * @param int $id : Identifiant de la plante côté API
     * @return array
     */
    private function fetchPlantData(int $id): array
    {
        try {
            $response = $this->queryBuilder->endpoint("details/$id")->get();
        } catch (ApiFailedException $e) {
            Log::error("Failed to fetch plant with ID {$id}: " . $e->getMessage());
            return [];
        }

        return $response;
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
     * @return ?Plant Données complètes de la plante ou null si non trouvée
     */
    public function checkAndCompleteData(Plant $plant): ?Plant
    {
        // Vérification si les données sont déja complètes
        $isComplete = $this->isPlantDataComplete($plant);
        if ($isComplete) {
            return $plant;
        }

        if (!$plant->api_id) {
            // Todo : Je sais pas trop faire quelque chose ici 
            // searchPlantByName avec $plant->common_name
            // Et après jle fill mais vsy flm
            // ALI AIDE MOI
            $searchResult = $this->searchPlantByName($plant->common_name);
            if (empty($searchResult['results']) || empty($searchResult['results']['data'])) {
                return null;
            }
        }
        // On récupère les données via API
        $plantDetails = $this->getPlantData($plant->api_id); // DTO
        // On le met dans l'instance Plant, et on sauvegarde
        $plant->fill($plantDetails->toArray())->save();

        return $plant;
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
    /**
     * Récupère une plante
     * - En cherchant premièrement dans la Database
     * - Puis le cache
     * - Puis dans l'API Perenual
     * @param string $plantName Nom de la plante
     * @return Plant|null
     */
    public function resolvePlantByName(string $plantName): ?Plant
    {
        // 1. Récupération de plante via la Base de donnée.
        $plant = Plant::where('common_name', 'LIKE', "%" . $plantName . "%")->first();
        if ($plant) {
            return $this->checkAndCompleteData($plant);
        }

        // Sinon on cherche dans l'API

        $searchResult = $this->searchPlantByName($plantName);
        $firstDto = $searchResult['results'][0] ?? null;

        return $this->checkAndCompleteData($firstDto);
    }
}
