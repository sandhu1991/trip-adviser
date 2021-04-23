<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FlightController extends Controller
{
    
    public function getTrips()
    {
        $data = json_decode(file_get_contents(base_path('resources/sampleData.json')));
        return response()->json($data);
    }

    public function getCountires()
    {
        return response()->json('getCountires');
    }

    public function getAirports()
    {
        return response()->json('getAirports');
    }

}