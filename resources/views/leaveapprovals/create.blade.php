@extends('layouts.app')
@section('title', 'New Offline Leave Approval')
@section('content')
    <div class="container">
        <div class="col-sm-offset-2 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                   <h2> {{$title}} </h2>
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New area Form -->
                    <form action="{{ url('leaveapprovals/new/') }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        <!-- Applicant username -->
                        <div class="form-group">
                            <label for="applicant_username_id" class="col-sm-3 control-label">Applicant Username</label>

                            <div class="col-sm-6">
                              <input type="text" name="applicant_username" placeholder="firstname.lastname" id="applicant_username_id" class="form-control" value="{{ old('name', isset($leaveapprovals->applicant_username) ? $leaveapprovals->applicant_username : null) }}">

                            </div>
                        </div>

                        <!-- days granted -->
                        <div class="form-group">
                            <label for="days" class="col-sm-3 control-label">Days Granted</label>

                            <div class="col-sm-6">
                              <input type="text" name="id" id="id_id" class="hidden form-control" value="{{ old('id', isset($leaveapprovals->id) ? $leaveapprovals->id : null) }}">
                              <input type="number" name="days" id="id_days" max='30' min="1" class="form-control" value="{{ old('days', isset($leaveapprovals->days) ? $leaveapprovals->days : null) }}">
                            </div>
                        </div>

                        <!--date approved-->
                        <div class="form-group">
                            <label for="date_approved" class="col-sm-3 control-label">Date Approved</label>

                            <div class="col-sm-6">
                              <input type="text" name="date_approved" id="id_date_approved" class="datepicker2 form-control" value="{{ old('$leaveapprovals->date_approved', isset($leaveapprovals->date_approved) ? $leaveapprovals->date_approved : null) }}">
                            </div>
                        </div>

                        <!-- Leave Type -->
                        <div class="form-group">
                            <label for="leave_type" class="col-sm-3 control-label">Leave Type</label>

                            <div class="col-sm-6">
                              <select class="form-control choosen-select" name="leave_type" id="id_leave_type" value="{{ old('leave_type', isset($leaveapprovals->leave_type) ? $leaveapprovals->leave_type : null) }}">
                                <option value="0">Select Leave Type</option>
                                @foreach($leavetypes as $lt)
                                  @if($lt->name == $leaveapproval->leave_type)
                                    <option value="{{$lt->name}}" selected>{{$lt->name}}</option>
                                  @else
                                  <option value="{{$lt->name}}" >{{$lt->name}}</option>
                                  @endif
                                @endforeach
                              </select>
                            </div>
                        </div>

                        <!-- Stand In -->
                        <div class="hidden form-group">
                            <label for="leaverequest-stand_in_username" class="col-sm-3 control-label">Stand In Username</label>

                            <div class="col-sm-6">
                                <input type="text" placeholder="firstname.lastname" name="stand_in_username" id="leaverequest-stand_in_username" class="form-control" value="{{ old('standin_username') }}">
                            </div>
                        </div>

                        <!-- Approver Username -->
                        <div class="form-group">
                            <label for="leaverequest-supervisor_username" class="col-sm-3 control-label">Approver Username</label>

                            <div class="col-sm-6">
                                <input type="text" placeholder="firstname.lastname" name="approved_by" id="leaverequest-supervisor_username" class="form-control" value="{{ old('approver_username') }}">
                            </div>
                        </div>

                       
                        <!-- Add area Button -->
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-6">
                                <button type="submit" class="btn btn-default">
                                    <i class="fa fa-btn fa-plus"></i>Add Leave Approval
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

    </div>
</div>
@endsection
