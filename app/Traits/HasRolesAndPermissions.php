<?php


namespace App\Traits;


use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

trait HasRolesAndPermissions
{
    /**
     * @return mixed
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * @return mixed
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'users_permissions');
    }

    /**
     * @param $permission
     * @return bool
     */
    public function hasPermission($permission): bool
    {
        $permission = $this->permissions()->where(function ($q) use ($permission) {
            $q->where('slug', 'like', "%$permission%");
            $q->orWhere('subject', '=', 'all');
        })->first();
        return is_null($permission) ? false : true;
    }

    /**
     * @param array $permissions
     * @return mixed
     */
    public function getAllPermissions(array $permissions)
    {
        return Permission::whereIn('id', $permissions)->get();
    }

    /**
     * @param mixed ...$permissions
     * @return  User|HasRolesAndPermissions
     */
    public function givePermissionsTo(...$permissions)
    {
        $permissions = $this->getAllPermissions($permissions);

        if ($permissions === null) {
            return $this;
        }
        $this->permissions()->saveMany($permissions);
        return $this;
    }

    /**
     * @param mixed ...$permissions
     * @return User|HasRolesAndPermissions
     */
    public function refreshPermissions(...$permissions)
    {
        $this->permissions()->detach();
        $this->givePermissionsTo(...$permissions);
        return $this;
    }
}
