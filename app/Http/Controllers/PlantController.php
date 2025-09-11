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
        // $plants = json_encode($plants);

        return $this->success($plants);
        
    }

    public function store(StorePlantRequest $request){

        $request->validated($request->all());

        $plant = Plant::create([
            'common_name' => $request->common_name,
            'watering_general_benchmark' => $request->watering_general_benchmark,
        ]);

        return $this->success($plant, "Plant succesfully created");
    }


    public function show(Request $request){

    }
    public function destroy(Request $request){

    }
    
}
