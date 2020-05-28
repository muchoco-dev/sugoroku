<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomLog extends Model
{
    const UPDATED_AT = null;

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function room()
    {
        return $this->belongsTo('App\Models\Room');
    }

}
