<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    public function getCountries()
    {
        $countries = DB::select('SELECT * FROM bird_countries');
        return response()->json($countries);
    }

    public function getStatesByCountry($countryId)
    {
        $states = DB::select('SELECT * FROM bird_states WHERE countryId = ?', [$countryId]);
        // print_r($states);die;
        return response()->json($states);
    }

    public function getCitiesByState($stateId)
    {
        $cities = DB::select('SELECT * FROM bird_cities WHERE state_id = ?', [$stateId]);
        return response()->json($cities);
    }
}
