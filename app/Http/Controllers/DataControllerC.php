<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataControllerC extends Controller
{
    public function getData(Request $request): JsonResponse
    {
        return response()->json(['message'=> 'This is the data from DataControllerC']);
    }
}
