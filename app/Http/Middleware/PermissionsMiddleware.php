<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PermissionsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next , ...$permission): Response
    {
   
       $user = User::with('permissions')->findOrFail($request->user()->id) ;
        if(!$user || !$user->hasAnyPermission($permission)){
            return response()->json([
                "status" => "access dined" , 
                "message" =>  "You dont have access to this action "
            ], 403);
        }
        return $next($request);
    }
}
