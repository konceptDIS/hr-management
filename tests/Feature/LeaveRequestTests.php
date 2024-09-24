<?php

namespace Tests\Feature;

use App\User;
use App\LeaveRequest;
use Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LeaveRequestTests extends TestCase
{
    use DatabaseTransactions;
    
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }

    public function test_i_am_redirect_to_login_if_i_try_to_view_my_dashboard_without_logging_in()
    {
        $this->visit('/home')->see('Login');
    }
}



