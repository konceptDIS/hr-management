<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\LeaveRequest;
use Auth;
use App\User;

class NewHRPleaseApproveRequest extends Mailable
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
    * @var App\User
    */
    public $approver;

    /**
    * The Applicant User Instance
    *
    * @var App\User
    */
    public $applicant;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(LeaveRequest $leave_request, $applicant, $approver)
    {
        $this->leave_request = $leave_request;
        $this->approver= $approver;
        $this->applicant =$applicant;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.hr.approve')->subject('New Leave Request awaiting HR Clearance');
    }
}
