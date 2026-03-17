<?php

namespace App\Http\Controllers;

use App\Services\RoutesService;
use App\Services\VehiclesService;
use Illuminate\Http\Request;

class RoutesController extends Controller
{
    public function index(Request $request)
    {
        $selectedScu = (int) $request->get('ship_scu', 0);

        $routeService = new RoutesService();
        $routes = collect($routeService->getRoutes());

        $routes = $routes->map(function ($route) use ($selectedScu) {

            $routeScuOrigin = $route['scu_origin'] ?? 0;
            $routeScuDest   = $route['scu_destination'] ?? 0;

            $shipScu = $selectedScu > 0 ? $selectedScu : max($routeScuOrigin, $routeScuDest);

            $usedScuOrigin = min($shipScu, $routeScuOrigin);
            $usedScuDest   = min($shipScu, $routeScuDest);

            $achievableScu = min($usedScuOrigin, $usedScuDest);

            $buy  = $route['price_origin'] * $achievableScu;
            $sell = $route['price_destination'] * $achievableScu;

            $route['used_scu'] = $achievableScu;
            $route['buy_total'] = $buy;
            $route['sell_total'] = $sell;
            $route['profit'] = $sell - $buy;

            return $route;
        })
        ->filter(fn ($r) => $r['profit'] > 0)
        ->sortByDesc('profit')
        ->take(50)
        ->values();

        $vehicleService = new VehiclesService();
        $vehicles = $vehicleService->getVehicles();
        $vehiclesGrouped = collect($vehicles)->groupBy('company_name');

        return view('routes.index', compact('routes', 'vehiclesGrouped', 'selectedScu'));
    }

    public function dump()
    {
        $service = new routesService();
        $routes = $service->getRoutes();

        return view('routes.dump', compact('routes'));
    }
}
