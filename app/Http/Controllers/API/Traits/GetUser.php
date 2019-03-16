<?php

namespace App\Http\Controllers\API\Traits;

use Illuminate\Support\Facades\Auth;

trait GetUser
{
    /**
     * @return \App\User
     */
    protected function getUser()
    {
        /** @var \App\User $user */
        $user = Auth::user();

        return $user;
    }
}
