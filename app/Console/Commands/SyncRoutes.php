<?php

namespace App\Console\Commands;

use App\Models\Commodity;
use App\Models\Location;
use App\Models\Route;
use App\Services\routesService;
use Illuminate\Console\Command;

class SyncRoutes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-routes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = new routesService();
        $routes = $service->getRoutes();

        foreach ($routes as $routeData) {

            // 1. Commodity
            $commodity = Commodity::updateOrCreate(
                ['id' => $routeData['id_commodity']],
                ['name' => $routeData['commodity_name']]
            );

            // 2. Origin location
            $origin = Location::updateOrCreate(
                [
                    'star_system_name' => $routeData['origin_star_system_name'],
                    'planet_name' => $routeData['origin_planet_name'],
                    'terminal_name' => $routeData['origin_terminal_name'],
                ]
            );

            // 3. Destination location
            $destination = Location::updateOrCreate(
                [
                    'star_system_name' => $routeData['destination_star_system_name'],
                    'planet_name' => $routeData['destination_planet_name'],
                    'terminal_name' => $routeData['destination_terminal_name'],
                ]
            );

            // 4. Route
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

        $this->info('Routes synced successfully!');
    }
}
