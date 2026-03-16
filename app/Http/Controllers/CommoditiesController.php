<?php

namespace App\Http\Controllers;

use App\Services\commoditiesService;
use Illuminate\Http\Request;

class CommoditiesController extends Controller
{
    public function index()
    {
        $service = new commoditiesService();
        $commodities = $service->getCommodities();

        return view('commodities.index', compact('commodities'));
    }
}
