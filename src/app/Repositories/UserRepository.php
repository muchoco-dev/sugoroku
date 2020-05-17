<?php

namespace App\Repositories;

use App\Models\Room;
use Illuminate\Support\Facades\Auth;

class UserRepository
{
    /**
     * Personal Access Tokenの取得
     */
    public function getPersonalAccessToken()
    {
        $token = Auth::user()->createToken('token_for_user1')->accessToken;
        return $token;
    }
}

