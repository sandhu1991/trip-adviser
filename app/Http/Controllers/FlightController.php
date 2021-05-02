<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\response;

class FlightController extends Controller
{

    private $connections;
    private $stops;

    /**
        * @OA\Get(
        * path="/api/trips",
        *  summary="Get Trips",
        *  @OA\Parameter(name="from",
        *    in="query",
        *    description="Depature Airport Code",
        *    @OA\Schema(type="string")
        *  ),
        *  @OA\Parameter(name="departDate",
        *    in="query",
        *    description="Depature Date",
        *    @OA\Schema(type="string")
        *  ),
        *  @OA\Parameter(name="to",
        *    in="query",
        *    description="Arrival Airport Code",
        *    @OA\Schema(type="string")
        *  ),
         *  @OA\Parameter(name="returnDate",
        *    in="query",
        *    description="Return Date",
        *    @OA\Schema(type="string")
        *  ),
        *  @OA\Parameter(name="oneway",
        *    in="query",
        *    description="Flight Type",
        *    @OA\Schema(type="string", default=false)
        *  ),
        *  @OA\Parameter(name="airline",
        *    in="query",
        *    description="Airline Code",
        *    @OA\Schema(type="string")
        *  ),
        *  @OA\Response(response="200",
        *    description="Validation Response",
        *  )
        * )
    */
    public function getTrips(request $request)
    {
        $data = $request->all();

        $from = $data['from'] ?? false;
        $to = $data['to'] ?? false;
        $departDate = $data['departDate'] ?? false;
        $returnDate = $data['returnDate'] ?? false;
        $stops = $data['stops'] ?? 0;
        $oneway = (isset($data['oneway']) && $data['oneway'] == 'true') ?  true : false;
        $airline = isset($data['airline']) ?  $data['airline'] : false;

        //add form validation
        if(!$from || !$to || !$departDate || !$returnDate){
            return 'Missing required input fields';
        }

        $results = json_decode(file_get_contents(base_path('resources/sampleData.json')));
        $flights = $results->flights;

        // filter by airline
        if($airline){
            $flights = array_filter($flights,function($item) use($airline){
                return $item->airline == strtoupper($airline);
            });
        }

        if(count($flights) == 0){
            return 'NO trips avaiable for this search';
        }

        // get the trips
        $response = $this->getFlights($flights, $to, $from, $departDate, $returnDate, $oneway, $stops);

        return response()->json(['code'=>200, 'status'=> 'Sucess', 'data'=>$response]);
    }
   
    /**
        * @OA\Get(
        * path="/api/countires",
        *  summary="Get All Countries",
        *  @OA\Parameter(name="code",
        *    in="query",
        *    description="Airline Code",
        *    @OA\Schema(type="string")
        *  ),
        *  @OA\Response(response="200",
        *    description="Validation Response",
        *  )
        * )
    */
    public function getCountires(request $request)
    {

        $data = $request->all();
        $code = isset($data['code']) ?  $data['code'] : false;

        $results = json_decode(file_get_contents(base_path('resources/sampleData.json')));
        $data = $results->airlines;

        if($code){
            $data = array_filter($data,function($item) use($code){
                return $item->code == $code;
            });
        }

        return response()->json($data);
    }

    /**
        * @OA\Get(
        * path="/api/airports",
        *  summary="Get All Airports",
        *  @OA\Parameter(name="code",
        *    in="query",
        *    description="Arrival Airport Code",
        *    @OA\Schema(type="string")
        *  ),
        *  @OA\Response(response="200",
        *    description="Validation Response",
        *  )
        * )
    */
    public function getAirports(request $request)
    {
        $data = $request->all();
        $code = isset($data['code']) ?  $data['code'] : false;

        $results = json_decode(file_get_contents(base_path('resources/sampleData.json')));
        $data = $results->airports;

        if($code){
            $data = array_filter($data,function($item) use($code){
                return $item->code == $code;
            });
        }

        return response()->json($data);
    }


    private function getFlights($flights, $to, $from, $departDate, $returnDate, $oneway = false, $stops = 0)
    {
        $data = []; 

        $depatureTrips=[];

        $landingFlights = array_filter($flights,function($item) use($to, $from){
            return strtoupper($item->arrival_airport) == strtoupper($to);
        });


        foreach ($landingFlights as $key => $flight) {

            if(strtoupper($flight->departure_airport) == strtoupper($from) && strtoupper($flight->arrival_airport) == strtoupper($to)){

                $depatureTrips[] = $flight;
            }else{

                $this->connections = [];

                $response = $this->getConnections($flights, $from, $flight, $stops);
                
                if(count($this->connections) > 0){

                    $depatureTrips[] = array_reverse($this->connections);
                }

                
            }
        }

        // return if it is oneway trip
        if($oneway){
            foreach($depatureTrips as $depature){
            
                if(is_array($depature)){
                    $stops = count($depature)-1;
                    $fullPrice = 0;
                    foreach ($depature as $depart) {
                        $fullPrice += $depart->price;
                    }
                }else{
                    $stops = 0;
                    $fullPrice = $depature->price;
                }

                $trip = new \stdClass();
                $trip->departDate = $departDate;
                $trip->departFlight = $depature;
                $trip->returnDate = null;
                $trip->returnFlight = null;
                $trip->fullPrice = number_format($fullPrice, 2, '.', '');
                $trip->stops = $stops;
                $trip->oneway = true;
                $data[] = $trip; 
            }
            return $data;
        }

        $returnTrips = array_filter($flights,function($item) use($to, $from){
            return strtoupper($item->departure_airport) == strtoupper($to) && strtoupper($item->arrival_airport) == strtoupper($from);
        });

        // var_dump("returnTrips",$returnTrips);die();

        foreach($depatureTrips as $depature){
            foreach($returnTrips as $return){

                if(is_array($depature)){
                    $stops = count($depature)-1;
                    $fullPrice = 0;
                    foreach ($depature as $depart) {
                        $fullPrice += $depart->price;
                    }
                }else{
                    $stops = 0;
                    $fullPrice = $depature->price;
                }

                $trip = new \stdClass();
                $trip->departDate = $departDate;
                $trip->departFlight = $depature;
                $trip->returnDate = $returnDate;
                $trip->returnFlight = $return;
                $trip->fullPrice = $fullPrice + $return->price;
                $trip->oneway = false;
                $trip->stops = $stops;
                $data[] = $trip;
            }

        }
        return $data;
    }

    public function getConnections($flights, $from, $flight, $stops)
    {
        $this->connections[] = $flight;

        $landingFlights = array_filter($flights,function($item) use($flight, $from){
            return strtoupper($item->arrival_airport) == strtoupper($flight->departure_airport);
        });

        if(!$landingFlights){
            $this->connections= []; 
        }
        
        foreach ($landingFlights as $key => $value) {

            if(strtoupper($value->departure_airport) == strtoupper($from)){

                $this->connections[] = $value;
                return;
            }else{
                $this->getConnections($flights, $from, $value, $stops );
            }
        }
        return;
    }

}