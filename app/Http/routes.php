<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

use Illuminate\Http\Request;



header('Access-Control-Allow-Origin: *');
header( 'Access-Control-Allow-Headers: Authorization, Content-Type' );

Route::group(['middleware' => ['web']], function () {
    /*
    |   Custom auth routes
    */
    Route::get('/sso', "SSOController@handleSSO");
    Route::get('/logout', 'SSOController@logOut');

    // Route::get('/', "Auth\LoginController@welcome");
    // Route::post('logout', 'Auth\LoginController@logout');
    // Route::get('/logout', 'Auth\LoginController@logout')->name('logout');
    // Route::post('login', "Auth\LoginController@attemptLogin")->name('login');
    // Route::get('/login', "Auth\LoginController@welcome")->name('welcome');

    /*End of Custom auth routes*/

    Route::get('/import-staff', 'UserController@importProfiles');
    Route::get('/users/search', 'UserController@listUsers');
    Route::delete('/users/delete/{id}', 'UserController@destroy');
    Route::get('/approvals/search', 'LeaveApprovalsController@index');
    Route::get('/import-approvals', 'LeaveRequestsController@importApprovals');

    Route::auth();
    Route::get('/update-staff-id', 'UserController@updateStaffNumber');
    Route::get('/home', 'LeaveRequestsController2@home');
    Route::get('/change-log', 'ChangeLogController@index');
    Route::get('/applications', 'LeaveRequestsController@applications');
    Route::get('/applications/search', 'LeaveRequestsController@applications');
    Route::post('/applications/search', 'LeaveRequestsController@applications');
    Route::get('/my-requests', 'LeaveRequestsController@myrequests');
    Route::get('/new-request', 'LeaveRequestsController2@newrequest');
    Route::get('/edit', 'LeaveRequestsController2@edit');
    Route::post('/new-request', 'LeaveRequestsController2@store');
    Route::get('/apply-for', 'LeaveRequestsController2@applyForGet');
    Route::post('/apply-for', 'LeaveRequestsController2@applyFor');
    Route::get('/pending-supervisor-approval', 'LeaveRequestsController@pendingSupervisorApproval');
    Route::delete('/requests/{leaverequest}', 'LeaveRequestsController2@destroy');
    Route::post('/supervisor-approve-request/{leaverequest}', 'LeaveRequestsController2@approve');
    Route::post('/supervisor-deny-request/{leaverequest}', 'LeaveRequestsController2@deny');
    Route::get('/leave/details/{leaverequest}', 'LeaveRequestsController2@show');

    Route::get('/fixBalances', 'LeaveRequestsController2@FixApplicationBalances'); //
    Route::get('/holidaysBetween', 'LeaveRequestsController@getWorkingDaysBetweenTwoDatesUrl'); //
    Route::get('/fixBadDaysLeft', 'LeaveRequestsController2@fixLeaveDaysLeftUrl'); //
    Route::post('/fixBadDaysLeft', 'LeaveRequestsController2@fixLeaveDaysLeftUrl'); //

    Route::get('/test', 'LeaveRequestsController2@test'); //


    Route::get('/get-resumption-date', 'LeaveRequestsController2@getResumptionDate');
    Route::post('/get-resumption-date-1', 'LeaveRequestsController2@getResumptionDate');

    Route::get('/stand-in-requests', 'LeaveRequestsController@standInRequestsToMe');
    Route::post('/accept-stand-in-request/{leaverequest}', 'LeaveRequestsController2@acceptStandInRequest');
    Route::post('/decline-stand-in-request/{id}', 'LeaveRequestsController2@declineStandInRequest');

    Route::get('/pending-hr-approval', 'LeaveRequestsController@pendingHRApproval');
    Route::post('/hr-approve/{leaverequest}', 'LeaveRequestsController2@hrApprove');
    Route::post('/hr-deny/{leaverequest}', 'LeaveRequestsController2@hrDeny');

    Route::get('/pending-md-approval', 'LeaveRequestsController@pendingMDApproval');
    Route::post('/md-approve/{leaverequest}', 'LeaveRequestsController@mdApprove');
    Route::post('/md-deny/{leaverequest}', 'LeaveRequestsController@mdDeny');
    Route::get('/view', 'LeaveRequestsController2@view');
    Route::get('/documents/download', 'LeaveRequestsController@download');
    Route::get('/user-guide', 'LeaveRequestsController@view');
    Route::get('/error', 'ErrorController@index');


    Route::get('complete-your-profile', 'ProfileController@completeProfileGet');
    Route::get('create-profile', 'ProfileController@completeProfileGet');
    Route::post('complete-your-profile', 'ProfileController@completeProfilePost');

    Route::get('/holidays', 'HolidaysController@index');
    Route::get('/holidays/new', 'HolidaysController@create');
    Route::post('/holidays/new', 'HolidaysController@store');
    Route::delete('/holidays/{holiday}', 'HolidaysController@destroy');


    Route::get('/regions', 'RegionsController@index');
    Route::get('/regions/new', 'RegionsController@create');
    Route::post('/regions/new/', 'RegionsController@store');
    Route::delete('/regions/{region}', 'RegionsController@destroy');

    Route::get('/areaoffices', 'AreaOfficesController@index');
    Route::get('/areaoffices/new', 'AreaOfficesController@create');
    Route::post('/areaoffices/new/', 'AreaOfficesController@store');
    Route::delete('/areaoffices/{holiday}', 'AreaOfficesController@destroy');


    Route::get('/leaveentitlements', 'LeaveEntitlementsController@index');
    Route::get('/leave-rules', 'LeaveEntitlementsController@index');
    Route::get('/leaveentitlements/new', 'LeaveEntitlementsController@create');
    Route::post('/leaveentitlements/new/', 'LeaveEntitlementsController@store');
    Route::delete('/leaveentitlements/{holiday}', 'LeaveEntitlementsController@destroy');

    Route::get('/leaveapprovals', 'LeaveApprovalsController@index');
    Route::get('/leaveapprovalsOnlineAll', 'LeaveApprovalsController@onlineOnly');
    Route::get('/leaveapprovals/new', 'LeaveApprovalsController@create');
    Route::post('/leaveapprovals/new/', 'LeaveApprovalsController@store');
    Route::delete('/leaveapprovals/{holiday}', 'LeaveApprovalsController@destroy');

    Route::get('/designations', 'DesignationsController@index');
    Route::get('/designations/new', 'DesignationsController@create');
    Route::post('/designations/new/', 'DesignationsController@store');
    Route::delete('/designations/{holiday}', 'DesignationsController@destroy');

    Route::get('users', 'UserController@listUsers');
    Route::get('all-users', 'UserController@listAllUsers');
    Route::get('adAccountsWhoHaveNeverLoggedIn', 'UserController@adAccountsWhoHaveNeverLoggedIn');
    Route::get('accountsNotInAD', 'UserController@accountsNotInAD');
    Route::get('usersWhoHaveNeverAppliedForLeave', 'UserController@usersWhoHaveNeverAppliedForLeave');
    Route::get('usersWhoHaveNeverBeenApprovedLeave', 'UserController@usersWhoHaveNeverBeenApprovedLeave');
    Route::get('users-with-incomplete-profiles', 'UserController@usersWithIncompleteProfiles');
    Route::post('users/roles/update/{user_id}', 'UserController@updateRoles');

    Route::get('get-units', 'OfficesController@getUnits');
    Route::get('offices', 'OfficesController@index');
    Route::get('offices/new', 'OfficesController@create');
    Route::post('offices/new', 'OfficesController@store');
    Route::post('offices/{id}', 'OfficesController@destroy');
    Route::get('get-area-offices', 'AreaOfficesController@getAreaOffices');

    Route::get('/refunds', 'RefundsController@index');
    // Route::get('/refunds/{username}', 'RefundsController@byUser');
    Route::get('/refunds/view/{id}', 'RefundsController@view');
    Route::get('/refunds/view', 'RefundsController@view');
    Route::post('/refunds/dismiss/{id}', 'RefundsController@dismiss');
    Route::post('/refunds/dismiss', 'RefundsController@dismiss');
    Route::post('/refunds/confirm/{id}', 'RefundsController@confirm');
    Route::post('/refunds/confirm', 'RefundsController@confirm');
    Route::get('/refunds/new/{id}', 'RefundsController@create');
    Route::get('/refunds/new', 'RefundsController@create');
    Route::post('/refunds/new/{id}', 'RefundsController@store');
    Route::delete('/refunds/{id}', 'RefundsController@destroy');

    Route::get('/leave-history', 'HistoryController@index');
    Route::get('/get-hr-to-update-your-profile', 'LeaveRequestsController2@getHrToUpdateYourProfile');
    Route::get('/get-outstanding-requests-approved', 'LeaveRequestsController2@getYourPendingApplicationsApproved');

    // Route::post('/refunds/new/{id}', 'RefundsController@store');
    // Route::delete('/refunds/{id}', 'RefundsController@destroy');
    Route::resource('/deleteable', "DeleteableLeaveController");
    Route::resource('/reverse', "ActionReversalController");
});

Route::group(['middleware' => ['api']], function () {
    Route::get('/api/dashboard', "Api\ApiController@myAedcDashboard");
    // Route::get('/api/dashboard', function (Request $request){
    //     return response()->json(['token' => $request->token], 200);
    // });
});