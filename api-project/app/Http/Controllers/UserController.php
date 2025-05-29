<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    // ✅ Get All Users
    public function index()
    {
        $users = User::all();
        return response()->json(['success' => true, 'users' => $users], 200);
    }

    // ✅ Store New User
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|exists:roles,name', // Validate role name
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Find role by name
        $role = Role::where('name', $request->role)->first(); // Remove guard_name condition

        if (!$role) {
            return response()->json(['success' => false, 'message' => 'Role not found'], 404);
        }

        // Create user with role_id from the Role model
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $role->id,
        ]);

        // Assign Spatie role to user (required for role-based auth)
        $user->assignRole($request->role);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'user' => $user,
            'assigned_role' => $request->role,
        ], 201);
    }

    // ✅ Get authenticated user's profile
public function getProfile(Request $request)
{
    $user = $request->user();

    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        // 'avatar' => $user->avatar, // Optional: if using profile photo
        'role' => $user->getRoleNames()->first(),
        'status' => $user->status, // Optional: if you have a 'status' field
    ]);
}


    // ✅ Get Single User
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }
        return response()->json(['success' => true, 'user' => $user], 200);
    }

    // ✅ Update User
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'role' => 'nullable|string|exists:roles,name', // Role is optional for update
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user->name = $request->name ?? $user->name;
        $user->email = $request->email ?? $user->email;
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        if ($request->role) {
            // Find new role and update
            $role = Role::where('name', $request->role)->first();
            if ($role) {
                $user->syncRoles([$role->name]);
            }
        }

        $user->save();

        return response()->json(['success' => true, 'message' => 'User updated successfully', 'user' => $user], 200);
    }

    // ✅ Delete User
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $user->delete();
        return response()->json(['success' => true, 'message' => 'User deleted successfully'], 200);
    }

    // ✅ Get Users with Paginate and Roles
    public function getUsers()
    {
        $users = User::with('roles')->paginate(10);

        $formatted = $users->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(), // Spatie Permission
            ];
        });

        return response()->json([
            'data' => $formatted,
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'per_page' => $users->perPage(),
            'total' => $users->total(),
        ]);
    }
}
