<?php

namespace App\Http\Controllers;

use App\Services\VehiclesService;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = new VehiclesService()->getVehicles();

        return view('vehicles.index', compact('vehicles'));
    }
}
