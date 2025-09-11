<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlantRequest;
use App\Models\Plant;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class UserPlantController extends Controller
{
    use HttpResponses;

    public function index(Request $request){

        $user = $request->user();

        $userPlants = $user->plants;

        return $this->success($userPlants, $user->name . "'s plants retrieved successfully");

    }
    
    public function store(StorePlantRequest $request){

        $request->validated($request->all());

        $user = $request->user();

        $plant = Plant::create([
            'common_name' => $request->common_name,
            'watering_general_benchmark' => json_encode($request->watering_general_benchmark),
        ]);

        // Attach the plant to the user (many-to-many)
        $user->plants()->attach($plant->id);

        return $this->success($plant, "Plant succesfully created by user " . $user->name, 201);
    }

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
