<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:role-create', ['only' => ['store']]);
        $this->middleware('permission:role-edit', ['only' => ['update', 'assignPermissions']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }

    // ✅ Get all roles (name only)
    public function index()
    {
        $roles = Role::select('id', 'name')->get();
        return response()->json(['success' => true, 'roles' => $roles]);
    }

    // ✅ Get all available permissions
    public function permissions()
    {
        $permissions = Permission::all();
        return response()->json(['permissions' => $permissions]);
    }

    // ✅ Create a new role with permissions
public function store(Request $request)
{
    $request->validate([
        'name' => 'required|unique:roles,name',
        'permission' => 'required|array',
    ]);

    // Create role with correct guard_name
    $role = Role::create([
        'name' => $request->input('name'),
        'guard_name' => 'api', // 👈 This is crucial
    ]);

    $role->syncPermissions($request->input('permission'));

    return response()->json([
        'success' => true,
        'message' => 'Role created successfully.',
        'data' => $role->load('permissions')
    ]);
}


    // ✅ Show role with permissions by ID
    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $role
        ]);
    }

    // ✅ Update role and sync permissions
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $id,
            'permission' => 'required|array',
        ]);

        $role = Role::findOrFail($id);
        $role->update(['name' => $request->input('name')]);
        $role->syncPermissions($request->input('permission'));

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully.',
            'data' => $role->load('permissions')
        ]);
    }

    // ✅ Delete role (only if not assigned to users)
    
public function destroy($id)
{
    try {
        // Specify the guard explicitly
        $role = Role::findById($id, 'api');

        if (!$role) {
            return response()->json(['error' => 'Role not found.'], 404);
        }

        $role->delete();

        return response()->json(['message' => 'Role deleted successfully.']);
    } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
        return response()->json(['error' => $e->getMessage()], 404);
    }
}
    // ✅ Reassign users from one role to another
    public function reassignUsers(Request $request, $id)
    {
        $newRoleId = $request->input('new_role_id');
        $newRole = Role::find($newRoleId);

        if (!$newRole) {
            return response()->json(['message' => 'Target role not found.'], 404);
        }

        $oldRole = Role::findOrFail($id);
        $users = User::role($oldRole->name)->get();

        foreach ($users as $user) {
            $user->syncRoles([$newRole->name]);
        }

        return response()->json(['message' => 'Users reassigned successfully.']);
    }

    // ✅ Assign permissions to a role
    public function assignPermissions(Request $request, $id)
    {
        $request->validate([
            'permissions' => 'required|array'
        ]);

        $role = Role::findOrFail($id);
        $role->syncPermissions($request->permissions);

        return response()->json([
            'success' => true,
            'message' => 'Permissions assigned successfully.',
            'data' => $role->load('permissions')
        ]);
    }
}
