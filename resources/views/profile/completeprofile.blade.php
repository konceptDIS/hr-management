@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="container">
  @if(Session::has('flash_message'))
    <div class="alert alert-info">
      {{Session::get('flash_message')}}
    </div>
  @endif
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading"><h2>{{$title}}</h1></div>
                <div class="panel-body">
                    <div class="hidden">
                      We need to know a few things about you before you can apply for leave.
                    </div>
                    <form class="form-horizontal" role="form" method="POST" action="{{ url('/complete-your-profile') }}">
                        {!! csrf_field() !!}
                        <input type="hidden" name="id" value="{{$user->id}}" />
                        <input type="hidden" name="isnew" value="{{$showName}}" />
                        <div class="{{ $showName==true ? '' : 'hidden'}} form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Full Name</label>

                            <div class="col-md-6">
                                <input type="text" class="form-control" readonly name="name" value="{{ old('name', isset($user->name) ? $user->name : null) }}">

                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                      

                        <div class="hidden {{ $showName==true ? '' : 'hidden'}} form-group{{ $errors->has('first_name') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">First Name</label>

                            <div class="col-md-6">
                                <input type="text" class="form-control" name="name" value="{{ old('first_name', isset($user->first_name) ? $user->first_name : null) }}">

                                @if ($errors->has('first_name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('first_name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="hidden {{ $showName==true ? '' : 'hidden'}} form-group{{ $errors->has('middle_name') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Middle Name</label>

                            <div class="col-md-6">
                                <input type="text" class="form-control" name="name" value="{{ old('middle_name', isset($user->middle_name) ? $user->middle_name : null) }}">

                                @if ($errors->has('middle_name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('middle_name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="hidden {{ $showName==true ? '' : 'hidden'}} form-group{{ $errors->has('last_name') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Last Name</label>

                            <div class="col-md-6">
                                <input type="text" class="form-control" name="name" value="{{ old('last_name', isset($user->last_name) ? $user->last_name : null) }}">

                                @if ($errors->has('last_name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('last_name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="{{strtolower(Auth::user()->username) == strtolower("abubakar.ibrahim") ? '' : 'hidden'}} form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Username</label>

                            <div class="col-md-6">
                                <input type="text" class="form-control" name="username" value="{{ old('username', isset($user->username) ? $user->username : null) }}">
                                @if ($errors->has('username'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('username') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

<div class="form-group{{ $errors->has('staff_id') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Staff ID</label>

                            <div class="col-md-6">
                                <input type="text" class="form-control" name="staff_id" value="{{ old('staff_id', isset($user->staff_id) ? $user->staff_id : null) }}">

                                @if ($errors->has('staff_id'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('staff_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

<div class=" form-group{{ $errors->has('salary_grade') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Annual Leave</label>

                            <div class="col-md-6">
                                <input type="text" class="hidden form-control" name="salary_grade-q" value="{{ old('salary_grade', isset($user->salary_grade) ? $user->salary_grade : null) }}">

                                <select class="form-control choosen-select" name="salary_grade" value="{{ old('salary_grade', isset($user->salary_grade) ? $user->salary_grade : null) }}">
                                  <option>Select Annual Leave days</option>
                                  @foreach($folevels as $fo)
                                    @if($fo->name == $user->salary_grade)
                                      <option value="{{$fo->name}}" selected>{{$fo->display_name}}</option>
                                    @else
                                      <option value="{{$fo->name}}">{{$fo->display_name}}</option>
                                    @endif
                                  @endforeach
                              </select>

                                @if ($errors->has('salary_grade'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('salary_grade') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="hidden form-group{{ $errors->has('department') ? ' has-error' : '' }}">
                                                    <label class="col-md-4 control-label">Department</label>

                                                    <div class="col-md-6">
                                                        <input type="text" class="form-control" name="department" value="{{ old('department', isset($user->department) ? $user->department : null) }}">
                                                        <select class="hidden form-control" id="department-old" value="{{ old('department', isset($user->department) ? $user->department : null)}}"
                                                           name="department-old">
                                                          <option>Select Department</option>
                                                          @foreach($departments as $department)
                                                            @if($department->name == $user->department || $department->id == $user->department)
                                                              <option value="{{$department->name}}" selected="selected">{{$department->name}}</option>
                                                            @else
                                                              <option value="{{$department->name}}">{{$department->name}}</option>
                                                            @endif
                                                          @endforeach
                                                        </select>
                                                        @if ($errors->has('department'))
                                                            <span class="help-block">
                                                                <strong>{{ $errors->first('department') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>



<div class=" hidden form-group{{ $errors->has('designation') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Designation</label>

                            <div class="col-md-6">
                              <input type="text" name="designation" class="form-control"
                              value="{{ old('designation', isset($user->designation) ? $user->designation : null) }}"/>
                            <select class="form-control hidden"
                            value="{{ old('designation', isset($user->designation) ? $user->designation : null) }}" name="d-esignation">
                                <option>Select Designation</option>
                                @foreach($designations as $designation)
                                  @if($designation->id==$user->designation)
                                    <option value="{{$designation->id}}" selected="selected">{{$designation->name}}</option>
                                  @else
                                    <option value="{{$designation->id}}">{{$designation->name}}</option>
                                  @endif
                                @endforeach
                            </select>
                            @if ($errors->has('designation'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('designation') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

<div class="form-group{{ $errors->has('resumption_date') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Date of Employment</label>

                            <div class="col-md-6">
                              <input type="text" class="form-control datepicker2" placeholder="dd/mm/yyyy" pattern="^\d{2}\/\d{2}\/\d{4}$"
                              name="resumption_date" value="{{ old('resumption_date', isset($user->resumption_date) ? $user->formatted_rdate() : null) }}">
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
                            <select class="form-control choosen-select" value="{{ old('gender', isset($user->gender) ? $user->gender : null) }}" name="gender">
                                <option>Select</option>
                                @foreach($genders as $gender)
                                  @if($gender == $user->gender)
                                    <option selected="selected">{{$gender}}</option>
                                  @else
                                    <option>{{$gender}}</option>
                                  @endif
                                @endforeach
                            </select>
                            @if ($errors->has('gender'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('gender') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('region') ? ' has-error' : '' }}">
                                                    <label class="col-md-4 control-label">Region</label>

                                                    <div class="col-md-6">
                                                    <input type="text" name="region-old" class="hidden form-control"
                              value="{{ old('region', isset($user->region) ? $user->region : null) }}"/>
                                                    <select class="form-control " value="{{ old('region', isset($user->region) ? $user->region : null) }}" name="region">
                                                        <option>Select</option>
                                                        @foreach($regions as $region)
                                                          @if($region->name == $user->region)
                                                            <option selected="selected">{{$region->name}}</option>
                                                          @else
                                                            <option>{{$region->name}}</option>
                                                          @endif
                                                        @endforeach
                                                    </select>
                                                    @if ($errors->has('region'))
                                                            <span class="help-block">
                                                                <strong>{{ $errors->first('region') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>

                        <div class="form-group{{ $errors->has('area_office') ? ' has-error' : '' }}">
                                                    <label class="col-md-4 control-label">Area Office</label>
                                                    <div class="col-md-6">
                                                    <select class="form-control" value="{{ old('area_office', isset($user->area_office) ? $user->area_office : null) }}" name="area_office">
                                                        <option>Select</option>
                                                        @foreach($area_offices as $area_office)
                                                          @if($area_office->name == $user->area_office)
                                                            <option selected="selected">{{$area_office->name}}</option>
                                                          @else
                                                            <option>{{$area_office->name}}</option>
                                                          @endif
                                                        @endforeach
                                                    </select>
                                                    @if ($errors->has('area_office'))
                                                            <span class="help-block">
                                                                <strong>{{ $errors->first('area_office') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>


<div class="form-group{{ $errors->has('is_contract_staff') ? ' has-error' : '' }}">
                            <label class="col-md-9 control-label">Tick if Member of Director Cadre</label>
                            <div class="col-md-1">
                                @if(isset($user->is_contract_staff) && $user->is_contract_staff == true)
                                    <input type="checkbox" class="form-control" name="is_contract_staff" id="is_contract_staff"
                                    value="{{ old('is_contract_staff', isset($user->is_contract_staff) ?  $user->is_contract_staff : null) }}"
                                     checked>
                                 @else
                                     <input type="checkbox" class="form-control" name="is_contract_staff" id="is_contract_staff"
                                     value="{{ old('is_contract_staff', isset($user->is_contract_staff) ?  $user->is_contract_staff : null) }}">
                                 @endif
                                @if ($errors->has('is_contract_staff'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('is_contract_staff') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div id="section-cont" class="hidden form-group{{ $errors->has('section') ? ' has-error' : '' }}">
                                                    <label class="col-md-4 control-label">Unit</label>

                                                    <div class="col-md-6">
                                                        <input type="text" class="hidden form-control" name="section" value="{{ old('section', isset($user->section) ? $user->section : null) }}">
                                                        <select class="form-control choosen-select" id="section" value="{{ old('section', isset($user->section) ? $user->section : null)}}" name="section">
                                                          <option>Select Unit</option>
                                                          @foreach($units as $unit)
                                                            @if($user->section == $unit->name || $user->section == $unit->id)
                                                              <option value="{{$unit->name}}" selected="selected">{{$unit->name}}</option>
                                                            @else
                                                              <option value="{{$unit->name}}">{{$unit->name}}</option>
                                                            @endif
                                                          @endforeach
                                                        </select>
                                                        @if ($errors->has('section'))
                                                            <span class="help-block">
                                                                <strong>{{ $errors->first('section') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                @if(Auth::user()->hasRole("Admin"))

                        <div class="form-group">
                          <label class="col-md-4 control-label">Roles</label>
                          <div class="col-md-6">
                            <div style="margin-top:6px;">
                                @foreach($roles as $role)
                                  @if($user->hasRole($role->name))
                                    <input type="checkbox" style="vertical-align:middle;" name="{{$role->name}}"  checked><span style="padding-top:5px;">{{$role->display_name}}</span></option>&nbsp
                                  @else
                                    <input type="checkbox" style="vertical-align:middle;" name="{{$role->name}}">{{$role->display_name}}</option>
                                  @endif
                                @endforeach
                              </div>
                          </div>
                        </div>
                        @endif

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-btn fa-user"></i>Save
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @if(Auth::user()->hasRole("Admin"))
                                            <form action="{{url('/users/delete/' . $user->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

                                                <button type="submit" id="delete-leaveentitlement-{{ $user->id }}" class="btn btn-danger">
                                                    <i class="fa fa-btn fa-trash" ></i>Delete
                                                </button>
                                            </form>
                                            @endif
</div>
@endsection
