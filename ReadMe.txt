Submitted By: Maryam Saleem

--------------------------------------------  Modified Files  --------------------------------------------

1. Http/Controllers/userController.php
2. Http/Middleware/CheckLoginMiddleware.php
3. Http/Middleware/EmailVerifyMiddleware.php
4. Http/Middleware/ResetPassEmailMiddleware.php
5. Http/Requests/loginRequest.php
6. Http/Requests/RegisterUser.php
7. Http/Requests/UpdateRequest.php
8. Mail/EmailVerificationMail.php
9. Mail/ResetPasswordMail.php
10. Models/ImageModel.php
11. Models/TokenModel.php
12. Models/User_Image_Model.php
13. Models/UserModel.php
14. resources/views/resetPasswordView.blade.php
15. resources/views/verificationEmail.blade.php
16. routes/api.php

--------------------------------------------  STORAGE  --------------------------------------------
1. public/storage/images
2. public/storage/uploadedImage

--------------------------------------------  Database Name = Task7  --------------------------------------------
Database Name = Task7

 ______________ Tables and Thier Queries: ______________

				 ------------------------- 1. Users Table ----------------------

CREATE TABLE IF NOT EXISTS Users(
	userId SERIAL PRIMARY KEY NOT NULL,
	name varchar(255) NOT NULL,
	age integer NOT NULL,
	email varchar(255) UNIQUE NOT NULL,
	phone_number varchar(13),
	password varchar(255) Not Null,
	profilePicture varchar(255) Not NULL DEFAULT 'storage\images\default.jpg',
	emailverified boolean NOT NULL DEFAULT false,
	created_at timestamp(0) without time zone,
    	updated_at timestamp(0) without time zone,
)

  				------------------------- 2.  Images Table ---------------------

CREATE TABLE IF NOT EXISTS Images
(
    	imageid SERIAL NOT NULL PRIMARY KEY,
    	name VARCHAR(255)  NOT NULL,
    	extension VARCHAR(255) NOT NULL,
   	 date date NOT NULL,
   	 "time" time(0) without time zone,
   	 visibility varchar(10) DEFAULT('hidden'),
	imagepath varchar(255) NOT NULL,
   	 created_at timestamp(0) without time zone,
    	updated_at timestamp(0) without time zone,
)

				 ------------------------- 3.  User Token Table ----------------------
CREATE TABLE IF NOT EXISTS User_Token(
	tokenId SERIAL NOT NULL PRIMARY KEY,
	userId INT,
	token varchar(255) NOT NULL,
	created_at timestamp(0) without time zone,
	updated_at timestamp(0) without time zone,
	CONSTRAINT userid_fkey FOREIGN KEY (userid) REFERENCES public.users (userid)
)

				 ------------------------- 4. User Image Table ---------------------

CREATE TABLE IF NOT EXISTS user_image
(
    	id SERIAL NOT NULL DEFAULT PRIMARY KEY,
    	userid integer,
    	imageid integer,
	created_at timestamp(0) without time zone,
    	updated_at timestamp(0) without time zone,
   	CONSTRAINT fk_user FOREIGN KEY (userid) REFERENCES users (userid),
    	CONSTRAINT fk_image FOREIGN KEY (imageid) REFERENCES images (imageid)
   
)



--------------------------------------------  Routes  --------------------------------------------

--------------------- Login: 	 http://127.0.0.1:8000/api/login 		 (Method = POST)
--------------------- Register: 		 http://127.0.0.1:8000/api/register	(Method = POST)
--------------------- Forget Password:   	http://127.0.0.1:8000/api/forget 	 (Method = POST) 
--------------------- Reset Password: 	   http://127.0.0.1:8000/api/reset/{userId}  	(Method = GET)
--------------------- Update Profile: 	 http://127.0.0.1:8000/api/updateProfile/{id}  	(Method = POST)
--------------------- UPLOAD IMAGE: 	 http://127.0.0.1:8000/api/uploadImage/{token}  	(Method = POST)
--------------------- DELETE IMAGE: 	 http://127.0.0.1:8000/api/deleteImage/{image_id}/{token}  	(Method = GET)
--------------------- LIST ALL IMAGES: 	 http://127.0.0.1:8000/api/listImages/{token?}  	(Method = GET)
--------------------- SEARCH IMAGE: 	 http://127.0.0.1:8000/api/searchImage/{searchitem} 	(Method = GET)
--------------------- CHANGE VISIBILITY: 	 http://127.0.0.1:8000/api/changeVisibility/{image_id}/{token?} 	(Method = POST)
--------------------- SHOW IMAGE: 	 http://127.0.0.1:8000/api/showImage/{image_id}/{token?}	(Method = GET)

















