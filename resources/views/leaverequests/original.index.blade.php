@extends('layouts.app')

{{--dd($leavetypes)--}}
@section('content')
    <div class="container">

        <!------------------------- Leave days left ------------------->
        <div class="row">
            <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>Leave days left</h1>
                    </div>

                    <div class="panel-body">
                        <table class="table table-striped task-table">
                
                        <thead>
                            @foreach($leavetypes as $type)            
                                <th class="text-center">
                                    {{$type->name}}
                                </th>
                            @endforeach  
                            <th class="text-center">
                            Total
                            </th>      
                        </thead>
                        <tbody> 
                            @foreach($leavetypes as $type)            
                                <td class="text-center">
                                    {{$type->balance}}
                                </td>
                            @endforeach                       
                            <td class="text-center">
                            {{ $totalBalance}}
                            </td>
                        </tbody>
                        </table>
                    </div>
            </div>
        </div>

        <!-------------------------- My Requests ------------------------>
        <div class="row">            
            <!-- My Leave Requests -->            
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>{{$mytitle}}</h1>
                    </div>
                    <div class="panel-body">
                    @if (count($leaverequests) > 0)
                        <table class="table table-striped task-table">
                            <thead>
                                <th>Name</th>
                                <th>Section</th>
                                <th>Type</th>
                                <th>Days</th>
                                <th>Starting</th>
                                <th>Ending</th>
                                <th>Days Left</th>
                                <th>Status</th>
                                <th>&nbsp;</th>
                            </thead>
                            <tbody>                            
                                @foreach ($leaverequests as $leavereq)                                    
                                    <tr class="{{ $leavereq->hr_response == true ? 'success' : ''}}">
                                    <td>{{ $leavereq->name}}</td>
                                    <td>{{ $leavereq->section }}</td>
                                    <td class="table-text"><div>{{ $leavereq->leave_type }}</div></td>
                                    <td class="text-center">{{$leavereq->days_requested}}</td>
                                        <td class="table-text"><div>{{ Carbon\Carbon::parse($leavereq->start_date)->toFormattedDateString() }}</div></td>
                                        <td class="table-text"><div>{{ Carbon\Carbon::parse($leavereq->end_date)->toFormattedDateString() }}</div></td>
                                        <td class="text-center">
                                        {{ $leavereq->daysLeft}}
                                        </td>
                                        <td class="table-text">
                                        @if ($leavereq->hr_response == true)
                                                <span>Approved</span>
                                            @endif
                                            @if($leavereq->stand_in_response == true)
                                                @if($leavereq->supervisor_response ==null)
                                                    @if($leavereq->supervisor_username != Auth::user()->username)
                                                        <span>Pending Supervisor Approval</span>
                                                    @elseif($leavereq->supervisor_username == Auth::user()->username)
                                                        <span>Awaiting your Approval</span>
                                                @endif
                                            @endif    
                                            @if($leavereq->supervisor_response == true && $leavereq->hr_response==null)
                                                <span>Pending HR Approval</span>
                                            @endif
                                            @if($leavereq->hr_response==true && $leavereq->md_response==null && $leavereq->md_approval_required==true)
                                                <span>Pending MD's Approval</span>
                                            @endif
                                            @elseif ($leavereq->stand_in_response == null)
                                                <span>Pending Stand in's Approval</span>
                                            @elseif ($leavereq->stand_in_response == false)
                                                <span>Stand in Declined</span>
                                            @endif    
                                        </td>
                                        <!-- Leave Request Delete Button -->
                                        <td>
                                            <form action="{{url('requests/' . $leavereq->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

                                                @if($leavereq->supervisor_response == null && $leavereq->created_by==Auth::user()->username)
                                                <button type="submit" id="delete-leave-request-{{ $leavereq->id }}" class="btn btn-danger">
                                                    <i class="fa fa-btn fa-trash"></i>Delete
                                                </button>
                                                @endif
                                            </form>
                                            </td><td>
                                            <form action="{{url('deny-request/' . $leavereq->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                
                                                @if($leavereq->stand_in_response==true &&$leavereq->supervisor_response == null &&$leavereq->supervisor_username==Auth::user()->username)
                                                <!-- Trigger the modal with a button -->
<button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal">Deny</button>

<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Reason for Denial</h4>
      </div>
      <div class="modal-body">
        <textarea col="25" rows="5" id="denial-reason" name="reason"></p>
      </div>
      <div class="modal-footer">
        <button type="submit" id="approve-leave-request-{{ $leavereq->id }}" class="btn btn-default">
                                                    <i class="fa fa-btn fa-trash"></i>Submit
                                                </button>
      </div>
    </div>

  </div>

</div>

                                                
                                                @endif
                                            </form>
                                            </td><td>
                                            <form action="{{url('approve-request/' . $leavereq->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                
                                                @if($leavereq->stand_in_response==true &&$leavereq->supervisor_response == null && $leavereq->supervisor_username==Auth::user()->username)
                                                <button type="submit" id="approve-leave-request-{{ $leavereq->id }}" class="btn btn-success d-inline" >
                                                    <i class="fa fa-btn fa-check"></i>Approve
                                                </button>
                                                @endif
                                            </form>
                                            </td><td>
                                            <form action="{{url('decline-stand-in-request/' . $leavereq->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                
                                                @if($leavereq->stand_in_username==Auth::user()->username && $leavereq->stand_in_response==null)
                                                <button type="submit" id="approve-leave-request-{{ $leavereq->id }}" class="btn btn-default">
                                                    <i class="fa fa-btn fa-trash"></i>Decline
                                                </button>
                                                @endif
                                            </form>
                                            </td><td>
                                            <form action="{{url('accept-stand-in-request/' . $leavereq->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                
                                                @if($leavereq->stand_in_username==Auth::user()->username && $leavereq->stand_in_response==null)
                                                <button type="submit" id="approve-leave-request-{{ $leavereq->id }}" class="btn btn-success d-inline" >
                                                    <i class="fa fa-btn fa-check"></i>Accept
                                                </button>
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



    <!-----------------------------------Pending Approval ---------------------------------->

        <div class="row">            
            <!-- My Leave Requests -->            
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>{{$pendingTitle}}</h1>
                    </div>

                    <div class="panel-body">
                    @if (count($pendingApproval) > 0)
                        <table class="table table-striped task-table">
                            <thead>
                                <th>Name</th>
                                <th>Section</th>
                                <th>Type</th>
                                <th>Days</th>
                                <th>Starting</th>
                                <th>Ending</th>
                                <th>Days Left</th>
                                <th>Status</th>
                                <th>&nbsp;</th>
                            </thead>
                            <tbody>                            
                                @foreach ($pendingApproval as $leavereq)
                                    <tr class="{{ $leavereq->hr_response == true ? 'success' : ''}}">
                                    <td>{{ $leavereq->name}}</td>
                                    <td>{{ $leavereq->section }}</td>
                                    <td class="table-text"><div>{{ $leavereq->leave_type }}</div></td>
                                    <td class="text-center"><span class="text-right">{{$leavereq->days_requested}}{{-- Carbon\Carbon::parse($leavereq->end_date)->diffInDays(Carbon\Carbon::parse($leavereq->start_date)) --}}</span></td>
                                        <td class="table-text"><div>{{ Carbon\Carbon::parse($leavereq->start_date)->toFormattedDateString() }}</div></td>
                                        <td class="table-text"><div>{{ Carbon\Carbon::parse($leavereq->end_date)->toFormattedDateString() }}</div></td>
                                        <td class="text-center">
                                        {{ $leavereq->daysLeft}}
                                        </td>
                                        <td class="table-text">
                                            @if ($leavereq->hr_response == true)
                                                <span>Approved</span>
                                            @endif
                                            @if($leavereq->stand_in_response == true)
                                                @if($leavereq->supervisor_response ==null)
                                                    @if($leavereq->supervisor_username != Auth::user()->username)
                                                        <span>Pending Supervisor Approval</span>
                                                    @elseif($leavereq->supervisor_username == Auth::user()->username)
                                                        <span>Awaiting your Approval</span>
                                                @endif
                                            @endif    
                                            @if($leavereq->supervisor_response == true && $leavereq->hr_response==null)
                                                <span>Pending HR Approval</span>
                                                {{$leavereq->hr_response}}
                                            @endif
                                            @if($leavereq->hr_response==true && $leavereq->md_response==null && $leavereq->md_approval_required==true)
                                                <span>Pending MD's Approval</span>
                                            @endif
                                            @elseif ($leavereq->stand_in_response == null)
                                                @if($leavereq->stand_in_username == Auth::user()->username)
                                                    <span>Will you Stand in?</span>
                                                @else
                                                    <span>Pending Stand in's Approval</span>
                                                @endif
                                            @elseif ($leavereq->stand_in_response == false)
                                                <span>Stand in Declined</span>
                                            @endif
                                        </td>
                                        <!-- Leave Request Delete Button -->    
                                        <td class="">
                                        <div class="d-inline">
                                            <form class="d-inline" action="{{url('requests/' . $leavereq->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

                                                @if($leavereq->supervisor_response == null && $leavereq->created_by==Auth::user()->username)
                                                <button type="submit" id="delete-leave-request-{{ $leavereq->id }}" class="btn btn-danger">
                                                    <i class="fa fa-btn fa-trash"></i>Delete
                                                </button>
                                                @endif
                                            </form>
                                        <!-- </td><td> -->
                                        </div><div class="d-inline">
                                            <form class="d-inline" action="{{url('hr-deny/' . $leavereq->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                
                                                @if($leavereq->stand_in_response==true &&$leavereq->supervisor_response == true &&
                                                $leavereq->hr_response == null && Auth::user()->hasRole("HRApprover"))
                                                <button type="submit" id="approve-leave-request-{{ $leavereq->id }}" class="btn btn-default">
                                                    <i class="fa fa-btn fa-trash"></i>Deny
                                                </button>
                                                @endif
                                            </form>
                                        </div><div class="d-inline">
                                        <!-- </td><td>                  -->
                                            <form class="d-inline" class="" action="{{url('hr-approve/' . $leavereq->id)}}" method="POST">
                                                {{ csrf_field() }}
                                               
                                                @if(                                 $leavereq->stand_in_response==true &&$leavereq->supervisor_response == true && 
                                                $leavereq->hr_response == null &&Entrust::hasRole("HRApprover"))
                                                <button type="submit" id="approve-leave-request-{{ $leavereq->id }}" class="btn btn-success d-inline" >
                                                    <i class="fa fa-btn fa-check"></i>Approve
                                                    </button>
                                                    {{--@if($leavereq->hr_response==null)
                                                    <span>HR Response is == null</span>
                                                    @elseif($leavereq->hr_response===null)
                                                    <span>HR Response is === null</span>
                                                    @elseif($leavereq->hr_response===1)
                                                    <span>HR Response is === 1</span>
                                                    @elseif($leavereq->hr_response===0)
                                                    <span>HR Response is === 0</span>
                                                    @elseif($leavereq->hr_response==1)
                                                    <span>HR Response is == 1</span>
                                                    @elseif($leavereq->hr_response==0)
                                                    <span>HR Response is == 0</span>
                                                    @elseif($leavereq->hr_response===true)
                                                    <span>HR Response is === true</span>
                                                    @elseif($leavereq->hr_response===false)
                                                    <span>HR Response is === false</span>
                                                    @elseif($leavereq->hr_response==true)
                                                    <span>HR Response is == true</span>
                                                    @elseif($leavereq->hr_response==false)
                                                    <span>HR Response is == false</span>
                                                    @endif--}}
                                                
                                                @endif
                                            </form>
                                        <!-- </td><td> -->
                                        </div><div class="d-inline">
                                            <form class="d-inline" action="{{url('deny-request/' . $leavereq->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                
                                                @if($leavereq->stand_in_response==true &&$leavereq->supervisor_response == null &&$leavereq->supervisor_username==Auth::user()->username)
                                                <button type="submit" id="approve-leave-request-{{ $leavereq->id }}" class="btn btn-default">
                                                    <i class="fa fa-btn fa-trash"></i>Deny
                                                </button>
                                                @endif
                                            </form> 
                                        <!-- </td><td>                                        -->
                                        </div><div class="d-inline">
                                            <form class="d-inline" action="{{url('approve-request/' . $leavereq->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                
                                                @if($leavereq->stand_in_response==true &&$leavereq->supervisor_response == null && $leavereq->supervisor_username==Auth::user()->username)
                                                <button type="submit" id="approve-leave-request-{{ $leavereq->id }}" class="btn btn-success d-inline" >
                                                    <i class="fa fa-btn fa-check"></i>Approve
                                                </button>
                                                @endif
                                            </form>                                         
                                        <!-- </td><td> -->
                                        </div><div class="d-inline">
                                            <form class="d-inline" action="{{url('decline-stand-in-request/' . $leavereq->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                
                                                @if($leavereq->stand_in_username==Auth::user()->username && $leavereq->stand_in_response==null)
                                                <button type="submit" id="approve-leave-request-{{ $leavereq->id }}" class="btn btn-default">
                                                    <i class="fa fa-btn fa-trash"></i>Decline
                                                </button>
                                                @endif
                                            </form>
                                        <!-- </td><td> -->
                                        </div><div class="d-inline">
                                            <form class="d-inline" action="{{url('accept-stand-in-request/' . $leavereq->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                
                                                @if($leavereq->stand_in_username==Auth::user()->username && $leavereq->stand_in_response==null)
                                                <button type="submit" id="approve-leave-request-{{ $leavereq->id }}" class="btn btn-success d-inline" >
                                                    <i class="fa fa-btn fa-check"></i>Accept
                                                </button>
                                                @endif
                                            </form>
                                        </td>                                            
                                    </tr>
                                @endforeach                                
                            </tbody>
                        </table>
                        @else
                        <p>Nothing for your attention</p>
                        @endif
                    </div>
                </div>            
        </div>
    </div>
@endsection
