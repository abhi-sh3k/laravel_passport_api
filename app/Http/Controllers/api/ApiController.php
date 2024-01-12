<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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
        ]);
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
            ]);

        } else {
            return response()->json([
                "status" => false,
                "message" => "Invalid login details"
            ]);
        }

    }

    // Profile API (get)
    public function profile() {

        $user = Auth::user();

        return response()->json([
            "status" => true,
            "message" => "Profile Information",
            "data" => [$user]
        ]);

    }

    //  Logout API (get)
    public function logout() {

        auth()->user()->token()->revoke();

        return response()->json([
            "status" => true,
            "message" => "Logout Successfull"
        ]);
    }
}
