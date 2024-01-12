<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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

    }

    // Profile API (get)
    public function profile() {

    }

    //  Logout API (get)
    public function logout() {

    }
}
