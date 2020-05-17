<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use SoftDeletes;

    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'owner_id');
    }

    public function board()
    {
        return $this->belongsTo('App\Models\Board');
    }
}
