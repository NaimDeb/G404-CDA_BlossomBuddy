<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlantRequest;
use App\Models\Plant;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class UserPlantController extends Controller
{
    use HttpResponses;

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

        $userPlants = $user->plants;

        return $this->success($userPlants, $user->name . "'s plants retrieved successfully");

    }
    
    /**
     * @OA\Post(
     *     path="/user/plant",
     *     summary="Create a plant in the database and attach it to the user",
     *     tags={"Users"},
     *     @OA\Response(response=201, description="Successful operation"),
     *     @OA\Response(response=400, description="Invalid request"),
     *     @OA\Response(response=403, description="Unauthorized action")
     * )
     */
    public function store(StorePlantRequest $request){

        $request->validated($request->all());

        $user = $request->user();

        $plant = Plant::create([
            'common_name' => $request->common_name,
            'watering_general_benchmark' =>  ($request->watering_general_benchmark),
        ]);

        // Attach the plant to the user (many-to-many)
        $user->plants()->attach($plant->id);

        return $this->success($plant, "Plant succesfully created by user " . $user->name, 201);
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
