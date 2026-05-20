<?php

namespace App\Http\Controllers;

use App\Models\Commodity;
use App\Models\Location;
use Illuminate\Support\Facades\Cache;
use App\Models\Route;
use App\Services\RoutesService;
use App\Services\VehiclesService;
use Illuminate\Http\Request;

class RoutesController extends Controller
{
    public function index(Request $request)
    {
        $selectedScu = (int) $request->get('ship_scu', 0);

        $selectedOrigin = $request->get('origin');
        $selectedDestination = $request->get('destination');

        /*
        |--------------------------------------------------------------------------
        | Build Route Query
        |--------------------------------------------------------------------------
        */

        $query = Route::with(['commodity', 'origin', 'destination']);

        /*
        |--------------------------------------------------------------------------
        | Origin Filter (system / planet / terminal)
        |--------------------------------------------------------------------------
        */

        if ($selectedOrigin && str_contains($selectedOrigin, '|')) {

            [$type, $value] = explode('|', $selectedOrigin, 2);

            $query->whereHas('origin', function ($q) use ($type, $value) {

                switch ($type) {

                    case 'system':
                        $q->where('star_system_name', $value);
                        break;

                    case 'planet':
                        $q->where('planet_name', $value);
                        break;

                    case 'terminal':
                        $q->where('terminal_name', $value);
                        break;
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Destination Filter
        |--------------------------------------------------------------------------
        */

        if ($selectedDestination && str_contains($selectedDestination, '|')) {

            [$type, $value] = explode('|', $selectedDestination, 2);

            $query->whereHas('destination', function ($q) use ($type, $value) {

                switch ($type) {

                    case 'system':
                        $q->where('star_system_name', $value);
                        break;

                    case 'planet':
                        $q->where('planet_name', $value);
                        break;

                    case 'terminal':
                        $q->where('terminal_name', $value);
                        break;
                }
            });
        }

        $routes = $query->get();

        /*
        |--------------------------------------------------------------------------
        | Sync Timestamp
        |--------------------------------------------------------------------------
        */

        $lastSynced = Cache::get('routes_last_synced');

        /*
        |--------------------------------------------------------------------------
        | Compute Route Values
        |--------------------------------------------------------------------------
        */

        $routes = $routes->map(function ($route) use ($selectedScu) {

            $routeScuOrigin = $route->scu_origin;
            $routeScuDest   = $route->scu_destination;

            $shipScu = $selectedScu > 0
                ? $selectedScu
                : max($routeScuOrigin, $routeScuDest);

            $usedScuOrigin = min($shipScu, $routeScuOrigin);
            $usedScuDest   = min($shipScu, $routeScuDest);

            $achievableScu = min($usedScuOrigin, $usedScuDest);

            $buy  = $route->price_origin * $achievableScu;
            $sell = $route->price_destination * $achievableScu;

            return [
                'commodity_name' => $route->commodity->name,

                'origin_star_system_name' => $route->origin->star_system_name,
                'origin_planet_name'      => $route->origin->planet_name,
                'origin_terminal_name'    => $route->origin->terminal_name,

                'destination_star_system_name' => $route->destination->star_system_name,
                'destination_planet_name'      => $route->destination->planet_name,
                'destination_terminal_name'    => $route->destination->terminal_name,

                'container_sizes_origin'      => $route->container_sizes_origin,
                'container_sizes_destination' => $route->container_sizes_destination,

                'scu_origin'      => $route->scu_origin,
                'scu_destination' => $route->scu_destination,

                'price_origin'      => $route->price_origin,
                'price_destination' => $route->price_destination,

                'distance' => $route->distance,

                'used_scu'    => $achievableScu,
                'buy_total'   => $buy,
                'sell_total'  => $sell,
                'profit'      => $sell - $buy,
            ];
        })
        ->filter(fn ($r) => $r['profit'] > 0)
        ->sortByDesc('profit')
        ->take(50)
        ->values();

        /*
        |--------------------------------------------------------------------------
        | Vehicles
        |--------------------------------------------------------------------------
        */

        $vehicleService = new VehiclesService();
        $vehicles = $vehicleService->getVehicles();

        $vehiclesGrouped = collect($vehicles)
            ->groupBy('company_name');

        /*
        |--------------------------------------------------------------------------
        | Cached Location Hierarchy (IMPORTANT OPTIMIZATION)
        |--------------------------------------------------------------------------
        */

        $locationsGrouped = Cache::remember('locations_grouped', 3600, function () {

            return Location::orderBy('star_system_name')
                ->orderBy('planet_name')
                ->orderBy('terminal_name')
                ->get()
                ->groupBy('star_system_name')
                ->map(function ($system) {

                    return $system->groupBy('planet_name')
                        ->map(function ($planet) {

                            return $planet->pluck('terminal_name')
                                ->unique()
                                ->values();
                        });
                });
        });

        $origins = $locationsGrouped;
        $destinations = $locationsGrouped;

        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */

        return view('routes.index', compact(
            'routes',
            'vehiclesGrouped',
            'selectedScu',
            'lastSynced',
            'origins',
            'destinations',
            'selectedOrigin',
            'selectedDestination'
        ));
    }

    public function sync()
    {
        if (Cache::has('routes_last_synced')) {
            return back()->with('error', 'You must wait before syncing again.');
        }

        $service = new RoutesService();
        $routes = $service->getRoutes();

        foreach ($routes as $routeData) {

            $commodity = Commodity::updateOrCreate(
                ['id' => $routeData['id_commodity']],
                ['name' => $routeData['commodity_name']]
            );

            $origin = Location::updateOrCreate([
                'star_system_name' => $routeData['origin_star_system_name'],
                'planet_name' => $routeData['origin_planet_name'],
                'terminal_name' => $routeData['origin_terminal_name'],
            ]);

            $destination = Location::updateOrCreate([
                'star_system_name' => $routeData['destination_star_system_name'],
                'planet_name' => $routeData['destination_planet_name'],
                'terminal_name' => $routeData['destination_terminal_name'],
            ]);

            Route::updateOrCreate(
                [
                    'commodity_id' => $commodity->id,
                    'origin_id' => $origin->id,
                    'destination_id' => $destination->id,
                ],
                [
                    'scu_origin' => $routeData['scu_origin'] ?? 0,
                    'scu_destination' => $routeData['scu_destination'] ?? 0,
                    'price_origin' => $routeData['price_origin'] ?? 0,
                    'price_destination' => $routeData['price_destination'] ?? 0,
                    'container_sizes_origin' => $routeData['container_sizes_origin'] ?? null,
                    'container_sizes_destination' => $routeData['container_sizes_destination'] ?? null,
                    'distance' => $routeData['distance'] ?? null,
                ]
            );
        }

        Cache::put('routes_last_synced', now(), now()->addMinutes(10));

        return back()->with('success', 'Routes synced successfully!');
    }
}