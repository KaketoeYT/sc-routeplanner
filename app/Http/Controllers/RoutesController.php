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
        $routes = collect($routeService->getRoutes())
            ->filter(fn ($route) => isset($route['profit'])) //  see if routes exist
            ->sortByDesc('profit') // highest profit first
            ->take(250) // limit showing
            ->values();

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
