<?php

use Illuminate\Database\Seeder;
use App\LeaveType;

class LeaveTypeTableSeeder extends Seeder
{
    /**
     * Seeds the database with Leave Types.
     *
     * @return void
     */
    public function run()
    {
        LeaveType::create(['name'=>'Annual','show'=>true,
            'description' => 'A period of rest granted to an employee during a calendar year']);
        LeaveType::create(['name'=>'Casual']);
        LeaveType::create(['name'=>'Compassionate', 'show'=>true]);
        LeaveType::create(['name'=>'Examination']);
        LeaveType::create(['name'=>'Maternal', 'gender' => 'Female', 'show'=>true]);
        LeaveType::create(['name'=>'Paternal', 'gender' => 'Male']);
        LeaveType::create(['name'=>'Sick']);
        LeaveType::create(['name'=>'Other']);
    }
}
