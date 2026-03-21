<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'star_system_name',
        'planet_name',
        'terminal_name',
    ];

    public function routesFrom()
    {
        return $this->hasMany(Route::class, 'origin_id');
    }

    public function routesTo()
    {
        return $this->hasMany(Route::class, 'destination_id');
    }
}
