<?php

namespace App\Http\Requests\API;

use Illuminate\Support\Facades\Auth;

abstract class BaseRequest extends \App\Http\Requests\BaseRequest
{
    public function authorize()
    {
        return Auth::guard('api')->check();
    }
}