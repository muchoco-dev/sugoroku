<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomLog extends Model
{
    const CREATED_AT = null;

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'id');
    }

    public function room()
    {
        return $this->belongsTo('App\Models\Room', 'id')
    }

}
