<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserApiController extends Controller
{
    // ✅ Get All Users
   // In your UsersController or wherever you fetch users
public function index()
{
    // 10 users per page
    $users = User::with('roles')->paginate(10);
    return response()->json($users);
}
public function getProfile(Request $request)
{
    $user = $request->user()->load('roles');

    return response()->json([
        'name' => $user->name,
        'email' => $user->email,
        'status' => $user->status ?? 'Active',
        'role' => $user->roles->first()?->name ?? 'No role',
    ]);
}



    // ✅ Store New User
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed', // 'confirmed'
            'role' => 'required|string|exists:roles,name', // Validate role name
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Find role by name
        $role = Role::where('name', $request->role)->first();

        if (!$role) {
            return response()->json(['success' => false, 'message' => 'Role not found'], 404);
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $role->id, // Optional: if you store role_id in users table
        ]);

        // Assign role
        $user->assignRole($role->name);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'user' => $user,
            'assigned_role' => $role->name,
        ], 201);
    }

    // ✅ Get Single User
   public function show($id)
{
    $user = User::with('roles')->find($id);

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
            'role' => 'nullable|string|exists:roles,name',
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
            $role = Role::where('name', $request->role)->first();
            if ($role) {
                $user->syncRoles([$role->name]);
                $user->role_id = $role->id; // Optional
            }
        }

        $user->save();

        return response()->json(['success' => true, 'message' => 'User updated successfully', 'user' => $user], 200);
    }

    public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:active,suspended,pending',
    ]);

    $user = User::findOrFail($id);
    $user->status = $request->status;
    $user->save();

    return response()->json([
        'message' => 'User status updated successfully.',
        'user' => $user,
    ]);
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

    public function getUsers()
    {
        // Fetch users with their roles
        $users = User::with('roles')->get();
    
        return response()->json($users);
    }
    
    public function stats()
{
    return response()->json([
        'total_users' => User::count(),
        'total_admins' => User::role('admin')->count(),
        'total_accountants' => User::role('accountant')->count(),
        'total_viewers' => User::role('viewer')->count(),
    ]);
}

    

    // ✅ Assign Role to User
    public function assignRole(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|string|exists:roles,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $role = Role::where('name', $request->role)->first();
        if (!$role) {
            return response()->json(['success' => false, 'message' => 'Role not found'], 404);
        }

        // Remove old roles and assign new one
        $user->syncRoles([$role->name]);

        // Optional: update user's role_id column
        $user->role_id = $role->id;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Role assigned successfully',
            'user' => $user,
            'assigned_role' => $role->name,
        ], 200);
    }
}
