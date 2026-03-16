<?php

namespace App\Services;

class commoditiesService
{
    protected $baseUrl = 'https://api.uexcorp.space/2.0/commodities';

    public function getCommodities()
    {
        $url = $this->baseUrl;
        $response = file_get_contents($url);
        $data = json_decode($response, true);

        $commodities = [];

        foreach ($data['data'] ?? [] as $commodity) {
            $commodities[] = [
                'id' => $commodity['id'],

                'code' => $commodity['code'],
                'name' => $commodity['name'],

                "price_buy" => $commodity["price_buy"],
                "price_sell" => $commodity["price_sell"],

                "is_available" => $commodity["is_available"],
            ];
        }

        return $commodities;
    }
}