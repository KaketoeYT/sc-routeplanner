<?php

namespace App\Services;

class VehiclesService
{
    protected $baseUrl = 'https://api.uexcorp.space/2.0/vehicles';

    public function getVehicles()
    {
        $vehiclesResponse = file_get_contents($this->baseUrl);
        $vehiclesData = json_decode($vehiclesResponse, true);
        $vehicles = $vehiclesData['data'];

        $prices = $this->getAllPurchasePrices();

        // Get latest price per vehicle
        $pricesByVehicle = collect($prices)
            ->sortByDesc('date_modified')
            ->groupBy('id_vehicle')
            ->map(fn ($group) => $group->first());

        foreach ($vehicles as &$vehicle) {
            $priceData = $pricesByVehicle[$vehicle['id']] ?? null;

            $vehicle['price_buy'] = $priceData['price_buy'] ?? null;

            // No rent price in this endpoint → leave null or fake it
            $vehicle['price_rent'] = $vehicle['price_buy']
                ? round($vehicle['price_buy'] * 0.02) // example fallback
                : null;
        }

        return $vehicles;
    }

    public function getAllPurchasePrices()
    {
        $url = 'https://api.uexcorp.space/2.0/vehicles_purchases_prices_all';
        $response = file_get_contents($url);
        $data = json_decode($response, true);

        return $data['data'] ?? [];
    }
}