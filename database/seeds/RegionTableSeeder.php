<?php

use Illuminate\Database\Seeder;
use App\Region;

class RegionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Region::create(
          [
            'name'=>'FCT North'
          ]
        );
        Region::create(
          [
            'name'=>'FCT Central'
          ]
        );
        Region::create(
          [
            'name'=>'FCT South'
          ]
        );
        Region::create(
          [
            'name'=>'Kogi'
          ]
        );
        Region::create(
          [
            'name'=>'Niger'
          ]
        );
        Region::create(
          [
            'name'=>'Nassarawa'
          ]
        );
    }
}
