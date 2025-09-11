<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlantRequest;
use App\Models\Plant;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class PlantController extends Controller
{
    use HttpResponses;


    public function index(Request $request){

        $plants = Plant::all();

        return $this->success($plants);
        
    }

    public function store(StorePlantRequest $request){

        $request->validated($request->all());

        $plant = Plant::create([
            'common_name' => $request->common_name,
            'watering_general_benchmark' => json_encode($request->watering_general_benchmark),
        ]);
        return $this->success($plant, "Plant succesfully created", 201);
    }


    public function show($name){

        // Todo : Meilleur algo pour chercher un nom
        $plant = Plant::where('common_name', 'LIKE', '%' . $name . '%')->first();

        if (!$plant) {
            return $this->error(null, 'Plant not found', 404);
        }

        return $this->success($plant);

    }

    public function destroy($id){

        $plant = Plant::find($id);
        if (!$plant) {
            return $this->error(null, 'Plant not found', 404);
        }

        $plant->delete();

        return $this->success(null, 'Plant deleted successfully');

    }
    
}
