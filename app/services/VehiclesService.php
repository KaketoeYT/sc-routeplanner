<?php

namespace App\Services;

class VehiclesService
{
    protected $baseUrl = 'https://api.uexcorp.space/2.0/vehicles';

    public function getVehicles()
    {
        $url = $this->baseUrl;
        $response = file_get_contents($url);
        $data = json_decode($response, true);

        $vehicles = $data['data'];

        return $vehicles;
    }
}