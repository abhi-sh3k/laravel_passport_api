<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\ForgotPassword;
use App\Http\Requests\api\PasswordReset as PasswordResetRequest;
use App\Http\Requests\api\UpdatePassword;
use App\Models\api\PasswordReset;
use Exception;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class ApiController extends Controller
{
    // Register API (post)
    public function register(Request $request) {

        // Data Validation
        $request->validate([
            "name" => "required",
            "email" => "required|email|unique:users",
            "password" => "required|confirmed"
        ]);

        //  Create User
        User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password)
        ]);

        return response()->json([
            "status" => true,
            "message" => "User Created Successfully !"
        ], 200);
    }

    //  Login API (post)
    public function login(Request $request) {

        // Data Validation
        $request->validate([
            "email" => "required|email",
            "password" => "required"
        ]);

        //  Check User login
        if(Auth::attempt([
            "email" => $request->email,
            "password" => $request->password
        ])) {

            // User Exist
            $user = Auth::user();

            $token = $user->createToken("myToken")->accessToken;

            return response()->json([
                "status" => true,
                "message" => "Login Successfull",
                "data" => [$token, $user]
            ], 200);

        } else {
            return response()->json([
                "status" => false,
                "message" => "Invalid login details"
            ], 404);
        }

    }

    // Profile API (get)
    public function profile() {

        $user = Auth::user();

        return response()->json([
            "status" => true,
            "message" => "Profile Information",
            "data" => [$user]
        ], 200);

    }

    //  Logout API (get)
    public function logout() {

        auth()->user()->token()->revoke();

        return response()->json([
            "status" => true,
            "message" => "Logout Successfull"
        ], 200);
    }

    // Forgot Password API (post)
    public function forgotPassword(ForgotPassword $forgotPassword) {

        $email = $forgotPassword->input('email');

        if(User::where('email', $email)->doesntExist()) {

            return response()->json([
                "message" => "User doesn't exist"
            ], 404);
        }

        $token = Str::random(40);

        try {
            PasswordReset::create([
                "email" => $email,
                "token" => $token
            ]);

            // Send Email
            Mail::send("api.mail.forgot-password", ['token' => $token], function(Message $message) use ($email) {
                $message->to($email);
                $message->subject("Reset your password");
            });

            return response()->json([
                "status" => true,
                "message" => "Check yoour email"
            ], 200);

        } catch (Exception $exception) {
            return response()->json([
                "status" => false,
                "message" => $exception->getMessage()
            ], 400);
        }
    }

    // Reset password API (post)
    public function resetPassword(PasswordResetRequest $passwordReset) {
        $token = $passwordReset->token ;

        if(!$passwordResets = PasswordReset::where('token', $token)->first()){
            return response()->json([
                "status" => false,
                "message" => "Invalid Token"
            ], 400);
        }

        if(!$user = User::where('email', $passwordResets->email)->first()) {
            return response()->json([
                "message" => "User doesn't exist"
            ], 404);
        }

        $user->password = Hash::make($passwordReset->password);
        $user->save();

        return response()->json([
            "status" => true,
            "message" => "Password reset successfully"
        ], 200);
    }

    // Update Password API (post)
    public function updatePassword(UpdatePassword $updatePassword) {
        $user = Auth::user();

        if(Hash::check($updatePassword->password, $user->password)){
            $user->password = Hash::make($updatePassword->new_password);
            $user->save();

            return response()->json([
                "status" => true,
                "message" => "Password updated successfully"
            ], 200);

        } else {
            return response()->json([
                "status" => false,
                "message" => "Incorrect old password"
            ], 400);
        }
    }
}
