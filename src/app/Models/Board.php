<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    /**
     * ボードが所有する部屋を取得
     */
    public function rooms()
    {
        return $this->hasMany('App\Models\Room');
    }
}
