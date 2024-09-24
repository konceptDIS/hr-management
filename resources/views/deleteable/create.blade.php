@extends('layouts.app')
@section('title', 'Create Deletable')
@section('content')
    <div class="container">
    @if(Session::has('flash_message'))
        <div id="error-message" class="alert alert-info">
          {{Session::get('flash_message')}}
        </div>
      @endif
        <div class="col-sm-offset-2 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h1>New Leave Delete Approval</h1>
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New Holiday Form -->
                    <form action="{{ url('/deleteable') }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}
                        <!-- applicant_username -->
                        <div class="form-group">
                            <label for="reason" class="col-sm-3 control-label">Applicant Username</label>

                            <div class="col-sm-6">
                                <input type="text" name="applicant_username" id="reason" class="form-control" readonly value="{{ $applicant_username }}">
                            </div>
                        </div>

                        <!-- reason -->
                        <div class="form-group">
                            <label for="reason" class="col-sm-3 control-label">Reason</label>

                            <div class="col-sm-6">
                                <textarea name="reason" id="reason" class="form-control" cols="10" rows="5"></textarea>
                            </div>
                        </div>

                        <!--leave_request_id -->
                        <div class="form-group">
                            <label for="leave_request_id" class="col-sm-3 control-label">Leave Request ID</label>

                            <div class="col-sm-6">
                                <input type="number" name="leave_request_id" id="leave_request_id" readonly autocomplete="off" class="form-control" 
                                 value="{{ $leave_request_id }}">
                            </div>
                        </div>

                        <!--approver -->
                        <div class="form-group">
                            <label for="approver" class="col-sm-3 control-label">Approval Granted by</label>

                            <div class="col-sm-6">
                                <input type="text" name="approver" id="approver" readonly autocomplete="off" class="form-control" 
                                 value="{{ request()->user()->name }}">
                            </div>
                        </div>

                        @if(Auth::user()->hasRole("HR"))
                        <!-- Add Holiday Button -->
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-6">
                                <button type="submit" class="btn btn-default">
                                    <i class="fa fa-btn fa-plus"></i>Submit
                                </button>
                            </div>
                        </div>
                        @endif
                    </form>
                </div>
            </div>

    </div>
    </div>
@endsection
