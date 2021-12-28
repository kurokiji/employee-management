<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class ValidatePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if($request->header('token') !== null){
            $token = $request->header('token');
            $loggedUser = User::where('api_token', $token)->first();
            if(!$loggedUser){
                return response()->json(['status' => 0, 'msg' => 'There is no user with that API key']);;
            } else {
                $request->loggedUser = $loggedUser;
                return $next($request);
            }
        } else {
            return response()->json(['status' => 0, 'msg' => 'You have not entered the API key']);
        }
    }
}
