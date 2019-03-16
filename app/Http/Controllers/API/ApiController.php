<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

abstract class ApiController extends Controller
{
    use Traits\GetUser;

    public function __construct()
    {
        parent::__construct();

        Auth::shouldUse('api');
    }

    /**
     * @return array
     */
    protected function withRelated(): array
    {
        return [];
    }
}