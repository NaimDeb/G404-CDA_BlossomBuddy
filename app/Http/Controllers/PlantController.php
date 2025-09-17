<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlantRequest;
use App\Models\Plant;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class PlantController extends Controller
{
    use HttpResponses;


    /**
     * @OA\Get(
     *     path="/plants",
     *     summary="Get a list of all plants",
     *     tags={"Plants"},
     *     @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function index(Request $request){

        $plants = Plant::all();

        return $this->success($plants);
        
    }

    /**
     * @OA\Post(
     *     path="/plants",
     *     summary="Create a new plant",
     *     tags={"Plants"},
     *     @OA\Response(response=201, description="Plant successfully created"),
     *     @OA\Response(response=400, description="Invalid request")
     * )
     */
    public function store(StorePlantRequest $request){

        $request->validated($request->all());

        $plant = Plant::create([
            'common_name' => $request->common_name,
            'watering_general_benchmark' => json_encode($request->watering_general_benchmark),
        ]);
        return $this->success($plant, "Plant succesfully created", 201);
    }


    /**
     * @OA\Get(
     *     path="/plants/{name}",
     *     summary="Get a plant by name",
     *     tags={"Plants"},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="Plant not found")
     * )
     */
    public function show($name){

        // Todo : Meilleur algo pour chercher un nom
        $plant = Plant::where('common_name', 'LIKE', '%' . $name . '%')->first();

        if (!$plant) {
            return $this->error(null, 'Plant not found', 404);
        }

        return $this->success($plant);

    }

    /**
     * @OA\Delete(
     *     path="/plants/{id}",
     *     summary="Delete a plant",
     *     tags={"Plants"},
     *     @OA\Response(response=200, description="Plant deleted successfully"),
     *     @OA\Response(response=404, description="Plant not found")
     * )
     */
    public function destroy($id){

        $plant = Plant::find($id);
        if (!$plant) {
            return $this->error(null, 'Plant not found', 404);
        }

        $plant->delete();

        return $this->success(null, 'Plant deleted successfully');

    }
    
}
