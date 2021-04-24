<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FlightController extends Controller
{
    /**
        * @OA\Get(
        * path="/api/trips",
        *  summary="Get All Trips",
        *  @OA\Parameter(name="email",
        *    in="query",
        *    required=true,
        *    @OA\Schema(type="string")
        *  ),
        *  @OA\Response(response="200",
        *    description="Validation Response",
        *  )
        * )
    */
    public function getTrips()
    {
        $data = json_decode(file_get_contents(base_path('resources/sampleData.json')));
        return response()->json($data);
    }

    /**
        * @OA\Get(
        * path="/api/countires",
        *  summary="Get All Countries",
        *  @OA\Parameter(name="email",
        *    in="query",
        *    required=true,
        *    @OA\Schema(type="string")
        *  ),
        *  @OA\Response(response="200",
        *    description="Validation Response",
        *  )
        * )
    */
    public function getCountires()
    {
        return response()->json('getCountires');
    }

    /**
        * @OA\Post(
        * path="/api/airports",
        *  summary="Get All Airports",
        *  @OA\Parameter(name="email",
        *    in="query",
        *    required=true,
        *    @OA\Schema(type="string")
        *  ),
        *  @OA\Response(response="200",
        *    description="Validation Response",
        *  )
        * )
    */
    public function getAirports()
    {
        return response()->json('getAirports');
    }

}