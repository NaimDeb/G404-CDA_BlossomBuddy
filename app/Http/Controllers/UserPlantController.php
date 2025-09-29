<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlantRequest;
use App\Models\Plant;
use App\Services\PlantService;
use App\Services\Watering\Strategies\DefaultWateringStrategy;
use App\Services\WeatherService;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class UserPlantController extends Controller
{
    use HttpResponses;

    protected $plantService;
    protected $weatherService;
    protected $wateringStrategy;

    public function __construct()
    {
        $this->plantService = new PlantService();
        $this->weatherService = new WeatherService();
        $this->wateringStrategy = new DefaultWateringStrategy();
    }

    /**
     * @OA\Get(
     *     path="/user/plants",
     *     summary="Get a list of the user's plants",
     *     tags={"Users"},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=400, description="Invalid request"),
     *     @OA\Response(response=403, description="Unauthorized action")
     * )
     */
    public function index(Request $request){

        $user = $request->user();
        $query = $user->plants();

        // Todo : Ajouter un minimum (un max par exemple)

        if ($request->has('plantName')) {
            $results = $query->where("common_name", "LIKE", "%" . $request->plantName . "%" )->first();
        } elseif ($request->has('plantId')) {
            $results = $query->where("common_name",$request->plantId);
        }


        $plants = $query->get()->map(function ($plant) {
            $plant->nextWatering = $this->wateringStrategy->calculateUntilNextWatering(
                $plant,
                $this->weatherService->getCurrentWeatherData($plant->pivot->city) // On récupère city de la table pivot user_plants
            );
            return $plant;
        });

        return $this->success($plants, $user->name . "'s plants retrieved successfully");

    }
    
    /**
     * @OA\Post(
     *     path="/user/plant",
     *     summary="Add a plant to the user's collection",
     *     tags={"Users"},
     *     @OA\Response(response=201, description="Successful operation"),
     *     @OA\Response(response=400, description="Invalid request"),
     *     @OA\Response(response=403, description="Unauthorized action")
     * )
     */
    public function store(StorePlantRequest $request){

        // Renvoie seulement les champs validés et attendus par la requête
        $validated = $request->validated();

        $user = $request->user();

        $plant = $this->plantService->resolvePlantByName($validated["plantName"]);

        if (!$plant) {
            return $this->error(null, 'Plant not found', 404);
        }

        if ($user->plants()->where('plant_id', $plant->id)->exists()) {
            return $this->error(null, "Plant $plant->common_name is already in user's collection", 409);
        }

        // Todo : check if city exists, and take it from WeatherApi
        $city = $this->weatherService->getCityName($validated["city"]);
        if (!$city) return $this->error(null, "City " . $validated["city"] . " wasn't found", 409);

        $weather = $this->weatherService->getCurrentWeatherData($city);

        // Attach the plant to the user (many-to-many)
        $user->plants()->attach($plant->id, ['city' => $validated["city"]]);

        return $this->success([
            'plant' => $plant,
            'city' => $city,
            'weather' => $weather
        ], "Plant {$plant->common_name} added in {$city}", 201);
    }

    /**
     * @OA\Delete(
     *     path="/user/plant/{id}",
     *     summary="Deletes a user's plant",
     *     tags={"Users"},
     *     @OA\Response(response=201, description="Successful operation"),
     *     @OA\Response(response=400, description="Invalid request"),
     *     @OA\Response(response=404, description="Plant not found"),
     *     @OA\Response(response=403, description="Unauthorized action")
     * )
     */
    public function destroy($id, Request $request){

        $user = $request->user();
        $plant = $user->plants()->find($id);
        if (!$plant) {
            return $this->error(null, 'Plant not found', 404);
        }
        $user->plants()->detach($plant->id);
        $plant->delete();
        return $this->success(null, 'Plant deleted successfully', 201);

    }
}
