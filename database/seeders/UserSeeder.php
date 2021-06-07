<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $member = Role::where('slug', 'member')->first();
        $user1 = User::create([
            'fullName' => 'member',
            'email' => 'member@gmail.com',
            'password' => bcrypt('member'),
            'role_id' => $member->id
        ]);
        $memberPerm = Permission::query()->where('slug', 'member_read')->first();
        $user1->permissions()->attach($memberPerm);

        $manager = Role::where('slug', 'manager')->first();
        $user2 = User::create([
            'fullName' => 'manager',
            'email' => 'manager@gmail.com',
            'password' => bcrypt('manager'),
            'role_id' => $manager->id
        ]);
        $managerPerm = Permission::query()->where('slug', 'manager_read')->first();
        $user2->permissions()->attach($managerPerm);

        $developer = Role::where('slug', 'developer')->first();
        $user3 = User::create([
            'fullName' => 'developer',
            'email' => 'developer@gmail.com',
            'password' => bcrypt('developer'),
            'role_id' => $developer->id
        ]);
        $developerPerm = Permission::query()->where('slug', 'all_manage')->first();
        $user3->permissions()->attach($developerPerm);
    }
}
