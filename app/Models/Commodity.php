<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commodity extends Model
{
    protected $fillable = [
        'name',
    ];
    
    public function routes()
    {
        return $this->hasMany(Route::class);
    }
}
