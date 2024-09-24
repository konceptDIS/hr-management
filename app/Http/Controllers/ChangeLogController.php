<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChangeLogController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }
    //
    public function index(){
        $changes = [];
        $changes[0] = new Change("February 2, 2018", "Leave History", "The Leave History page provides a detailed explanation as to how your leave balance is calculated");
        $changes[1] = new Change("February 2, 2018", "Leave Balance", "10 days carry over has been restricted to users who applied online last year. For the software to carry over leave days, the user has to apply online or ensure that their paper applications are uploaded.");
        $changes[2] = new Change("February 6, 2018", "Profile Page", "Limit the information on profile page to NameStaff IDAnnual LeaveDate of employmentGenderRegionCadre.");
        $changes[3] = new Change("February 6, 2018", "Application Form", "At times the application is unable to validate supervisor and stand in username. When that happens the previous behavior was to prevent the submission of the application. Now, the form will give the user an option to disable the validation. This eliminates the frustration experienced by users who know that the usernames they provided are accurate.");
        $changes[4] = new Change("February 7, 2018", "Reports", "The following reports have been built in: Users not in Active DirectoryUsers who have never logged inUsers who have never been approved leaveUsers who have never applied for leave");
        $changes[5] = new Change("February 7, 2018", "Apply For", "This feature allows HR to apply for leave in behalf of users who have difficulty applying online or who already applied on paper. This feature will really help to keep the leave database accurate.");
        $changes[6] = new Change("February 7, 2018", "Apply For", "By enabling the backdating feature, HR can now upload old paper applications");
        $changes[7] = new Change("February 19, 2018", "Last Login", "Last Login date has been added to the user model, this field is updated every time a user logs in, this will help us keep track of which users are using the leave application and will help towards removing invalid user accounts from the leave application");
        $changes[8] = new Change("February 19, 2018", "Apply For", "A bug in the apply for feature, that prevented user feedback was fixed for non admins");
        $changes[9] = new Change("March 19, 2018", "Core", "A substantial overhaul of the functions that calculate days taken, entilement and balance. The integrity algorithm now works on real time data and has been revamped, similar changes have been made to the Leave History page. We are hoping that this update will put paid to the occassional wrong balance issues.");
        $changes[10] = new Change("March 22, 2018", "Apply", "Fixed a bug in the apply process which used last years balance in determining max days avaialable.");
        $changes[11] = new Change("March 22, 2018", "View", "Ensured that the most recent state of a leave request is displayed, when viewing an application.");
        $changes[12] = new Change('March 29, 2018', 'Core', 'Deployed a single new harmonized, logical and easy to maintain system for leave balance calculation and reporting. Plus better feedback to users on how their leave balance is arrived at');
        $changes[13] = new Change('April 6, 2018', 'View', 'Caught an error that occures when attempting to view a deleted leave request');
        $changes[14] = new Change('April 6, 2018', 'Grade, Grade Level', 'Adding Grade and Grade level objects to allow changes to user grade level and its corresponding leave entitlement without affecting the accuracy past leave allocation and balance calculations');
        $changes[15] = new Change('April 6, 2018', 'Error handling', 'A friendly error page will be displayed henceforth');
        $changes[16] = new Change('April 9, 2018', 'Home page', 'Swapped positions of the logo/name with the login button');
        // $changes[17] = new Change('April 13, 2018', 'Leave Cost', 'How much do you loose when you do not go for leave? How much does your leave cost the organization? These details will be availabled once this module is fully operational');
        $changes[17] = new Change('April 16, 2018', 'Core', "Negative balances are now brought forward");
        $changes[18] = new Change('June 12, 2018', 'Holiday', "Holidays can no longer be deleted. When a holiday is added, all affected requests are adjusted. Your boss, and HR will be notified");
        $changes[19] = new Change('June 12, 2018', 'Requests', "Now you can delete an approved request, provided it hasn't commenced. Your boss, and HR will be notified");
        return view('change-log.index', compact('changes'));
    }
}

class Change{

    public function __construct($date, $name, $description){
        $this->date = $date;
        $this->name = $name;
        $this->description = $description;
    }
    public $date;
    public $name;
    public $description;
}