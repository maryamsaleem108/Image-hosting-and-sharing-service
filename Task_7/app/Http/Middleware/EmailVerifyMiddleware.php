<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmailVerifyMiddleware
{
    /**
     * Handle an incoming request.
     * @param \Illuminate\Http\Request  $request
     * @param \Closure  $next
    * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        //Update Email Verification Status to true in db
            if (DB::table('token')->where('email', '=', $request->route('userEmail'))->value('userToken') == $request->route('userToken')){
                DB::table('users')->where('email','=', $request->route('userEmail'))->update(['emailVerified' => True]);
                return response()->json([
                    'success' => 'True',
                    'Message' => 'Email Verified '
                ],200);
            }else{
                return response()->json([
                    'success' => 'False',
                    'Message' => 'Email is not Verified',
                ],404);
            }
        return $next($request);

    }

}
