<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    /**
     * 役目を所有するユーザー
     */
    public function users()
    {
        return $this->belongsToMany('App\Models\User')
            ->using('App\Models\RoomUser')
            ->withPivot([
                'go',
                'status',
                'position'
            ]);
    }
}
