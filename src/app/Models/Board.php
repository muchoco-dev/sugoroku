<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    /**
     * ボードを所有するユーザーを取得
     */
    public function users()
    {
        return $this->belongsToMany('App\Models\User');
    }
}
