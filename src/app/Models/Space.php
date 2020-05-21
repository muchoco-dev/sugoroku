<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Space extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    /**
     * 特殊マスが所属するボードを取得
     */
    public function board()
    {
        return $this->belongsTo('App\Models\Board');
    }

}
