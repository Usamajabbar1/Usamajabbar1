<?php
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

class DeleteUnwantedPermissions extends Migration
{
    public function up()
    {
        Permission::whereIn('name', [
            'view-content',
            'view-reports',
            'create-users',
            'product-list',
            'product-create',
            'product-edit',
            'product-delete',
        ])->delete();
    }

    public function down()
    {
        // optional: recreate permissions if needed
    }
}
