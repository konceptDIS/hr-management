<?php

use App\Role;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'name' => 'HR',
                'display_name' => 'Leave Administrator',
                'description' => 'Members can handle HR functions as respect leave processing'
            ],
            [
                'name' => 'Admin',
                'display_name' => 'IT Administrator',
                'description' => 'Allow user to manage system users'
            ],
            [
                'name' => 'OC',
                'display_name' => 'OPS Control',
                'description' => 'Leave Supervisors'
            ]
        ];

        foreach ($roles as $key => $value) {
            if (Role::where('name', $value['name'])->count() == 0) {
                Role::create($value);
            }
        }
    }
}
