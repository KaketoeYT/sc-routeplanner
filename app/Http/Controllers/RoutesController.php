<?php

namespace App\Http\Controllers;

use App\Services\RoutesService;
use App\Services\VehiclesService;
use Illuminate\Http\Request;

class RoutesController extends Controller
{
    public function index(Request $request)
    {
        $routeService = new RoutesService();
        $routes = $routeService->getRoutes();

        $vehicleService = new VehiclesService();
        $vehicles = $vehicleService->getVehicles();

        // Group ships by company
        $vehiclesGrouped = collect($vehicles)->groupBy('company_name');

        return view('routes.index', compact('routes', 'vehiclesGrouped'));
    }

    public function dump()
    {
        $service = new routesService();
        $routes = $service->getRoutes();

        return view('routes.dump', compact('routes'));
    }
}
