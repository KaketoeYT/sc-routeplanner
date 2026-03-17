<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $fillable = [
        '',
    ];

    public function commodity(){
        return $this->hasMany(Commodity::class);
    }
}
