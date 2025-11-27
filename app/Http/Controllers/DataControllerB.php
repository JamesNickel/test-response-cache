<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataControllerB extends Controller
{
    public function getData(Request $request): JsonResponse
    {
        try{
            $value = random_int(100000, 999999);
            return response()->json(['message'=> 'This is the data from DataControllerB', 'value'=> $value]);
        }catch(\Exception $exception){
            return response()->json(['message' => $exception->getMessage()], 500);
        }
    }
}
