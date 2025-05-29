<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    // Admin - Register New User
    public function register(Request $request)
    {
        // Ensure that the user is an admin
        $user = $request->user(); // Get the authenticated user

        if (!$user || !$user->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403); // If the user is not an admin
        }

        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed', // password confirmation
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Create new user
        $newUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Optionally, you can assign a default role to the new user (e.g., "user")
        $role = Role::findByName('user'); // Assuming you have a "user" role
        $newUser->assignRole($role);

        return response()->json([
            'message' => 'User successfully created.',
            'user' => $newUser
        ], 201);
    }
}
