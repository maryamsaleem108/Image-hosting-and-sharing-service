<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// -------------------------------------------- LOGIN ------------------------------------------------------
try {
    Route::post('login', [userController::class, 'login']);
}catch (MethodNotAllowedException $exception){
    return response()->json([
        'message' => 'Use POST Method'
    ],405);
}


// -------------------------------------------- Register ------------------------------------------------------
try {
    Route::post('register', [userController::class, 'Register']);
}catch (MethodNotAllowedException $exception){
    return response()->json([
        'message' => 'Use POST Method'
    ],405);
}

// -------------------------------------------- SEND VERIFICATION EMAIL ------------------------------------------------------
Route::get('email/{userId}/{userEmail}/{userName}/{userToken}',function ($userId,$userEmail,$userName,$userToken) {
    Mail::to($userEmail)->send(new VerifyMail($userId,$userEmail,$userName,$userToken)); //Send Mail with Verify Email Button
    return response()->json([
        'message' => 'Successfully Inserted On Id = '.$userId.' Kindly Check Your Mail :)',
        'Login Now' => 'http://127.0.0.1:8000/api/login'
    ],201);
})->name('verifyEmail');

// -------------------------------------------- LINK IN VERIFICATION MAIL ------------------------------------------------------
Route::get('verified/{userEmail}/{userToken}',function ($userEmail,$userToken){
})->name('emailVerified')->middleware('registration'); //Middleware = EmailVerifyMiddleware

// -------------------------------------------- FORGET PASSWORD ------------------------------------------------------
try {
    Route::post('forget',[userController::class, 'ForgetPassword'])->name('forgetPassword');
}catch (MethodNotAllowedException $exception){
    return response()->json([
        'message' => 'Use POST Method'
    ],405);
}

// -------------------------------------------- RESENT PASSWORD Through Email ------------------------------------------------------
try {
    Route::get('reset/{userId}',function ($userId){
        Mail::to(request()->email)->send(new ResetPasswordMail(request()->newPass));  //Send Mail with New and Updated Password
        return response()->json([
            'Status' => 'True',
            'Message' => 'Your Password Has Been Reset. Kindly Check You MailBox. :)',
            'Login Now' => 'http://127.0.0.1:8000/api/login'

        ]);
    })->name('resetPassword')->middleware('checkEmail');  //Middleware = ResetPassEmailMiddleware

}catch (MethodNotAllowedException $exception){
    return response()->json([
        'message' => 'Use Get Method'
    ],405);
}

// -------------------------------------------- UPDATE PROFILE ------------------------------------------------------
Route::post('updateProfile/{id}',[UserController::class,'UpdateProfile'])->name('UpdateProfile');

// ---------- ROUTES WITH SAME MIDDLEWARE ------------------------
Route::group(['middleware' => 'checkLogin'], function() {

    // -------------------------------------------- UPLOAD IMAGE ------------------------------------------------------
    Route::post('uploadImage/{token}',[UserController::class,'UploadImage'])->name('UploadImage');

    // -------------------------------------------- DELETE IMAGE ------------------------------------------------------
    Route::get('deleteImage/{image_id}/{token}',[UserController::class,'deleteImage'])->name('deleteImage');

    // -------------------------------------------- LIST ALL IMAGES ------------------------------------------------------
    Route::get('listImages/{token?}',[UserController::class,'listImages'])->name('listImages');

    // -------------------------------------------- SEARCH IMAGES ------------------------------------------------------
    Route::get('searchImage/{token?}/{id?}/{name?}/{ext?}/{date?}/{time?}/{visibility?}',[UserController::class,'searchImage'])->name('searchImage');

    // -------------------------------------------- CHANGE VISIBILITY ------------------------------------------------------
    Route::post('changeVisibility/{image_id}/{token?}',[UserController::class,'changeVisibility'])->name('ChangeVisibility');

    // -------------------------------------------- SHOW IMAGE ------------------------------------------------------
    //(Shareable link = http://127.0.0.1:8000/api/Image/{image id}/{token?})
    Route::get('showImage/{image_id}/{token?}',[UserController::class,'showImage'])->name('ShowImage');
});
