<?php

use Illuminate\Database\Seeder;
use App\Designation;

class DesignationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Designation::create(
          [
            'name' => 'Team Member System Development',
            'fo_equivalent' => 'FO1'
          ]
        );
        Designation::create(
          [
            'name' => 'Team Lead System Development',
            'fo_equivalent' => 'FO1'
          ]
        );
    }
}
