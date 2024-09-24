<?php

use App\User;
use App\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LeaveRequestTest extends TestCase
{
    use DatabaseTransactions;


    public function test_i_am_redirect_to_login_if_i_try_to_view_my_dashboard_without_logging_in()
    {
        $this->browse(function ($browser) {
            $browser->visit('/home')->see('Login');
        });
    }


    public function test_users_can_login_with_AD_credentials_and_have_a_local_profile_created()
    {
        $this->visit('/')
            ->type('emem.isaac', 'username')
            ->type('2018.01.06.TDday!', 'password')
            ->press('Login')
            ->seePageIs('/home')
            ->seeInDatabase('users', ['username' => 'emem.isaac']);
    }


    public function test_authenticated_users_can_apply_for_leave()
    {
        $user = factory(User::class)->create(); //User::where('username','emem.isaac')->first(); 
        $time = time();
        $this->actingAs($user)
             ->visit('/new-request')
             ->type('Annual ', 'leave_type')
             ->type('Testing @ ' . $time, 'reason')
             ->type('17/01/2018', 'start_date')
             ->type('5', 'days_requested')
             ->type('emem.isaac', 'stand_in_username')
             ->type('emem.isaac', 'supervisor_username')
             ->press('Submit')
             ->seeInDatabase('leave_requests', 
             [
                'created_by' => $user->username,
                'days_requested' => 5,
                'reason' => 'Testing @ ' . $time,
                'leave_type' => 'Annual',
             ]);
    }

    private static function create_leave_request($user, $type, $days, $start_date, $end_date){
        $r = new \App\LeaveRequest();
        $r->name = $user->name;
        $r->date_created = \Carbon\Carbon::now();
        $r->leave_type = $type;
        $r->created_by = $user->username;
        $r->start_date = $start_date;
        $r->end_date = $end_date;
        $r->supervisor_username = "emem.isaac";
        $r->stand_in_username = "emem.isaac";
        $r->days_requested = $days;
        $r->reason = "Testing @ . " . time();
        $r->save();
        return $r;
    }

    public function test_users_can_delete_a_leave_request()
    {
        // $user = User::where('username','emem.isaac')->first(); //factory(User::class)->create();
        $user = factory(User::class)->create(); //User::where('username','emem.isaac')->first(); 

        $requestOne =static::create_leave_request($user, 'Annual', 5, Carbon::now(), Carbon::now()->addDays(4));
        $requestTwo =static::create_leave_request($user, 'Annual', 5, Carbon::now(), Carbon::now()->addDays(4));

        $this->actingAs($user)
             ->visit('/tasks')
             ->see($requestOne->reason)
             ->see($requestTwo->reason)
             ->press('delete-leave-request-'.$requestOne->id)
             ->dontSee($taskOne->reason)
             ->see($taskTwo->reason);
    }


    public function test_users_cant_view_tasks_of_other_users()
    {
        $userOne = factory(User::class)->create();
        $userTwo = factory(User::class)->create();

        $requestByUserOne =static::create_leave_request($userOne, 'Annual', 5, Carbon::now(), Carbon::now()->addDays(4));
        $requestByUserTwo =static::create_leave_request($userTwo, 'Annual', 5, Carbon::now(), Carbon::now()->addDays(4));
       
        $this->actingAs($userOne)
             ->visit('/home')
             ->see($requestByUserOne->reason)
             ->dontSee($requestByUserTwo->reason);
    }


    public function test_users_cant_delete_applications_by_other_users()
    {
        $this->withoutMiddleware();

        $userOne = factory(User::class)->create();
        $userTwo = factory(User::class)->create();

        $requestByUserOne =static::create_leave_request($userOne, 'Annual', 5, Carbon::now(), Carbon::now()->addDays(4));
        $requestByUserTwo =static::create_leave_request($userTwo, 'Annual', 5, Carbon::now(), Carbon::now()->addDays(4));
       
        $this->actingAs($userOne)
             ->delete('/requests/'.$requestByUserTwo->id)
             ->assertDontSeeText($requestByUserTwo->reason);
             //  ->assertSeeText("You cannot delete another's application");
    }
}
