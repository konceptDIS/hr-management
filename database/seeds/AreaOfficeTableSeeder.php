<?php

use Illuminate\Database\Seeder;
use App\AreaOffice;

class AreaOfficeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AreaOffice::create(
          [
            'name' => 'Akwanga',
            'region_id' => 6
          ]
        );
    }
}
