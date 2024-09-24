@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="container">
    <div class="row">
        @if(Session::has('flash_message'))
      <div class="alert alert-info">
        {{ Session::get('flash_message')}}
      </div>
      @endif
      @if(isset($notification))
      <div class="alert alert-default">
        <strong>Info!</strong>
        {{$notification}}
         <a href="/complete-your-profile" class="link">Update Profile</a>
      </div>
      @endif
      @if(isset($hrnotification))
      <div class="alert alert-info">
        {{$hrnotification}}
      </div>
      @endif
    </div>
    @if(Auth::user()->hasRole("HR") || Auth::user()->hasRole("Admin"))
    <!--HR Stats Dashboard-->
    <div class="row">
            <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>Statistics</h1>
                    </div>

                    <div class="panel-body">
                        <table class="table table-striped task-table">

                          <thead>
                              @foreach($stats as $type)
                                  <th class="text-center" style="background-color:#1A5276; color:white; border-right:solid 2px white; max-width:70px;">
                                      {{$type->type}}
                                  </th>
                              @endforeach
                          </thead>
                          <tbody>
                            <tr>
                              @foreach($stats as $type)
                                  <td class="text-center" style="background-color:#1A5276; color:white; border-right:solid 2px white; max-width:70px;">
                                      <div style="font-size:50px; margin-top:10px;">{{$type->count}}</div>
                                      <br/>
                                  </td>
                              @endforeach
                            </tr>
                          </tbody>
                        </table>
                    </div>
            </div>
    </div>
    @endrole
    <!--End of HR Stats Dashboard-->

    <!--Leave Balance Dashboard -->
    <div class="row">
            <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>Leave days left</h1>
                    </div>

                    <div class="panel-body">
                        <table class="table table-striped task-table">

                          <thead>
                              @foreach($leavetypes as $type)
                                  <th class="text-center" style="background-color:#1A5276; color:white; border-right:solid 2px white; max-width:70px;">
                                      {{$type->name}}
                                  </th>
                              @endforeach
                              <th class="text-center hidden">
                              Available
                              </th>
                          </thead>
                          <tbody>
                            <tr>
                              @foreach($leavetypes as $type)
                                  <td class="text-center" style="background-color:#1A5276; color:white; border-right:solid 2px white; max-width:70px;">
                                      <div style="font-size:30px; padding-bottom:10px;">{{$type->balance}}</div>
                                      @if($type->enabled())
                                          <a class="btn btn-default" style="width:100%; margin-bottom:0px;" href="/new-request?name={{$type->name}}">Apply</a>
                                      @else
                                          <a class="btn btn-default" style="width:100%; margin-bottom:0px;" {{$type->balance > 0 && $type->enabled() ? '' : 'disabled'}} href="#">Apply</a>
                                      @endif
                                      <br/>
                                  </td>
                              @endforeach
                              <td class="text-center hidden">
                                <div style="padding-top:10px;font-size:50px; font-weight:bold; vertically-align:center;">{{ $totalBalance}}</div>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                    </div>
            </div>
    </div>
    <!--End of Leave Balance Dashboard -->

    <!-- My Requests-->
    <div class="row">
            <!-- My Leave Requests -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>{{$mytitle}} <span class="pull-right">{{$countMyApplications}}<span></h1>
                    </div>
                    <div class="panel-body">
	                    @if ($leaverequests && $leaverequests->isEmpty() == false)
	                        <table class="table table-striped task-table">
	                            <thead>
	                                <th>#</th>
	                                <th>Date</th>
	                                <th>Type</th>
	                                <th>Starting</th>
	                                <th>Ending</th>
                                    <th class="text-center">Before</th>
	                                <th class="text-center">Request</th>
	                                <th class="text-center">After</th>
	                                <th>Status</th>
	                                <th style="white-space:nowrap;">&nbsp;</th>
	                            </thead>
	                            <tbody>
	                                @foreach ($leaverequests as $leavereq)
	                                    <tr class="{{ $leavereq->hr_response == true ? 'success' : ''}}">
                                            <td class="table-text">
	                                    		<span>{{ $leavereq->id }}</span>
	                                    	</td>
                                            <td class="table-text">
	                                        	<span>
	                                        		{{ Carbon\Carbon::parse($leavereq->created_at)->toDateTimeString() }}
	                                        	</span>
	                                      </td>
                                          	<td class="table-text">
	                                    		<span>{{ $leavereq->leave_type }}</span>
	                                    	</td>
	                                      <td class="table-text">
	                                        	<span>
	                                        		{{ Carbon\Carbon::parse($leavereq->start_date)->toFormattedDateString() }}
	                                        	</span>
	                                      </td>

	                                      <td class="table-text">
	                                        	<span>
	                                        		{{ Carbon\Carbon::parse($leavereq->end_date)->toFormattedDateString() }}
	                                        	</span>
	                                      </td>

	                                    	<td class="text-center">
	                                        	<span class="{{ $leavereq->days_left ==0 ? 'danger' : ''}}">
	                                        		{{ $leavereq->days_left}} {{$leavereq->days_left >1 ? 'days' : 'day' }}
                                                    @if($leavereq->increment)
                                                        (+{{ $leavereq->increment}})
                                                    @endif
	                                        	</span>
	                                      </td>
                                          <td class="text-center">
                                                <span>
                                                    {{$leavereq->days_requested}} {{$leavereq->days_requested >1 ? 'days' : 'day' }}
                                                </span>
                                            </td>
                                          <td class="text-center">
	                                        	<span class="{{ $leavereq->days_left ==0 ? 'danger' : ''}}">
	                                        		{{ $leavereq->days_left-$leavereq->days_requested}} {{$leavereq->days_left-$leavereq->days_requested >1 ? 'days' : 'day' }}
	                                        	</span>
	                                      </td>
	                                      <td class="table-text">
                                                <span>
                                                    {{$leavereq->getStatus()}}
                                                </span>
	                                        </td>

	                                        <!-- Leave Request Delete Button -->
	                                        <td style="white-space: nowrap;">

	                                            <form action="{{url('requests/' . $leavereq->id)}}" method="POST">
	                                                {{ csrf_field() }}
	                                                {{ method_field('DELETE') }}
                                                <a href="{{ url('view?applicationId=' . $leavereq->id)}}" class="btn btn-default">View</a>
                                                @if($leavereq->recallable())
                                                <a href="{{ url('refunds/new?id=' . $leavereq->id)}}" class="btn btn-default">Refund</a>
                                                @endif
                                                @if($leavereq->allowEdit())
                                                <a href="{{ url('edit?id=' . $leavereq->id)}}" class="btn btn-default">Edit</a>
                                                @endif

	                                                @if($leavereq->allowDelete())
                                                    <div class="delete" data-id="{{ $leavereq->id }}">
                                                        <textarea hidden data-id="{{ $leavereq->id }}" placeholder="Reason"></textarea>
                                                        <button type="button"  name="delete-leave-request-{{ $leavereq->id }}" id="delete-leave-request-{{ $leavereq->id }}" class="btn btn-danger">
                                                        <i class="fa fa-btn fa-trash"></i>Delete
                                                        </button>
                                                    </div>
	                                                @endif
	                                            </form>
	                                        </td>
	                                    </tr>
	                                @endforeach
	                            </tbody>
	                        </table>
	                    @else
	                        <p>You have no leave applications</p>
	                    @endif
                    </div>
                </div>
    </div>

    <!--Offline Leave Applications -->
    <div class="rows">

            <!-- Current Offline Leave Approvals -->
            @if ($offline_approvals && $offline_approvals->isEmpty() == false)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>Offline Approvals</h1>
                    </div>

                    <div class="panel-body">
                        <table class="table table-striped holiday-table">
                            <thead>
                              <th>Applicant</th>
                              <th>Leave Type</th>
                              <th>Days</th>
                              <th>Approved by</th>
                              <th>Date Approved</th>
                              <th>Request Id</th>
                              <th>&nbsp;</th>
                            </thead>
                            <tbody>
                                @foreach ($offline_approvals as $leaveApproval)
                                    <tr>
                                      <td>{{$leaveApproval->applicant_username}}</td>
                                      <td>{{$leaveApproval->leave_type}}</td>
                                      <td class="table-text"><div>{{ $leaveApproval->days }}</div></td>
                                      <td>{{$leaveApproval->approved_by}}</td>
                                      <td>{{$leaveApproval->date_approved}}</td>
                                      <td>{{$leaveApproval->leave_request_id}}</td>
                                      <!-- Holiday Delete Button -->
                                      <td>

                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
  <!-- MyLeave Refund Requests-->
    <div class="row">
            <!-- My Leave Refund Requests -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>{{$refundTitle}} <span class="pull-right">{{$countMyRefundApplications}}<span></h1>
                    </div>
                    <div class="panel-body">
	                    @if ($refundApplications && !$refundApplications->isEmpty())
	                        <table class="table table-striped task-table">
	                            <thead>
	                                <th>Date</th>
	                                <th>Type</th>
	                                <th>Date Resumed</th>
	                                <th>Reason</th>
                                    <th class="text-center">Request</th>
	                                <th>Status</th>
	                                <th>&nbsp;</th>
	                            </thead>
	                            <tbody>
	                                @foreach ($refundApplications as $refund)
	                                    <tr class="{{ $refund->supervisor_response == true ? 'success' : ''}}">
	                                        <td class="table-text">
	                                        	<span>
	                                        		{{ Carbon\Carbon::parse($refund->created_at)->toFormattedDateString() }}
	                                        	</span>
	                                      </td>
                                          	<td class="table-text">
	                                    		<span>{{ $refund->leave_type }}</span>
	                                    	</td>
	                                      <td class="table-text">
	                                        	<span>
	                                        		{{ Carbon\Carbon::parse($refund->date)->toFormattedDateString() }}
	                                        	</span>
	                                      </td>
                                          <td class="table-text">
	                                    		<span>{{ $refund->reason }}</span>
	                                    	</td>
	                                    	<td class="text-center">
	                                        	<span class="{{ $refund->days_credited ==0 ? 'danger' : ''}}">
	                                        		{{ $refund->days_credited}} {{$refund->days_credited >1 ? 'days' : 'day' }}
	                                        	</span>
	                                      </td>
	                                      <td class="table-text">
                                                <span>
                                                    {{$refund->getStatus()}}
                                                    {{$refund->supervisor_response_reason ? '. Reason: ' . $refund->supervisor_response_reason : ''}}
                                                </span>
	                                        </td>

	                                        <td>
	                                            <form action="{{url('refunds/' . $refund->id)}}" method="POST">
	                                                {{ csrf_field() }}
	                                                {{ method_field('DELETE') }}
	                                                @if($refund->supervisor_response === null && $refund->applicant_username==Auth::user()->username)
	                                                <button type="submit" id="delete-leave-request-{{ $refund->id }}" class="btn btn-danger">
	                                                    <i class="fa fa-btn fa-trash"></i>Delete
	                                                </button>
	                                                @endif
	                                            </form>
	                                        </td>
	                                    </tr>
	                                @endforeach
	                            </tbody>
	                        </table>
	                    @else
	                        <p>You have not applied for leave refund</p>
	                    @endif
                    </div>
                </div>
    </div>

    <!-- Leave Refund Requests for my attention-->
    <div class="row">
            <!-- Leave Refund Requests for my attention-->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>{{$refundsPendingApprovalTitle}} <span class="pull-right">{{$countRefundsPendingApproval}}<span></h1>
                    </div>
                    <div class="panel-body">
	                    @if ($refundsPendingApproval && !$refundsPendingApproval->isEmpty())
	                        <table class="table table-striped task-table">
	                            <thead>
	                                <th>Date</th>
	                                <th>Type</th>
	                                <th>Date Resumed</th>
	                                <th>Reason</th>
                                    <th class="text-center">Request</th>
	                                <th>Status</th>
	                                <th>&nbsp;</th>
	                            </thead>
	                            <tbody>
	                                @foreach ($refundsPendingApproval as $refund)
	                                    <tr class="{{ $refund->supervisor_response == true ? 'success' : ''}}">
	                                        <td class="table-text">
	                                        	<span>
	                                        		{{ Carbon\Carbon::parse($refund->created_at)->toFormattedDateString() }}
	                                        	</span>
	                                      </td>
                                          	<td class="table-text">
	                                    		<span>{{ $refund->leave_type }}</span>
	                                    	</td>
	                                      <td class="table-text">
	                                        	<span>
	                                        		{{ Carbon\Carbon::parse($refund->date)->toFormattedDateString() }}
	                                        	</span>
	                                      </td>
                                          <td class="table-text">
	                                    		<span>{{ $refund->reason }}</span>
	                                    	</td>
	                                    	<td class="text-center">
	                                        	<span class="{{ $refund->days_credited ==0 ? 'danger' : ''}}">
	                                        		{{ $refund->days_credited}} {{$refund->days_credited >1 ? 'days' : 'day' }}
	                                        	</span>
	                                      </td>
	                                      <td class="table-text">
                                                <span>
                                                    {{$refund->getStatus()}}
                                                </span>
	                                        </td>

	                                        <td>
	                                                @if($refund->supervisor_response === null && $refund->supervisor_username==Auth::user()->username)
                                                        <a href="{{ url('refunds/view?id=' . $refund->id)}}" class="btn btn-default">Process</a>
                                                    @endif

	                                        </td>
	                                    </tr>
	                                @endforeach
	                            </tbody>
	                        </table>
	                    @else
	                        <p>Nothing for your attention.</p>
	                    @endif
                    </div>
                </div>
    </div>
    <!--Pending Approval -->
     <div class="row">

                @if (($pendingApproval && $pendingApproval->isEmpty() == false) or ($pendingSApproval && $pendingSApproval->isEmpty() == false) or ($pendingHRApproval && $pendingHRApproval->isEmpty() == false))
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>{{$pendingTitle}} <span class="pull-right">{{$countPending}}</span></h1>
                    </div>

                    <div class="panel-body">
                        <p>Nothing for your attention</p>
                    </div>
                </div>
                @endif

                <!--Pending Stand In -->
                    @if ($pendingApproval->isEmpty() == false)
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h1>Stand-in Requests<span class="pull-right">{{$countPendingStandin}}</span></h1>
                        </div>

                        <div class="panel-body">
                            <table class="table table-striped task-table">
	                            <thead>
	                                <th>Date</th>
	                                <th>Applicant</th>
	                                <th>Type</th>
	                                <th class="text-center">Days Requested</th>
                                    <th class="text-center">Days Available</th>
	                                <th>Status</th>
	                                <th>&nbsp;</th>
	                            </thead>
	                            <tbody>
	                                @foreach ($pendingApproval as $leavereq)
	                                    <tr class="{{ $leavereq->supervisor_response == true ? 'success' : ''}}">
	                                        <td class="table-text">
	                                        	<span>
                                                {{ Carbon\Carbon::parse($leavereq->created_at)->toFormattedDateString() }}
	                                        	</span>
	                                      </td>
                                          	<td class="table-text">
	                                    		<span>{{ $leavereq->name}} | {{ $leavereq->applicant()->designation }} | {{ $leavereq->applicant()->department }}</span>
	                                    	</td>
	                                      <td class="table-text">
	                                        	<span>
                                                {{ $leavereq->leave_type }}
	                                        	</span>
	                                      </td>
                                          <td class="text-center">
	                                        	<span>
                                                {{ $leavereq->days_requested }}
	                                        	</span>
	                                      </td>
                                          <td class="text-center">
	                                    		<span>{{ $leavereq->days_left }}</span>
	                                    	</td>

	                                      <td class="table-text">
                                                <span>
                                                    {{$leavereq->getStatus()}}
                                                </span>
	                                        </td>

	                                        <td>
                                                <a href="{{ url('/view?applicationId=' . $leavereq->id)}}" class="btn btn-default">Process</a>
	                                        </td>
	                                    </tr>
	                                @endforeach
	                            </tbody>
	                        </table>
                        </div>
                    </div>
                    @endif


                <!--Pending Supervisor-->
                @if ($pendingSApproval && $pendingSApproval->isEmpty() == false)
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h1>{{$pendingTitle}} as a Supervisor<span class="pull-right">{{$countPendingSupervisor}}</span></h1>
                        </div>

                        <div class="panel-body">
                        <table class="table table-striped task-table">
	                            <thead>
	                                <th>Date</th>
	                                <th>Applicant</th>
	                                <th>Type</th>
	                                <th class="text-center">Days Requested</th>
                                    <th class="text-center">Days Available</th>
	                                <th>Status</th>
	                                <th>&nbsp;</th>
	                            </thead>
	                            <tbody>
	                                @foreach ($pendingSApproval as $leavereq)
	                                    <tr class="{{ $leavereq->supervisor_response == true ? 'success' : ''}}">
	                                        <td class="table-text">
	                                        	<span>
                                                {{ Carbon\Carbon::parse($leavereq->created_at)->toFormattedDateString() }}
	                                        	</span>
	                                      </td>
                                          	<td class="table-text">
	                                    		<span>{{ $leavereq->name}} | {{ $leavereq->applicant()->designation }} | {{ $leavereq->applicant()->department }}</span>
	                                    	</td>
	                                      <td class="table-text">
	                                        	<span>
                                                {{ $leavereq->leave_type }}
	                                        	</span>
	                                      </td>
                                          <td class="table-text text-center">
	                                        	<span>
                                                {{ $leavereq->days_requested }}
	                                        	</span>
	                                      </td>
                                          <td class="table-text text-center">
	                                        	<span >
                                                    {{ $leavereq->days_left }}
                                                </span>
	                                    	</td>

	                                      <td class="table-text">
                                                <span>
                                                    {{$leavereq->getStatus()}}
                                                </span>
	                                        </td>

	                                        <td>
                                                <a href="{{ url('/view?applicationId=' . $leavereq->id)}}" class="btn btn-default">Process</a>
	                                        </td>
	                                    </tr>
	                                @endforeach
	                            </tbody>
	                        </table>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

</div>
@endsection
@section('script')
<script>
    document.addEventListener('DOMContentLoaded', event => {
        const taDeleteReasons = document.querySelectorAll('textarea');
        if(taDeleteReasons != null){
            taDeleteReasons.forEach(taDeleteReason => {
                taDeleteReason.setAttribute('hidden', 'true');
                const div = taDeleteReason.parentElement;
                const btnDel = div.querySelector("#delete-leave-request-" + taDeleteReason.dataset.id);
                // console.log(btnDel);
                if(btnDel){
                    btnDel.setAttribute('type', 'button');
                    btnDel.addEventListener('click', event => {
                        console.log("Ädding click eent to bnt start delete");
                        taDeleteReason.removeAttribute('hidden');
                    });
                }
                taDeleteReason.addEventListener('keypress', event => {
                    if(taDeleteReason.value != null && taDeleteReason.value.length > 4){
                        console.log(taDeleteReason.value);
                        btnDel.removeAttribute('hidden');
                        btnDel.setAttribute('type', 'submit');
                    }
                });
            });
        }
    })

</script>
@endsection
