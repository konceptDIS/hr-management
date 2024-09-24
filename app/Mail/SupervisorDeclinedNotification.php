<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\LeaveRequest;
use Auth;
use App\User;

class SupervisorDeclinedNotification extends Mailable
{
    use Queueable, SerializesModels;

    /*
    * The Leave Request Instance
    *
    * @var LeaveRequest
    */
    public $leave_request;

    /**
    * The Approver User Instance
    *
    * @var User
    */
    public $approver;

    /**
    * The Applicant User Instance
    *
    * @var User
    */
    public $applicant;

    /**
     * Create a new message instance.
     *
     * @return void
     */
     public $to;
     /**
      * Create a new message instance.
      *
      * @return void
      */
     public function __construct(LeaveRequest $leave_request, $applicant, $approver, $to)
     {
         $this->leave_request = $leave_request;
         $this->approver= $approver;
         $this->applicant =$applicant;
         $this->to->$to;
     }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.notifications.supervisordeclined')->subject('FYI - Supervisor Declined!');
    }
}
