@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Register</div>
                <div class="panel-body">
                    <form class="form-horizontal" role="form" method="POST" action="{{ url('/register') }}">
                        {!! csrf_field() !!}

                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Name</label>

                            <div class="col-md-6">
                                <input type="text" class="form-control" name="name" value="{{ old('name') }}">

                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">E-Mail Address</label>

                            <div class="col-md-6">
                                <input type="email" class="form-control" name="email" value="{{ old('email') }}">

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

<div class="form-group{{ $errors->has('staff_id') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Staff Id</label>

                            <div class="col-md-6">
                                <input type="text" class="form-control" name="staff_id" value="{{ old('staff_id') }}">

                                @if ($errors->has('staff_id'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('staff_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

<div class="form-group{{ $errors->has('job_title') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Job Title</label>

                            <div class="col-md-6">
                                <input type="text" class="form-control" name="job_title" value="{{ old('job_title') }}">

                                @if ($errors->has('job_title'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('job_title') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

<div class="form-group{{ $errors->has('section') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Section</label>

                            <div class="col-md-6">
                                <input type="text" class="form-control" name="section" value="{{ old('section') }}">

                                @if ($errors->has('section'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('section') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

<div class="form-group{{ $errors->has('salary_grade') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Salary Grade</label>

                            <div class="col-md-6">
                            <select class="form-control" value="{{ old('salary_grade') }}" name="salary_grade">
                                <option>Select</option>
                                <option>FO 1</option>
                                <option>FO 2</option>
                            </select>
                            @if ($errors->has('salary_grade'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('salary_grade') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

<div class="form-group{{ $errors->has('resumption_date') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Resumption Date</label>

                            <div class="col-md-6">
                                <input type="date" class="form-control datepicker2" name="resumption_date" value="{{ old('resumption_date') }}">

                                @if ($errors->has('resumption_date'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('resumption_date') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
<div class="form-group{{ $errors->has('gender') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Gender</label>

                            <div class="col-md-6">
                            <select class="form-control" value="{{ old('gender') }}" name="gender">
                                <option>Select</option>
                                <option value="M">Male</option>
                                <option value="F">Female</option>
                            </select>
                            @if ($errors->has('salary_grade'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('salary_grade') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

<div class="form-group{{ $errors->has('md_approval_required') ? ' has-error' : '' }}">
                            <label class="col-md-9 control-label">Tick if MD has to approve your leave</label>
                            <div class="col-md-1">
                                <input type="checkbox" class="form-control" name="md_approval_required" id="md_approval_required" value="{{ old('md_approval_required') }}">

                                @if ($errors->has('md_approval_required'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('md_approval_required') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Password</label>

                            <div class="col-md-6">
                                <input type="password" class="form-control" name="password">

                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Confirm Password</label>

                            <div class="col-md-6">
                                <input type="password" class="form-control" name="password_confirmation">

                                @if ($errors->has('password_confirmation'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-btn fa-user"></i>Register
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
