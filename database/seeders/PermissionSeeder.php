<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Permission::query()->create([
            'name' => 'Обычные права',
            'slug' => 'member_read',
            'subject' => 'member',
            'action' => 'read',
        ]);
        Permission::query()->create([
            'name' => 'Учитель',
            'slug' => 'teacher_read',
            'subject' => 'teacher',
            'action' => 'read',
        ]);
        Permission::query()->create([
            'name' => 'Шаблоны наградных документов',
            'slug' => 'awardDocument_write',
            'subject' => 'awardDocument',
            'action' => 'write',
        ]);
        Permission::query()->create([
            'name' => 'Менеджер',
            'slug' => 'manager_read',
            'subject' => 'manager',
            'action' => 'read',
        ]);
        Permission::query()->create([
            'name' => 'Разработчик',
            'slug' => 'developer_read',
            'subject' => 'all',
            'action' => 'manage',
        ]);
        Permission::query()->create([
            'name' => 'Зам. директора',
            'slug' => 'deputy-director_read',
            'subject' => 'all',
            'action' => 'manage',
        ]);
        Permission::query()->create([
            'name' => 'Директор',
            'slug' => 'director_read',
            'subject' => 'all',
            'action' => 'manage',
        ]);
        Permission::query()->create([
            'name' => 'Полные права',
            'slug' => 'all_manage',
            'subject' => 'all',
            'action' => 'manage',
        ]);
        Permission::query()->create([
            'name' => 'Руководитель отдела',
            'slug' => 'headOfDepartment_read',
            'subject' => 'headOfDepartment',
            'action' => 'read',
        ]);
    }
}
