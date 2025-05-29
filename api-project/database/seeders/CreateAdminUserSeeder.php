<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
  // Ensure role exists
        $role = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'api',
        ]);
        // $role = Role::create(['name' => 'Admin']);

        // $role->syncPermissions(Permission::pluck('name')->all());

        // $user = User::create([
        //     'name' => 'Hardik Savani',
        //     'email' => 'admin@gmail.com',
        //     'password' => bcrypt('123456'),
        //     'role_id' => $role->id,
        // ]);
 // Create admin user
        $user = User::firstOrCreate([
            'email' => 'admin@gmail.com'
        ], [
            'name' => 'Hardik Savani',
            'password' => bcrypt('123456'),
            'role_id' => $role->id,
        ]);



        $permissions = Permission::pluck('id','id')->all();

        $role->syncPermissions($permissions);

        $user->assignRole('admin');
    }
}
