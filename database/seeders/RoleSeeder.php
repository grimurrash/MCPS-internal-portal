<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Role::query()->create([
            'name' => 'Сотрудник',
            'slug' => 'member'
        ]);
        Role::query()->create([
            'name' => 'Учитель',
            'slug' => 'teacher'
        ]);
        Role::query()->create([
            'name' => 'Менеджер',
            'slug' => 'manager'
        ]);
        Role::query()->create([
            'name' => 'Разработчик',
            'slug' => 'developer'
        ]);
        Role::query()->create([
            'name' => 'Зам. директора',
            'slug' => 'deputy-director'
        ]);
        Role::query()->create([
            'name' => 'Директор',
            'slug' => 'director'
        ]);
    }
}
