@extends('layouts.app')
@section('title', 'Leave Approvals')
@section('content')
    <div class="container">
    <a href="/leaveapprovals/new" class="btn btn-primary btn-large">New Offline Leave Approval</a>
        <div class="rows">

            <!-- Current Offline Leave Approvals -->
            @if (count($approvals) > 0)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>Online Leave Approvals</h1>
                    </div>

                    <div class="panel-body">
              
                        <table class="table table-striped holiday-table">
                            <thead>
                              <th>#</th>
                              <th>Applicant</th>
                              <th>Username</th>
                              <th>Leave Type</th>
                              <th>Starting</th>
                              <th>Ending</th>
                              <th>Days</th>
                              <th>Approved by</th>
                              <th>Date Approved</th>
                              <th>Request Id</th>
                              <th>Approval Id</th>
                              <th style="white-space:nowrap;"></th>
                            </thead>
                            <tbody>
                                @foreach ($approvals as $approval)
                                    <tr>
                                      <td>{{$approval->sn}}</td>
                                      <td>{{$approval->applicant_name}}</td> 
                                      <td> ({{$approval->applicant_username}})</td>
                                      <td>{{$approval->leave_type}}</td>
                                      <td>{{$approval->start_date}}</td>
                                      <td>{{$approval->end_date}}</td>
                                      <td class="table-text"><div>{{ $approval->days }}</div></td>
                                      <td>{{$approval->approved_by}}</td>
                                      <td>{{$approval->date_approved}}</td>
                                      <td>{{$approval->leave_request_id}}</td>
                                      <td>{{$approval->id}}</td>
                                      <!-- Holiday Delete Button -->
                                      <td style="white-space:nowrap;">
                                        @if(Auth::user()->hasRole("HR1"))
                                            <form action="{{url('/leaveapprovals/' . $approval->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

                                                <button  type="submit" id="delete-leaveentitlement-{{ $approval->id }}" class="btn btn-danger pull-right">
                                                    <i class="fa fa-btn fa-trash" ></i>Delete
                                                </button>
                                                @if($approval->leave_request_id>0)
                                                    <a class="btn btn-primary btn-large pull-right" href="{{url('/view?applicationId=' . $approval->leave_request_id)}}">View</a>
                                                @endif
                                            </form>
                                            @endif
                                           
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                    </div>
                </div>
            @endif
        </div>
    <a class="btn btn-primary btn-large" href="/leaveapprovals/new">New Offline Leave Approval</a>

    </div>
@endsection