<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

;

class ResetPassEmailMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
//     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     * @return mixed
     *
     */
    public function handle(Request $request, Closure $next)
    {
        try {
                //Check If Email Exist or Not
                $email = DB::table('users')->where('id','=',$request->route('userId'))->value('email');
                if ($email != []) {
                    $randomPassword = Str::random(8);

                    //Update Password in db
                    DB::table('users')->where('email','=',$email)->update(['password' => HASH::make($randomPassword)]);
                    $request->email = $email;
                    $request->newPass = $randomPassword;
                    return $next($request);  //Proceed to send Email From Route
                }else{
                    return response()->json([
                        'ERROR' => 'Email Not Registered',
                        'Register Now' => 'http://127.0.0.1:8000/api/newUser'
                    ],404);
                }
        }catch (\Exception $e){
            return response()->json([
                'ERROR' => 'Something Went Wrong'
            ],404);
        }

    }
}
