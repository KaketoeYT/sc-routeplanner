<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $fillable = [
        'commodity_id',
        'origin_id',
        'destination_id',
        'scu_origin',
        'scu_destination',
        'price_origin',
        'price_destination',
        'container_sizes_origin',
        'container_sizes_destination',
        'distance',
    ];

    public function commodity()
    {
        return $this->belongsTo(Commodity::class);
    }

    public function origin()
    {
        return $this->belongsTo(Location::class, 'origin_id');
    }

    public function destination()
    {
        return $this->belongsTo(Location::class, 'destination_id');
    }
}
