<?php

use Illuminate\Database\Seeder;
use Database\Seeds\LeaveTypeTableSeeder;
use Database\Seeds\LeaveEntitlementTableSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call('LeaveTypeTableSeeder');
      	$this->call('LeaveEntitlementTableSeeder');
        $this->call('OfficeTableSeeder');
        $this->call('PermissionsTableSeeder');
        $this->call('RegionTableSeeder');
        $this->call('RolesTableSeeder');
        $this->call('DesignationTableSeeder');
        $this->call('HolidayTableSeeder');

    }
}
