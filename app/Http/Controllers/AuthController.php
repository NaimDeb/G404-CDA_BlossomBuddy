<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use HttpResponses;

    
    public function register(Request $request)
    {

        return $this->success(["Hello"]);


    }

}
