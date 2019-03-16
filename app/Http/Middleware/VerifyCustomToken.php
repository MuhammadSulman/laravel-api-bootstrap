<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use JWTFactory;
use Illuminate\Http\JsonResponse;

class VerifyCustomToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->input('token');
        if(!$token){
            return response()->json(array('message'=>'token not found'), 404);
        }
                        
        try { 
            JWTAuth::setToken($token);
            $claim = JWTAuth::getPayload();
            if (! $claim ) { 
                return response()->json(array('message'=>'user_not_found'), 404); 
            } 
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(array('message'=> $e->getMessage()),JsonResponse::HTTP_NOT_FOUND); 
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {            
            return response()->json(array('message'=> $e->getMessage()),JsonResponse::HTTP_NOT_FOUND); 
        } catch (\Tymon\JWTAuth\Exceptions\TokenBlacklistedException $e) {
            return response()->json(array('message'=> $e->getMessage()),JsonResponse::HTTP_NOT_FOUND); 
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(array('message'=> $e->getMessage()),JsonResponse::HTTP_NOT_FOUND); 
        }
        
        return $next($request);
    }
}
