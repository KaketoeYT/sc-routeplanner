<?php

namespace App\Services;

class routesService
{
    protected $baseUrl = 'https://api.uexcorp.space/2.0';

    public function getRoutes()
    {
        $url = $this->baseUrl . '/commodities_routes?id_commodity=1';
        $response = file_get_contents($url);
        $data = json_decode($response, true);

        $routes = $data['data'];

        return $routes;
    }
}