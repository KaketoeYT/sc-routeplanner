<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class routesService
{
    protected $baseUrl = 'https://api.uexcorp.space/2.0';

    public function getRoutes()
    {
        //Fetch all commodities
        $commodityResponse = Http::get($this->baseUrl . '/commodities');

        if (!$commodityResponse->successful()) {
            return [];
        }

        $commodities = $commodityResponse->json()['data'] ?? [];

        //Take first 10 commodity IDs
        $commodityIds = collect($commodities)
            ->take(1)
            ->pluck('id')
            ->toArray();

        //Fetch routes in parallel
        $responses = Http::pool(function ($pool) use ($commodityIds) {
            return collect($commodityIds)->map(function ($id) use ($pool) {
                return $pool->get($this->baseUrl . '/commodities_routes', [
                    'id_commodity' => $id
                ]);
            })->toArray();
        });

        //Merge all results
        $allRoutes = [];

        foreach ($responses as $response) {
            if ($response->successful()) {
                $allRoutes = array_merge($allRoutes, $response->json()['data'] ?? []);
            }
        }

        return $allRoutes;
    }
}