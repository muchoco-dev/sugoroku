<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Auth;
use App\Models\Room;

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

