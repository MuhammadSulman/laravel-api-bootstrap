<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Resources\UserResource;


class APIRegisterController extends RegisterController
{


	public function register(Request $request)
    {
        $errors = $this->validator($request->all())->errors();

        if(count($errors)){
            return response(['errors' => $errors], 422);
        }

        event(new Registered($user = $this->create($request->all())));

        return new UserResource($user);
    }

}