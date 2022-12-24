<?php

namespace App\Http\Controllers;

use App\Http\Requests\loginRequest;
use App\Http\Requests\RegisterUser;
use App\Http\Requests\UpdateRequest;
use App\Models\ImageModel;
use App\Models\TokenModel;
use App\Models\User_Image_Model;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use mysql_xdevapi\Exception;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return string
     */
    public function index()
    {
        return UserModel::all()->toJson();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
///////////////////////////////////////     LOGIN   //////////////////////////////////////////

    //Login Function by email and password USING POST METHOD
    public function login(loginRequest $request)
    {
        if($request->validated()){   //Check Validation
            try {
                $email = $request->email;
                $password = $request->password;
            }catch (\Exception $e){
                abort(203);  //No Content
            }

            try {   //Exception Handling,
                $user = (DB::table('users')->where('email', '=', $email))->get('userid');
            }catch (\Exception $exception) {   //If Table or record don't Exist
                $response = [
                    'status' => 404,
                    'message' => 'Email Not Registered'
                ];
            }

            if ($user){
                $userTokenTable = (DB::table('user_token')->where('userid', '=', $user));
            }

            //Verify Password from DB
            if (Hash::check($password, $user->value('password'))) {
                $token = Str::random(10);
                $userTokenTable->update(['token' => $token]);  //Updating Token of user in d
                if (!$user->value('emailVerified')){   //Password is correct but email is not verified
                    $message = "You are Logged In, Kindly Verify Your Email. Check Your MailBox :)";
                }else{   //Password and email are verified.
                    $message = "You are Logged In Successfully :)";
                }
                $response = [
                    'status' => 200,
                    'message' => $message,
                    'Profile' => [
                        'name' => $user->value('name'),
                        'Age' => $user->value('age'),
                        'Email' => $user->value('email'),
                        'Phone Number' => $user->value('phone_number')],
                    'Reset Password' => 'http://127.0.0.1:8000/api/reset/'.$user->value('id')
                ];
            }
            else{      //Incorrect Password
                $response = [
                    'status' => 404,
                    'message' => 'Wrong Password',
                ];
            }
        }else{
            $response = [
                'status' => 404,
                'message' => 'Something Went Wrong',
            ];
        }
        return response()->json($response);

    }

///////////////////////////////////////     REGISTER   //////////////////////////////////////////

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */

//    Register using post method
    public function Register(RegisterUser $request)
    {
        if($request->validated()) {   //Check Validation
            if ($request->hasFile('profilepicture')) {   //check if request contains image or not
                $filename = time() . '.' . $request->file('profilepicture')->getClientOriginalExtension();
                $picPath = $request->file('profilepicture')->move('storage\images\\', $filename, 'public');
            } else {
                $picPath = 'storage\\images\\default.jpg';
            }

            //Create New User in db using USER Model
            try {
                $newUser = UserModel::create([
                    'name' => $request->name,
                    'age' => $request->age,
                    'email' => $request->email,
                    'phone_number' => $request->phone_number,
                    'password' => bcrypt($request->password),
                    'profilepicture' => $picPath
                ]);
            }catch (\Exception $e){
                return abort(204);  //No Content
            }


            $token = Str::random(10);   //Generate Token

//            Create New Token for user in db using UserToken Model
            try {
                $newToken = TokenModel::create([
                    'userid' => $newUser->userid,
                    'token' => $token,
                ]);
            }catch (\Exception $e){
                return abort(204);  //No Content
            }
        }

//        Go to route with data to send verification email
        return Redirect::route('verifyEmail',['userId' => $newUser->userid,'userEmail' => $newUser->email,'userName' => strtoupper($newUser->name), 'userToken'=>$token]);

        //Route of Request = Controller(register function) -> Route(verifyEmail) -> Mail(VerifyMail) -> Views(verificationEmail)->Rout(emailVerified)->Middlware(registration)
    }

//////////////////////////////////     FORGET PASSWORD   //////////////////////////////////////

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function ForgetPassword(Request $request)   //Forget Password use POST Method
    {
        $validator = Validator::make($request->all(),  //Validate Inputs
            $rules = [
                'email' => 'required|email',
                'password' => 'required',
            ],
            $messages = [
                'email.required' => 'Email is required.',
                'password.required' => 'Please Enter New Password.',
                'email.email' => 'Invalid Email Address'
            ]
        );

        if ($validator->fails()){  //If Validation Failed
            return response()->json([
                'status' => 404,
                'Message' => $validator->errors()
            ]);
        }

        try {       //Handling Exception if some excpetion occurs during getting value from db
            $userId = (DB::table('users')->where('email', '=', $request->email))->value('id');
        }catch (\Exception){
            $response = [
                'status' => 404,
                'Message' => 'Something Went Wrong'
            ];
        }

        if($userId){  //Update Password in db If User Exist
            DB::table('users')->where('userid', '=', $userId)->update(['password' => HASH::make($request->password)]);
            $response = [
                'status' => true,
                'message' => 'Password Updated Successfully! Login Now :)',
                'Login' => 'http://127.0.0.1:8000/api/login'
            ];
        }
        else{   //If User Does not Exist
            $response = [
                'status' => 404,
                'message' => 'Email Not Found! Register Now',
                'register' => 'http://127.0.0.1:8000/api/newUser'
            ];
        }
        return response()->json($response);
    }


////////////////////////////////////     UPDATE PROFILE   /////////////////////////////////////

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function UpdateProfile(UpdateRequest $request,$id){

        if($request->validated()) {   //Check Validation
            $user = UserModel::find($id);

            if($user) //If User Exists
            {
                if($request->has('name')) //Update Name
                {
                    $user->name = $request->name;

                }

                if($request->has('age'))  //Update Age

                {
                    $user->age = $request->age;
                }

                if($request->has('email'))  //Update Email

                {
                    $user->email = $request->email;
                }

                if($request->has('phone_number'))   //Update Phone Number

                {
                    $user->phone_number = $request->phone_number;
                }

                if($request->has('password'))   //Update Password

                {
                    $user->password = bcrypt($request->password);
                }

                if ($request->hasFile('profilepicture')) {   //check if request contains image or not
                    $filename = time() . '.' . $request->file('profilepicture')->getClientOriginalExtension();
                    $picPath = $request->file('profilepicture')->move('storage\images\\', $filename, 'public');
                    $user->profilepicture = $picPath; //Update Profile Picture
                }

                $user->save();  //Save Changes

                $response = [
                    'status' => 200,
                    'message' => 'Data Updated Successfully',
                    'newData' => $user
                ];
            }else{   //If User does not Exist
                $response = [
                    'status' => 404,
                    'message' => 'User Not Found. ',
                    'Register Now' => 'http://127.0.0.1:8000/api/register'
                ];

            }

            return response()->json($response);
        }
    }

////////////////////////////////////     UPLOAD IMAGE   /////////////////////////////////////

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function UploadImage(Request $request)
    {
        if ($request->login == 'True'){  //If User Is Login

            $validator = Validator::make($request->all(),  //Validate Inputs
                $rules = [
                    'visibility' => 'max:6',
                    'image' => 'required|image',
                ],
                $messages = [
                    'image.required' => 'Image is required.',
                    'image.image' => 'Input should be Image'
                ]
            );

            if ($validator->fails()){  //If Validation Failed
                return response()->json([
                    'status' => 404,
                    'Message' => $validator->errors()
                ]);
            }

            if ($validator){
                $filename = time();
                $extension = $request->file('image')->getClientOriginalExtension();
                $fullpath = 'storage\uploadedImage\\'.$filename.'.'.$extension;

                // Save Image in DB
                try {
                    $image = ImageModel::create([
                        'name' => $filename,
                        'extension' => $extension,
                        'date' => now()->format('Y-m-d'),
                        'time' => now()->format('H:i:s'),
                        'visibility' => strtolower($request->visibility),
                        'imagepath' => $fullpath
                    ]);
                }catch (Exception $e){
                    $response = [
                        'status' => 404,
                        'Message' => 'Something Went Wrong! Image Upload Process Failed'
                    ];
                }

                //Save Image with user Id in DB
                if ($image){
                    $request->file('image')->move('storage\uploadedImage\\', $filename.'.'.$extension, 'public');
                    $image_user = User_Image_Model::create([
                        'userid' => $request->loginId,
                        'imageid' => $image->imageid
                    ]);

                    $response = [
                        'status' => 200,
                        'Message' => 'Image Uploaded Successfully',
                        'Image Shareable Link' => 'http://127.0.0.1:8000/api/Image/'.$image->imageid
                    ];
                }else{
                    $response = [
                        'status' => 404,
                        'Message' => 'Something Went Wrong! Image Upload Process Failed'
                    ];
                }
            }

        }else{     //If User Is not Login
            $response = [
                'status' => 404,
                'Message' => 'You are not LoggedIn. Login to upload Picture.',
                'LogIn Now' => 'http://127.0.0.1:8000/api/login'
            ];
        }

        return response()->json($response);
    }

////////////////////////////////////     DELETE IMAGE   /////////////////////////////////////

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteImage(Request $request, $image_id)
    {
        if ($request->login == 'True'){

            //Check if image is uploaded by logged-in user
            if((DB::table('user_image')->where('userid','=',$request->loginId)->value('imageid')) == $image_id){
                    $deleted = DB::table('images')->where('imageid','=',$image_id)->delete();

                    //Verify Deletion
                    if ($deleted){
                        try {
                            DB::table('user_image')->where('imageid','=',$image_id)->delete();
                            $response = [
                                'status' => '200',
                                'id' => $image_id,
                                'Message' => 'Image Deleted Successfully'
                            ];
                        }catch (\Exception $e){
                            $response = [
                                'status' => '404',
                                'Message' => 'Something Went Wrong!'
                            ];
                        }
                    }
            }else{
                $response = [
                    'status' => '404',
                    'Message' => 'You are not Authorized to delete this Image'
                ];
            }
        }else{
            $response = [
                'status' => 404,
                'Message' => 'You are not LoggedIn. Login to Delete Picture.',
                'LogIn Now' => 'http://127.0.0.1:8000/api/login'
            ];
        }
        return response()->json($response);
    }

////////////////////////////////////     LIST OF IMAGES   /////////////////////////////////////

    public function listImages(Request $request){


        if ($request->login == 'True'){     //User is login
            $images = ImageModel::all();
            $response = [
                'status' => 200,
                'Message' => 'You Are Logged In',
                'Images' => $images
            ];
        }else{      //User is not login
            $images = ImageModel::where('visibility','public')->get();
            $response = [
                'status' => 200,
                'Message' => 'Login To See All Pictures',
                'Images' => $images
            ];
        }

        return response()->json($response);
    }

////////////////////////////////////    Search Image   /////////////////////////////////////

    public function searchImage(Request $request)
    {
        if ($request->login == 'True') {      //User is login
            if (!empty($request->id)) {  //Search by id
                $images = ImageModel::where('imageid', $request->id)->get();
                $response = [
                    'status' => 200,
                    'Images' => $images
                ];

            }elseif (!empty($request->name)) {      //Search by name
                $images = ImageModel::where('name', $request->name)->get();
                $response = [
                    'status' => 200,
                    'Images' => $images
                ];
            } elseif (!empty($request->ext)) {      //Search by extension
                $images = ImageModel::where('extension', $request->ext)->get();
                $response = [
                    'status' => 200,
                    'Images' => $images
                ];
            } elseif (!empty($request->visibility)) {       //Search by visibility
                $images = ImageModel::where('visibility', $request->visibility)->get();
                $response = [
                    'status' => 200,
                    'Images' => $images
                ];
            }else {
                $images = ImageModel::all();
                $response = [
                    'status' => 200,
                    'Images' => $images
                ];
            }
        }else {           //User is not login
            if (!empty($request->id)) {     //Search by id, show only public
                $images = ImageModel::where('imageid', $request->id)->where('visibility', 'public')->get();
                $response = [
                    'status' => 200,
                    'Images' => $images
                ];

            } elseif (!empty($request->name)) {     //Search by name, show only public
                $images = ImageModel::where('name', $request->name)->where('visibility', 'public')->get();
                $response = [
                    'status' => 200,
                    'Images' => $images
                ];
            } elseif (!empty($request->ext)) {      //Search by extension, show only public
                $images = ImageModel::where('extension', $request->ext)->where('visibility', 'public')->get();
                $response = [
                    'status' => 200,
                    'Images' => $images
                ];
            } elseif (!empty($request->visibility)) {       //Search by visibility, show only public
                if ($request->visibility == 'public') {
                    $images = ImageModel::where('visibility', 'public')->get();
                    $response = [
                        'status' => 200,
                        'Images' => $images
                    ];
                } else {
                    $response = [
                        'status' => 404,
                        'Images' => 'Login to see private photoes.'
                    ];
                }
            } else {
                $images = ImageModel::where('visibility', 'public')->get();
                $response = [
                    'status' => 200,
                    'Message' => 'Login To See All Pictures',
                    'Images' => $images
                ];
            }
        }
        return response()->json($response);
    }

////////////////////////////////////   CHANGE VISIBILITY   /////////////////////////////////////

    public function changeVisibility(Request $request,$image_id){
        $validator = Validator::make($request->all(),  //Validate Inputs
            $rules = [
                'visibility' => 'required'
            ],
            $messages = [
                'visibility.required' => 'visibility is required.'
            ]
        );

        if ($validator->fails()){  //If Validation Failed
            return response()->json([
                'status' => 404,
                'Message' => $validator->errors()
            ]);
        }

        if ($request->login == 'True'){     //User is Login

            //Check if image is uploaded by logged-in user
            if((DB::table('user_image')->where('userid','=',$request->loginId)->value('imageid')) == $image_id){

                //Update Visibility
              $updated = ImageModel::where('imageid',$image_id)->update(['visibility' => strtolower($request->visibility)]);
              if ($updated){
                   $response = [
                       'status' => '200',
                       'Message' => 'Visibility Updated Successfully on id = '.$image_id
                   ];
              }
            }else{
                $response = [
                    'status' => '404',
                    'Message' => 'You are not Authorized to Update this Image'
                ];
            }
        }else{
            $response = [
                'status' => 404,
                'Message' => 'You are not LoggedIn. Login to Update Picture Visibility.',
                'LogIn Now' => 'http://127.0.0.1:8000/api/login'
            ];
        }

        return response()->json($response);
    }

////////////////////////////////////   Shareable Link   /////////////////////////////////////

    public function showImage(Request $request,$image_id){

        //Get visibility of image
        $visibility = DB::table('images')->where('imageid','=',$image_id)->value('visibility');
        if (!empty($visibility)){
            if ($visibility == 'public'){   //If Image is public, show it with or without login
                $response = [
                    'status' => 200,
                    'Image' => ImageModel::where('imageid', $image_id)->get()
                ];
            }elseif ($visibility == 'private' || $visibility == 'hidden'){
                if ($request->login == 'True'){ //If Image is not public and user is login, show it
                    $response = [
                        'status' => 200,
                        'Image' => ImageModel::where('imageid', $image_id)->get()
                    ];
                }else{      //If Image is not public and user is not login, Do not show it.
                    $response = [
                        'status' => 404,
                        'Message' => 'You are not LoggedIn. Login to See This Image.',
                        'LogIn Now' => 'http://127.0.0.1:8000/api/login'
                    ];
                }
            }else{
                $response = [
                    'status' => 404,
                    'Message' => 'Something Went Wrong'
                ];
            }
        }else{
            $response = [
                'status' => 404,
                'Message' => 'Image Do not Exist'
            ];
        }

        return response()->json($response);
    }
}
