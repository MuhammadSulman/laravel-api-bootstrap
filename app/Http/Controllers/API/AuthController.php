<?php

namespace App\Http\Controllers\API;

use App\User;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use App\Http\Requests\API\ForgotRequest;
// use App\Http\Requests\API\ResetRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Auth\Passwords\PasswordBrokerManager;
// use App\Notifications\PasswordResetNotification;
use Validator;
use App\Http\Resources\UserResource;
use App\Http\Requests\API\LoginRequest;
// use App\Http\Requests\API\LoginWithAccessCodeRequest;
use JWTAuth;
use JWTFactory;

class AuthController extends Controller
{
    //

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        Auth::shouldUse('api');        
        $this->middleware('auth:api', ['except' => ['login', 
        'forgot', 'reset']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return array|JsonResponse
     */
    public function login(LoginRequest $request)
    {                      
        $credentials = $request->only(['email', 'password']);
        
        if (! $token = auth()->attempt($credentials)) {
            return new JsonResponse(['error' => 'Unauthorized'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return array
     */
    public function logout()
    {
        try {
            $this->getGuard()->logout();
        } catch(\Throwable $t) {
        }

        return ['message' => 'Successfully logged out'];
    }

    /**
     * Refresh a token.
     *
     * @return array
     */
    public function refresh()
    {
        return $this->respondWithToken(
            $this->getGuard()->refresh());
    }

    /**
     * @param ForgotRequest $request
     * @return JsonResponse
     */
    public function forgot(ForgotRequest $request)
    {
        $attributes = $request->only(['email']);

        /** @var PasswordBrokerManager $password */
        $password = App::make('auth.password');

        /** @var PasswordBroker $passwordBroker */
        $passwordBroker = $password->broker();

        /** @var User $user */
        $user = $passwordBroker->getUser($attributes);

        if (!$user) {
            return new JsonResponse([
                'error' => 'user_not_found',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $token = $passwordBroker->createToken($user);

        $user->notify(new PasswordResetNotification($token));
        
        return new JsonResponse([
            'message' => __('An email has been sent to you, Kindly follow the email.')
        ], JsonResponse::HTTP_OK);
    }

    /**
     * @param ResetRequest $request
     * @return JsonResponse
     */
    public function reset(ResetRequest $request)
    {
        $attributes = $request->only(['email', 'token', 'password', 'password_confirmation']);

        /** @var PasswordBrokerManager $password */
        $password = App::make('auth.password');

        /** @var PasswordBroker $passwordBroker */
        $passwordBroker = $password->broker();

        $result = $passwordBroker->reset($attributes, function (User $user, $password) {
            $user->update(['password' => Hash::make($password)]);
        });

        if ($result == PasswordBroker::INVALID_USER) {
            return new JsonResponse([
                'error' => 'user_not_found',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        if ($result == PasswordBroker::INVALID_PASSWORD) {
            return new JsonResponse([
                'error' => 'invalid_password',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        if ($result == PasswordBroker::INVALID_TOKEN) {
            return new JsonResponse([
                'error' => 'invalid_token',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = $passwordBroker->getUser($attributes);

        return $this->respondWithToken($this->getGuard()->login($user));
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     * @return array
     */
    protected function respondWithToken($token)
    {
        /** @var User $user */
        $user = $this->getGuard()->user();

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->getGuard()->factory()->getTTL() * 60,
            'user' => new UserResource($user)
        ];
    }

    /**
     * @return \Illuminate\Contracts\Auth\Guard|
     * \Illuminate\Contracts\Auth\StatefulGuard|\Tymon\JWTAuth\JWTGuard
     */
    protected function getGuard() {
        return Auth::guard('api');
    }

}
