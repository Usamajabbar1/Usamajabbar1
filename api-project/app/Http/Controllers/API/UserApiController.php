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
    /**
     * Get paginated list of users with their roles.
     */
    public function index()
    {
        $users = User::with('roles')->paginate(10);

        return response()->json($users);
    }

    /**
     * Get all users with roles (admin-only).
     */
    public function getUsers()
    {
        $users = User::with('roles')->get();

        return response()->json($users);
    }

    /**
     * Get user count grouped by role for dashboard stats.
     * Uses a raw query approach to avoid relationship issues.
     */
    public function userCountByRole()
    {
        $roles = Role::all();

        $data = $roles->map(function ($role) {
            // Count users assigned to this role (model_has_roles pivot)
            $count = \DB::table('model_has_roles')
                ->where('role_id', $role->id)
                ->where('model_type', User::class)
                ->count();

            return [
                'label' => $role->name,
                'count' => $count,
            ];
        });

        return response()->json($data);
    }

    /**
     * Alias for userCountByRole, with different key names.
     */
    public function userRoleStats()
    {
        $roles = Role::all();

        $data = $roles->map(function ($role) {
            $count = \DB::table('model_has_roles')
                ->where('role_id', $role->id)
                ->where('model_type', User::class)
                ->count();

            return [
                'role' => $role->name,
                'count' => $count,
            ];
        });

        return response()->json($data);
    }

    /**
     * Return authenticated user's profile info with role.
     */
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

    /**
     * Create a new user and assign role.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $role = Role::where('name', $request->role)->first();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            // Optional if you have role_id column on users table
            'role_id' => $role->id,
        ]);

        // Assign role using Spatie permission package
        $user->assignRole($role->name);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'user' => $user,
            'assigned_role' => $role->name,
        ], 201);
    }

    /**
     * Get single user by ID with roles.
     */
    public function show($id)
    {
        $user = User::with('roles')->find($id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        return response()->json(['success' => true, 'user' => $user], 200);
    }

    /**
     * Update user info and roles.
     */
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

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->filled('role')) {
            $role = Role::where('name', $request->role)->first();
            if ($role) {
                $user->syncRoles([$role->name]);
                // Optional update role_id
                $user->role_id = $role->id;
            }
        }

        $user->save();

        return response()->json(['success' => true, 'message' => 'User updated successfully', 'user' => $user], 200);
    }

    /**
     * Update only user status (active, suspended, pending).
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,suspended,pending',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $user->status = $request->status;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully.',
            'user' => $user,
        ]);
    }

    /**
     * Delete user by ID.
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['success' => true, 'message' => 'User deleted successfully'], 200);
    }

    /**
     * Basic stats for users grouped by roles.
     */
    public function stats()
    {
        return response()->json([
            'total_users' => User::count(),
            'total_admins' => User::role('admin')->count(),
            'total_accountants' => User::role('accountant')->count(),
            'total_viewers' => User::role('viewer')->count(),
        ]);
    }

    /**
     * Assign a new role to a user (removing old roles).
     */
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

        $user->syncRoles([$role->name]);
        $user->role_id = $role->id;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Role assigned successfully',
            'user' => $user,
            'assigned_role' => $role->name,
        ]);
    }
}
