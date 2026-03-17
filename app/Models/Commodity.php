<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commodity extends Model
{
    public function Route(){
        return $this->belongsTo(Route::class);
    }
}
