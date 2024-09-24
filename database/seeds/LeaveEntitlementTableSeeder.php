<?php


use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\LeaveEntitlement;

class LeaveEntitlementTableSeeder extends Seeder
{
    /**
     * Seeds the database with Leave Types.
     *
     * @return void
     */
    public function run()
    {
        LeaveEntitlement::create(
            [
                'leave_type'=>'Annual',
                'days_since_resumption'=> 180,
                'days_allowed' => 15,
                'salary_grade' => 'FO1',
                'show' => true
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Annual',
                'days_since_resumption'=> 180,
                'days_allowed' => 11,
                'salary_grade' => 'FO2',
                'show' => true
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Annual',
                'days_since_resumption'=> 210,
                'days_allowed' => 18,
                'salary_grade' => 'FO1',
                'show' => true
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Annual',
                'days_since_resumption'=> 210,
                'days_allowed' => 13,
                'salary_grade' => 'FO2',
                'show' => true
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Annual',
                'days_since_resumption'=> 240,
                'days_allowed' => 20,
                'salary_grade' => 'FO1',
                'show' => true
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Annual',
                'days_since_resumption'=> 240,
                'days_allowed' => 14,
                'salary_grade' => 'FO2',
                'show' => true
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Annual',
                'days_since_resumption'=> 270,
                'days_allowed' => 23,
                'salary_grade' => 'FO1',
                'show' => true
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Annual',
                'days_since_resumption'=> 270,
                'days_allowed' => 16,
                'salary_grade' => 'FO2',
                'show' => true
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Annual',
                'days_since_resumption'=> 300,
                'days_allowed' => 25,
                'salary_grade' => 'FO1',
                'show' => true
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Annual',
                'days_since_resumption'=> 300,
                'days_allowed' => 18,
                'salary_grade' => 'FO2',
                'show' => true
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Annual',
                'days_since_resumption'=> 330,
                'days_allowed' => 28,
                'salary_grade' => 'FO1',
                'show' => true
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Annual',
                'days_since_resumption'=> 330,
                'days_allowed' => 19,
                'salary_grade' => 'FO2',
                'show' => true
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Annual',
                'days_since_resumption'=> 360,
                'days_allowed' => 30,
                'salary_grade' => 'FO1',
                'show' => true
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Annual',
                'days_since_resumption'=> 360,
                'days_allowed' => 21,
                'salary_grade' => 'FO2',
                'show' => true
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Compassionate',
                'days_since_resumption'=> 360,
                'days_allowed' => 5,
                'salary_grade' => 'FO1'
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Compassionate',
                'days_since_resumption'=> 360,
                'days_allowed' => 5,
                'salary_grade' => 'FO2'
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Compassionate',
                'days_since_resumption'=> 180,
                'days_allowed' => 5,
                'salary_grade' => 'FO1'
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Compassionate',
                'days_since_resumption'=> 180,
                'days_allowed' => 5,
                'salary_grade' => 'FO2'
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Maternity',
                'days_since_resumption'=> 180,
                'days_allowed' => 112,
                'salary_grade' => 'FO1',
                'show' =>true
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Maternity',
                'days_since_resumption'=> 180,
                'days_allowed' => 112,
                'salary_grade' => 'FO2',
                'show' =>true
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Maternity',
                'days_since_resumption'=> 360,
                'days_allowed' => 112,
                'salary_grade' => 'FO1',
                'show' =>true
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Maternity',
                'days_since_resumption'=> 360,
                'days_allowed' => 112,
                'salary_grade' => 'FO2',
                'show' =>true
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Paternity',
                'days_since_resumption'=> 180,
                'days_allowed' => 5,
                'salary_grade' => 'FO1'
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Paternity',
                'days_since_resumption'=> 180,
                'days_allowed' => 5,
                'salary_grade' => 'FO2'
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Paternity',
                'days_since_resumption'=> 360,
                'days_allowed' => 5,
                'salary_grade' => 'FO1'
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Paternity',
                'days_since_resumption'=> 360,
                'days_allowed' => 5,
                'salary_grade' => 'FO2'
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Casual',
                'days_since_resumption'=> 180,
                'days_allowed' => 5,
                'salary_grade' => 'FO1'
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Casual',
                'days_since_resumption'=> 180,
                'days_allowed' => 5,
                'salary_grade' => 'FO2'
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Casual',
                'days_since_resumption'=> 360,
                'days_allowed' => 5,
                'salary_grade' => 'FO1'
            ]);
        LeaveEntitlement::create(
            [
                'leave_type'=>'Casual',
                'days_since_resumption'=> 360,
                'days_allowed' => 5,
                'salary_grade' => 'FO2'
            ]);

    }
}
?>
