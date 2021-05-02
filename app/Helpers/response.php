<?php

namespace App\Helpers;

class response 
{
    public $trips;
    public $code;
    public $status;

    public function set_trips($trips){

        $this->trips = $trips;
    }
    public function get_trips(){
       
        return $this->trips;
    }

    public function set_code($code){

        $this->code = $code;
    }
    public function get_code(){
       
        return $this->code;
    }

    public function set_status($status){

        $this->status = $status;
    }
    public function get_status(){
       
        return $this->status;
    }

    public function jsonSearlize(){

        $array = [
            'status' => $this->get_status(),
            'trips' => $this->get_trips(),
            'code' => $this->get_code()
        ];
        return $array;
    }


}
