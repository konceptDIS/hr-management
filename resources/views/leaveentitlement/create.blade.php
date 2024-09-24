@extends('layouts.app')
@section('title', 'New Leave Entitlement')
@section('content')
    <div class="container">
        <div class="col-sm-offset-2 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    {{$title}}
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New area Form -->
                    <form action="{{ url('leaveentitlements/new/') }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        <!-- Days Since Resumption -->
                        <div class="form-group">
                            <label for="days_since_resumption" class="col-sm-5 control-label">Days Since Resumption</label>

                            <div class="col-sm-7">
                              <input type="text" name="id" id="id_id" class="hidden form-control" value="{{ old('id', isset($leaveentitlement->id) ? $leaveentitlement->id : null) }}">
                              <input type="number" name="days_since_resumption" id="id_days_since_resumption" class="form-control" value="{{ old('days_since_resumption', isset($leaveentitlement->days_since_resumption) ? $leaveentitlement->days_since_resumption : null) }}">
                            </div>
                        </div>

                        <!--Allowance-->
                        <div class="form-group">
                            <label for="days_allowed" class="col-sm-5 control-label">Days Allocated</label>

                            <div class="col-sm-7">
                              <input type="number" name="days_allowed" id="id_days_allowed" class="form-control" value="{{ old('name', isset($leaveentitlement->days_allowed) ? $leaveentitlement->days_allowed : null) }}">
                            </div>
                        </div>

                        <!-- Leave Type -->
                        <div class="form-group">
                            <label for="leave_type" class="col-sm-5 control-label">Leave Type</label>

                            <div class="col-sm-7">
                              <select class="form-control choosen-select" name="leave_type" id="id_leave_type" value="{{ old('leave_type', isset($leaveentitlement->leave_type) ? $leaveentitlement->leave_type : null) }}">
                                <option value="0">Select Leave Type</option>
                                @foreach($leavetypes as $lt)
                                  @if($lt->name == $leaveentitlement->leave_type)
                                    <option value="{{$lt->name}}" selected>{{$lt->name}}</option>
                                  @else
                                  <option value="{{$lt->name}}" >{{$lt->name}}</option>
                                  @endif
                                @endforeach
                              </select>
                            </div>
                        </div>

                        <!-- Salary Grade -->
                        <div class="form-group">
                            <label for="salary_grade" class="col-sm-5 control-label">Leave Type</label>

                            <div class="col-sm-7">
                              <select class="form-control choosen-select" name="salary_grade" id="id_salary_grade" value="{{ old('salary_grade', isset($leaveentitlement->salary_grade) ? $leaveentitlement->salary_grade : null) }}">
                                <option value="0">Select Salary Grade</option>
                                @foreach($salarygrades as $sg)
                                  @if($sg->name == $leaveentitlement->salary_grade)
                                    <option value="{{$sg->name}}" selected>{{$sg->display_name}}</option>
                                  @else
                                  <option value="{{$sg->name}}" >{{$sg->display_name}}</option>
                                  @endif
                                @endforeach
                              </select>
                            </div>
                        </div>
                        <!-- Add area Button -->
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-6">
                                <button type="submit" class="btn btn-default">
                                    <i class="fa fa-btn fa-plus"></i>Add Leave Entitlement
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

    </div>
    </div>
@endsection
