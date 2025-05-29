<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name', // Accept role name
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // If role is 'admin', only allow if email is exactly 'admin@gmail.com'
        if (
            $request->role === 'admin' &&
            $request->email !== 'admin@gmail.com'
        ) {
            return response()->json([
                'message' => 'You are not allowed to register as admin.'
            ], 403);
        }

        // Prevent multiple admins with same email
        if (
            $request->role === 'admin' &&
            User::where('email', 'admin@gmail.com')->exists()
        ) {
            return response()->json([
                'message' => 'Admin user already exists.'
            ], 409);
        }

        // Get Role
        $role = Role::where('name', $request->role)->first();

        // Create User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $role->id,
        ]);

        $user->assignRole($role->name);

        // Token only for admin (optional, or allow for other roles too)
        $token = null;
        if ($role->name === 'admin') {
            $token = $user->createToken('adminToken')->plainTextToken;
        }

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'role' => $role->name,
            'token' => $token,
        ], 201);
    }
}
