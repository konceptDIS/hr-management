<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Holiday;

class HolidayTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {        
        Holiday::create(
            [
                'name' => 'Eid-el-fitri',
                'date'=>'2017/6/26', 
                'created_by'=> 'system',
            ]);
        Holiday::create(
            [
                'name' => 'Sallah Holiday',
                'date'=>'2017/6/27', 
                'created_by'=> 'system',
            ]);
        Holiday::create(
            [
                'name' => 'Id el Kabir',
                'date'=>'2017/9/01', 
                'created_by'=> 'system',
            ]);
        Holiday::create(
            [
                'name' => 'Independence Day',
                'date'=>'2017/10/01', 
                'created_by'=> 'system',
            ]);
        Holiday::create(
            [
                'name' => 'Christmas',
                'date'=>'2017/12/25', 
                'created_by'=> 'system',
            ]);
        Holiday::create(
            [
                'name' => 'Boxing',
                'date'=>'2017/12/26', 
                'created_by'=> 'system',
            ]);

    }
}
